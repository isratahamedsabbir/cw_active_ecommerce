<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Http\Requests\OrderStoreRequest;
use App\Jobs\SendSmsJob;
use App\Jobs\ProcessOrderPlacedJob;
use App\Models\CombinedOrder;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ShippingCharge;
use App\Models\User;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\Hash;
use App\Models\Coupon;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;
use App\Mail\OrderPlacedMail;
use Illuminate\Support\Facades\Mail;
use App\Services\Payment\PaymentService;
use DB;
use Exception;

class CustomOrderController extends Controller
{

    public function store(OrderStoreRequest $request, PaymentService $paymentService)
    {

        try {
            DB::beginTransaction();
            
            $user = auth('sanctum')->user() ?? null;

            if ($user == null) {
                $user = User::where('phone', $request->customer_address['phone'])->first();
                if ($user != null) {
                    $user = $user;
                } else {
                    $user = User::create([
                        'name' => $request->customer_address['name'],
                        'email' => $request->customer_address['email'] ?? null,
                        'phone' => $request->customer_address['phone'],
                        'password' => Hash::make(random_int(10000000, 99999999))
                    ]);

                    /* $sms = 'Your temporary password is ' . $user->password;
                    SendSmsJob::dispatch($sms, $user->phone); */
                }
            }

            //start insert customer address
            $customerAddress = [];
            if ($request->customer_address != null) {
                $customerAddress = [
                    'name'      => $request->customer_address['name'],
                    'email'     => $request->customer_address['email'] ?? null,
                    'phone'     => $request->customer_address['phone'],
                    'address'   => $request->customer_address['address'],
                    'comment'   => $request->customer_address['comment']
                ];
            }

            //start insert shipping address
            $shippingAddress = [];
            if ($request->shipping_address != null) {
                $shippingAddress = [
                    'name'      => $request->shipping_address['name'],
                    'email'     => $request->shipping_address['email'] ?? null,
                    'phone'     => $request->shipping_address['phone'],
                    'address'   => $request->shipping_address['address'],
                    'comment'   => $request->shipping_address['comment']
                ];
            }

            if ($user != null) {
                $combined_order = new CombinedOrder();
                $combined_order->user_id = $user->id ?? null;
                $combined_order->shipping_address = json_encode($shippingAddress);
                $combined_order->save();
            }

            //end insert shipping address

            $order = new Order;
            $order->combined_order_id = $combined_order->id ?? null;
            $order->user_id = $user->id ?? null;
            $order->shipping_address = json_encode($shippingAddress);
            $order->customer_address = json_encode($customerAddress);
            $order->shipping_type = $request->shipping_type;
            $order->pickup_point_id = $request->shipping_type == 'pickup_point' ? $request->pickup_point : 0;
            $order->order_from = 'web';
            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('YmdHis') . rand(100, 999);
            $order->date = strtotime('now');
            $order->payment_status = 'unpaid';
            $order->terms_accepted = $request->terms_accepted ? 1 : 0;
            $order->additional_info = $request->additional_info ?? $request->customer_address['comment'] ?? $request->shipping_address['comment'] ?? null;
            $order->save();

            $subTotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $totalShippingCost = 0;
            $coupon_discount_amount = 0;
            $hasAnyNonFreeDeliveryProduct = false;

            // load all products/stocks once (row-locked to prevent overselling)
            $productIds = collect($request->products)->pluck('product_id')->unique()->sort()->values();
            $products = Product::with('user.seller')->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $stocksByProduct = ProductStock::whereIn('product_id', $productIds)->orderBy('id')->lockForUpdate()->get()->groupBy('product_id');
            $findStock = function ($productId, $variant) use ($stocksByProduct) {
                return ($stocksByProduct[$productId] ?? collect())->first(fn($s) => strcasecmp((string) $s->variant, (string) $variant) === 0);
            };

            foreach ($request->products as $key => $product) {
                $productDetail = $products[$product['product_id']] ?? null;

                if ($productDetail == null) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'code' => 400, 'message' => translate('Product is not available')]);
                }

                /* if ($productDetail->current_stock < $product['quantity']) {
                    return response()->json(['status' => false, 'code' => 400, 'message' => translate('Product quantity is not available')]);
                } */

