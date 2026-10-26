<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FlashDealProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public static $wrap = null;

    public function toArray($request)
    {
        // one entry per product, its variants nested inside
        return $this->collection->groupBy('product_id')->map(function ($rows) {
                $product = $rows->first()->product;
                $lowest = $rows->sortBy(fn($row) => $row->discountedPrice())->first();
                $hasVariants = $rows->contains(fn($row) => ($row->product_stock->variant ?? '') !== '');

                return array_merge(['id' => $product->id],
                    // only a product without variants carries it, the order uses this id directly
                    $hasVariants ? [] : ['flash_deal_product_id' => $lowest->id],
                [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => uploaded_asset($product->thumbnail_img),
                    'price' => format_price(convert_price($lowest->discountedPrice())),
                    'has_discount' => $rows->contains(fn($row) => $row->discountAmount() > 0),
                    'stroked_price' => format_price(convert_price($lowest->basePrice())),
                    'is_free_delivery' => $product->is_free_delivery == 1 ? true : false,
                    'variants' => $hasVariants ? $rows->map(function ($row) {
                        $basePrice = $row->basePrice();
                        $discountAmount = $row->discountAmount($basePrice);

                        return [
                            'flash_deal_product_id' => $row->id,
                            'variant' => $row->product_stock->variant ?? '',
                            'price' => format_price(convert_price(max($basePrice - $discountAmount, 0))),
                            'has_discount' => $discountAmount > 0,
                            'discount' => $row->discount_type == 'percent'
                                ? "-" . (float) $row->discount . "%"
                                : "-" . format_price(convert_price($row->discount)),
                            'stroked_price' => format_price(convert_price($basePrice)),
                        ];
                    })->values() : [],
                ]);
        })->values()->all();
    }
}
