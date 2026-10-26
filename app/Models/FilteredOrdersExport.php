<?php

namespace App\Models;

use App\Traits\PreventDemoModeChanges;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Auth;

class FilteredOrdersExport implements FromCollection, WithMapping, WithHeadings, WithCustomValueBinder
{
    use PreventDemoModeChanges;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $request = $this->request;
        $date = $request->date;
        $sort_search = null;
        $admin_user_id = get_admin()->id;

        $orders = Order::orderBy('id', 'desc');

        if (Route::currentRouteName() == 'inhouse_orders.index' && Auth::user()->can('view_inhouse_orders')) {
            $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
        } elseif (Route::currentRouteName() == 'seller_orders.index' && Auth::user()->can('view_seller_orders')) {
            $orders = $orders->where('orders.seller_id', '!=', $admin_user_id);
        } elseif (Route::currentRouteName() == 'pick_up_point.index' && Auth::user()->can('view_pickup_point_orders')) {
            if (get_setting('vendor_system_activation') != 1) {
                $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
            }
            $orders->where('shipping_type', 'pickup_point')->orderBy('code', 'desc');
            if (Auth::user()->user_type == 'staff' && Auth::user()->staff->pick_up_point != null) {
                $orders->where('shipping_type', 'pickup_point')
                    ->where('pickup_point_id', Auth::user()->staff->pick_up_point->id);
            }
        } elseif (Route::currentRouteName() == 'all_orders.index' && Auth::user()->can('view_all_orders')) {
            if (get_setting('vendor_system_activation') != 1) {
                $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
            }
        } elseif (Route::currentRouteName() == 'offline_payment_orders.index' && Auth::user()->can('view_all_offline_payment_orders')) {
            $orders = $orders->where('orders.manual_payment', 1);
            if ($request->order_type != null) {
                $order_type = $request->order_type;
                $orders = $order_type == 'inhouse_orders' ?
                    $orders->where('orders.seller_id', '=', $admin_user_id) :
                    $orders->where('orders.seller_id', '!=', $admin_user_id);
            }
        } elseif (Route::currentRouteName() == 'unpaid_orders.index' && Auth::user()->can('view_all_unpaid_orders')) {
            $orders = $orders->where('orders.payment_status', 'unpaid');
        }

        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where(function ($q) use ($sort_search) {
                $q->where('code', 'like', '%' . $sort_search . '%')
                    ->orWhereHas('user', function ($u) use ($sort_search) {
                        $u->where('name', 'like', '%' . $sort_search . '%')
                            ->orWhere('phone', 'like', '%' . $sort_search . '%');
                    })
                    ->orWhereHas('orderDetails.product', function ($p) use ($sort_search) {
                        $p->where('name', 'like', '%' . $sort_search . '%')
                            ->orWhereHas('product_translations', function ($t) use ($sort_search) {
                                $t->where('name', 'like', '%' . $sort_search . '%');
                            });
                    });
            });
        }
        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])) . '  00:00:00')
                ->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])) . '  23:59:59');
        }

        return $orders->with('orderDetails.product')->get();
    }

    public function headings(): array
    {
        return [
            'Order Code',
            'Num. of Products',
            'Products',
            'customer_name',
            'customer_email',
            'customer_phone',
            'Seller',
            'Delivery Charge',
            'Discount',
            'Total Amount',
            'Delivery Status',
            'Payment method',
            'Payment Status',
            'Create Date',
        ];
    }

    public function map($order): array
    {
        return [
            (string) $order->code,
            count($order->orderDetails),
            $this->productsSummary($order),
            $order->user != null ? $order->user->name : '',
            $order->user != null ? $order->user->email : '',
            $order->orderAddress ? $order->orderAddress->phone : ($order->user ? $order->user->phone : ''),
            $order->shop != null ? $order->shop->name : translate('Inhouse Order'),
            single_price($order->shipping_cost ?? 0),
            single_price(($order->coupon_discount ?? 0) + ($order->admin_discount ?? 0)),
            single_price($order->grand_total),
            translate(ucfirst(str_replace('_', ' ', $order->delivery_status))),
            translate(ucfirst(str_replace('_', ' ', $order->payment_type))),
            translate(ucfirst($order->payment_status)),
            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    protected function productsSummary($order): string
    {
        return $order->orderDetails->map(function ($detail) {
            $name = $detail->product ? $detail->product->getTranslation('name') : 'Deleted Product';
            if ($detail->variation) {
                $name .= ' (' . $detail->variation . ')';
            }
            return $name . ' x' . $detail->quantity . ' - ' . single_price($detail->price);
        })->implode(', ');
    }

    public function bindValue(Cell $cell, $value)
    {
        $cell->setValueExplicit($value, DataType::TYPE_STRING);

        return true;
    }
}
