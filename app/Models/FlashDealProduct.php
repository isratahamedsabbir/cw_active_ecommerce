<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class FlashDealProduct extends Model
{
    use PreventDemoModeChanges;

    protected $fillable=['flash_deal_id', 'product_id', 'product_stock_id', 'discount', 'discount_type'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function product_stock()
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function flash_deal()
    {
        return $this->belongsTo(FlashDeal::class);
    }

    // price of this variant (or the product itself) before the flash discount
    public function basePrice()
    {
        return $this->product_stock ? (float) $this->product_stock->price : (float) $this->product->unit_price;
    }

    public function discountAmount($price = null)
    {
        $price = $price ?? $this->basePrice();

        $amount = $this->discount_type == 'percent'
            ? ($price * $this->discount) / 100
            : (float) $this->discount;

        return min($amount, $price);
    }

    public function discountedPrice()
    {
        return max($this->basePrice() - $this->discountAmount(), 0);
    }
}
