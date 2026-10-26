<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ReviewCollection;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Storage;
use Image;

class ReviewController extends Controller
{
    public function index($id)
    {
        return new ReviewCollection(Review::where('product_id', $id)->where('status', 1)->orderBy('updated_at', 'desc')->paginate(10));
    }

    /* public function submit(Request $request)
    {
        $product = Product::find($request->product_id);
        $user = User::find(auth('sanctum')->user()->id);

        $reviewable = false;

        foreach ($product->orderDetails as $key => $orderDetail) {
            if($orderDetail->order != null && $orderDetail->order->user_id == auth('sanctum')->user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', auth()->user()->id)->where('product_id', $product->id)->first() == null){
                $reviewable = true;
            }
        }

        if(!$reviewable){
            return response()->json([
                'result' => false,
                'message' => translate('You cannot review this product')
            ]);
        }

        $review = new \App\Models\Review;
        $review->product_id = $request->product_id;
        $review->user_id = auth()->user()->id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->viewed = 0;
        $review->save();

        $orderIds = Order::where('user_id', auth()->user()->id)->pluck('id');
            OrderDetail::whereIn('order_id', $orderIds)
                ->where('product_id', $request->product_id)
                ->update(['reviewed' => 1]);

        $count = Review::where('product_id', $product->id)->where('status', 1)->count();
        if($count > 0){
            $product->rating = Review::where('product_id', $product->id)->where('status', 1)->sum('rating')/$count;
        }
        else {
            $product->rating = 0;
        }
        $product->save();

        if($product->added_by == 'seller'){
            $seller = $product->user->shop;
            $seller->rating = (($seller->rating*$seller->num_of_reviews)+$review->rating)/($seller->num_of_reviews + 1);
            $seller->num_of_reviews += 1;
            $seller->save();
        }

        return response()->json([
            'result' => true,
            'message' => translate('Review  Submitted')
        ]);
    } */

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string|max:1000',
            'photos'     => 'nullable|array',
            'photos.*'   => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['result' => false,'message' => $validator->errors()->first()], 400);
        }

        $review = new Review();
        $review->product_id = $request->product_id;
        $review->user_id = auth('sanctum')->user()->id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->viewed = 0;

        if ($request->hasFile('photos')) {
            $uploadedIds = [];
            foreach ($request->file('photos') as $file) {
                $upload = new Upload;
                $extension = strtolower($file->getClientOriginalExtension());

                $upload->file_original_name = null;
                $arr = explode('.', $file->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0) {
                        $upload->file_original_name .= $arr[$i];
                    } else {
                        $upload->file_original_name .= "." . $arr[$i];
                    }
                }

                $upload->file_original_name = Upload::uniqueOriginalName($upload->file_original_name, auth('sanctum')->user()->id);

                $path = $file->store('uploads/all', 'local');
                $size = $file->getSize();

                if (get_setting('disable_image_optimization') != 1) {
                    try {
                        $img = Image::make($file->getRealPath())->encode();
                        $height = $img->height();
                        $width = $img->width();
                        if ($width > $height && $width > 1500) {
                            $img->resize(1500, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        } elseif ($height > 1500) {
                            $img->resize(null, 800, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        }
                        $img->save(base_path('public/') . $path);
                        clearstatcache();
                        $size = $img->filesize();
                    } catch (\Exception $e) {
                    }
                }

                if (env('FILESYSTEM_DRIVER') == 's3') {
                    Storage::disk('s3')->put(
                        $path,
                        file_get_contents(base_path('public/') . $path),
                        [
                            'visibility' => 'public',
                            'ContentType' => $extension == 'svg' ? 'image/svg+xml' : $file->getMimeType()
                        ]
                    );
                    if ($arr[0] != 'updates') {
                        unlink(base_path('public/') . $path);
                    }
                }

                $upload->extension = $extension;
                $upload->file_name = $path;
                $upload->user_id = auth('sanctum')->user()->id;
                $upload->type = 'image';
                $upload->file_size = $size;
                $upload->save();

                $uploadedIds[] = $upload->id;
            }
            $review->photos = implode(',', $uploadedIds);
        }

        $review->save();

        return response()->json([
            'result' => true,
            'message' => translate('Review  Submitted')
        ]);
    }

}