                if ($productDetail->upcoming == 1) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'code' => 400, 'message' => translate('Product is not available')]);
                }

                //start check flash deal
                $flashDealProductId = $product['flash_deal_product_id'] ?? null;
                if ($flashDealProductId) {
                    $today = strtotime(date('d-m-Y'));
                    $flashDealProduct = FlashDealProduct::where('id', $flashDealProductId)
                        ->where('product_id', $product['product_id'])
                        ->first();
                    $inDeal = $flashDealProduct && FlashDeal::where('id', $flashDealProduct->flash_deal_id)
                        ->where('status', 1)
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->exists();

                    if (!$inDeal) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'code' => 400, 'message' => translate('Flash deal is not available for this product')]);
                    }
                }
                //end check flash deal

                $productDetail->current_stock = $productDetail->current_stock - $product['quantity'];
                $productDetail->num_of_sale = $productDetail->num_of_sale + $product['quantity'];
                $productDetail->save();

                //start check product variation
                if (isset($product['variant']) && $product['variant'] != null) {
                    $product_variation = $product['variant'];
                } else {
                    $product_variation = '';
                }

                $product_stock = $findStock($product['product_id'], $product_variation);

                if ($product_stock == null) {
                    DB::rollBack();
                    Log::error('Order store: product stock row not found', [
                        'product_id' => $product['product_id'],
                        'variant'    => $product_variation,
                    ]);
                    return response()->json(['combined_order_id' => 0, 'status' => false, 'message' => translate('The requested product quantity is not available.')]);
                }

                if ($flashDealProductId && $flashDealProduct->product_stock_id && $flashDealProduct->product_stock_id != $product_stock->id) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'code' => 400, 'message' => translate('Flash deal is not available for this product')]);
                }

                if ($productDetail->digital != 1 && $product['quantity'] > $product_stock->qty) {
                    DB::rollBack();
                    return response()->json(['combined_order_id' => 0, 'status' => false, 'message' => translate('The requested product quantity is not available.')]);
                } elseif ($productDetail->digital != 1) {
                    $product_stock->qty -= $product['quantity'];
                    $product_stock->save();
                }
                //end check product variation

                $quantity = $product['quantity'];

                $total = $product_stock->price * $quantity;
                $subTotal += $total;
                
                if ($flashDealProductId) {
                    $discount = $flashDealProduct->discountAmount($product_stock->price) * $quantity;
                }elseif($product_stock->discount != null || $product_stock->discount > 0){
                    $discount = ($product_stock->price - $product_stock->discount) * $quantity;
                }elseif($productDetail->discount>0 && $productDetail->discount_type == 'percent'){
                     $discount = ($product_stock->price * $productDetail->discount) / 100 * $quantity;
                }else{
                    $discount = $productDetail->discount * $quantity;
                }
                $totalDiscount += $discount;

                $tax = $productDetail->tax * $quantity;
                $totalTax += $tax;

                if ($productDetail->is_free_delivery == 1) {
                    $shipping = 0;
                } else {
                    $shipping = $productDetail->shipping_cost * $quantity;
                    $hasAnyNonFreeDeliveryProduct = true;
                }
                $totalShippingCost += $shipping;

                $thisProsuctTotal = $total + $tax + $shipping - $discount;

                $orderDetail = new OrderDetail;
                $orderDetail->order_id = $order->id;
                $orderDetail->seller_id = $productDetail->user_id;
                $orderDetail->product_id = $product['product_id'];
                $orderDetail->flash_deal_product_id = $flashDealProductId ?: null;
                $orderDetail->variation = $product_variation;
                $orderDetail->quantity = $quantity;
                $orderDetail->unit_price = ceil($thisProsuctTotal / $quantity);
                $orderDetail->discount = $discount / $quantity;
                $orderDetail->price = $thisProsuctTotal;
                $orderDetail->tax = $tax;
                $orderDetail->shipping_cost = $shipping;
                $orderDetail->save();

                if ($productDetail->added_by == 'seller' && $productDetail->user->seller != null) {
                    $productDetail->user->seller->increment('num_of_sale', $quantity);
                }
            }

            $shipping_cost = ShippingCharge::find($request->shipping_charge_id)->cost;
            $order->shipping_charge_id = $request->shipping_charge_id;
            if ($hasAnyNonFreeDeliveryProduct) {
                $order->shipping_cost = $shipping_cost;
            } else {
                $shipping_cost = 0;
                $order->shipping_cost = 0;
            }

            //start coupon
            if ($request->filled('coupon_code')) {

                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('status', 1)
                    ->where('start_date', '<=', strtotime(date('d-m-Y')))
                    ->where('end_date', '>=', strtotime(date('d-m-Y')))
                    ->first();

                if ($coupon) {

                    $details              = json_decode($coupon->details, true);
                    $coupon_code          = $coupon->code;
                    $coupon_discount      = (float) $coupon->discount;
                    $coupon_discount_type = $coupon->discount_type;

                    $freeShippingAmount = 0;

                    if ($coupon->delivery_free == 1) {
                        $freeShippingAmount = $totalShippingCost + $shipping_cost;
                        $totalShippingCost = 0;
                        $shipping_cost = 0;
                        $order->shipping_cost = 0;
                        $order->coupon_code = $coupon_code;
                        OrderDetail::where('order_id', $order->id)->update(['shipping_cost' => 0]);
                    }

                    if ($coupon->type == 'cart_base' || $coupon->type == 'delivery_free') {
                        $coupon_min_buy      = (int) ($details['min_buy'] ?? 0);
                        $coupon_max_discount = (int) ($details['max_discount'] ?? 0);

                        if ($subTotal >= $coupon_min_buy) {
                            if ($coupon_discount_type == 'percent') {
                                $coupon_discount_amount = ($subTotal * $coupon_discount) / 100;
                                if ($coupon_max_discount > 0 && $coupon_discount_amount > $coupon_max_discount) {
                                    $coupon_discount_amount = $coupon_max_discount;
                                }
                            } else {
                                $coupon_discount_amount = $coupon_discount;
                            }
                            $order->coupon_code     = $coupon_code;
                        }

                        $order->coupon_discount = $coupon_discount_amount + $freeShippingAmount;
                    } elseif ($coupon->type == 'product_base') {
                        $matchingTotal = 0;
                        foreach ($request->products as $product) {
                            foreach ($details as $detail) {
                                if ($detail['product_id'] == $product['product_id']) {
                                    $product_stock = $findStock($product['product_id'], $product['variant'] ?? '');
                                    if ($product_stock) {
                                        $matchingTotal += $product_stock->price * $product['quantity'];
                                    }
                                    break;
                                }
                            }
                        }

                        if ($matchingTotal > 0) {
                            if ($coupon_discount_type == 'percent') {
                                $coupon_discount_amount = ($matchingTotal * $coupon_discount) / 100;
                            } else {
                                $coupon_discount_amount = $coupon_discount;
                            }
                            $order->coupon_code     = $coupon_code;
                            $order->coupon_discount = $coupon_discount_amount;
                        }
                    }
                }
            }
            //end coupon

            $add_total = ($subTotal + $totalTax + $totalShippingCost + $shipping_cost);
            $sub_total = ($totalDiscount + $coupon_discount_amount);
            $order->grand_total = $add_total - $sub_total;

            $order->save();

            DB::commit();

            if (empty($user->email) && !empty($customerAddress['email'])) {

                $emailExists = User::where('email', $customerAddress['email'])
                    ->where('id', '!=', $user->id)
                    ->exists();

                if (!$emailExists) {
                    $user->email = $customerAddress['email'];
                    $user->save();
                }
            }

            try{
                $sms = "Your order has been placed successfully.\n" . "Order code: {$order->code}.\n" . "https://agrony.com";
                SendSmsJob::dispatch($sms, $user->phone);
                // telegram, email and notifications run in the background
                ProcessOrderPlacedJob::dispatch($order->id, $user->id, (bool) auth('sanctum')->user());
            }catch(\Throwable $e){
                Log::error('Order post-processing dispatch failed', ['order_code' => $order->code, 'error' => $e->getMessage()]);
            }

            $paymentData = null;
            if ($request->payment_type !== 'cash') {
                try {
                    $paymentData = $paymentService->process($request->payment_type, [
                        'combined_order_id' => $combined_order->id,
                        'user_id'           => $user->id,
                        'amount'            => $order->grand_total,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Payment initiation failed', ['order_code' => $order->code, 'error' => $e->getMessage()]);
                }
            }

            $response = [
                'order_id' => $order->code,
                'status'   => true,
                'code'     => 200,
                'total_amount' => $order->grand_total,
                'message'  => translate('Your order has been placed successfully'),
            ];

            if ($paymentData) {
                $response['payment'] = $paymentData;
            }

            return response()->json($response);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Order store failed', [
                'order_code' => $order->code ?? null,
                'phone'      => $request->customer_address['phone'] ?? null,
                'products'   => $request->products,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile() . ':' . $e->getLine(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'code'    => 500,
                'message' => translate('Something went wrong. Please try again.'),
            ], 500);
        }
    }
}
