<?php

namespace App\Http\Resources\V2;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $prices = $this->collection->map(function ($product) {
            return (float) home_discounted_base_price($product, false);
        })->filter();
        
        return [
            'data' => $this->collection->map(function ($data) {
                $wholesale_product = ($data->wholesale_product == 1) ? true : false;
                $firstStock = $data->stocks->first();
                $product_stock = $this->productStocks($data);
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'discount' => "-" . discount_in_percentage($data) . "%",
                    'stroked_price' => home_base_price($data),
                    'main_price' => home_discounted_base_price($data),
                    'current_stock' => (int) (optional($firstStock)->qty ?? 0),
                    'sku' => optional($firstStock)->sku ?? '',
                    'rating' => (float) $data->rating,
                    'sales' => (int) $data->num_of_sale,
                    'is_wholesale' => $wholesale_product,
                    'is_new' => $data->is_new,
                    'is_wishlisted' => $data->is_wishlisted,
                    'is_free_delivery' => $data->is_free_delivery == 1 ? true : false,
                    'model' => $data->model,
                    'product_type' => $data->type->name ?? '',
                    'made_in' => $data->country->name ?? '',
                    'upcoming' => $data->upcoming == 1 ? true : false,
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'product_stock' => $product_stock,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ]
                ];
            }),
            'price_range' => [
                'min' => $prices->min(),
                'max' => $prices->max(),
            ],
            'fixed_attributes' => $this->getFixedAttributes(),
            'unique_attributes' => $this->getUniqueAttributes(),
            'sub-category' => Category::select('id', 'name', 'slug')
            ->whereIn('parent_id', $this->collection->pluck('category_id')->unique()->values()->all())
            ->get()
            ->toArray(),
        ];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta'] = array_merge($default['meta'], $this->getCategorySeo($request));

        return $default;
    }

    public function productStocks($data)
    {
        $productStocks = $data->stocks;

        if ($productStocks->isEmpty()) {
            return [];
        }

        return $productStocks->map(function ($item) use ($data) {
            $mainPrice = $item->price;
            $discountedPrice = $mainPrice;

            if ($data->discount > 0) {
                if ($data->discount_type == 'percent') {
                    $discountedPrice = $mainPrice - ($mainPrice * $data->discount / 100);
                } else {
                    $discountedPrice = $mainPrice - $data->discount;
                }
            }

            $discountedPrice = max($discountedPrice, 0);

            $stroked_price = format_price(convert_price($mainPrice));
            $main_price = format_price(convert_price($item->discount != null && $item->discount >= 0 ? $item->discount : $discountedPrice));
            return [
                'variant'           => $item->variant,
                'stroked_price'     => $stroked_price,
                'main_price'        => $main_price,
                'is_discount'       => $stroked_price > $main_price ? true : false,
                'calculable_price'  => $item->discount != null && $item->discount >= 0 ? $item->discount : $discountedPrice,
                'sku'               => $item->sku,
                'qty'               => $item->qty,
                'image'             => $item->image ? uploaded_asset($item->image) : '',
            ];
        })->toArray();
    }

    private function getCategorySeo($request)
    {
        $empty = [
            'icon' => null,
            'meta_title' => null,
            'meta_description' => null,
        ];

        if (!$request->filled('category')) {
            return $empty;
        }

        $category = Category::select(['id', 'icon', 'meta_title', 'meta_description'])
            ->where('slug', $request->category)
            ->first();

        if ($category == null) {
            return $empty;
        }

        return [
            'icon' => uploaded_asset($category->icon),
            'meta_title' => $category->meta_title,
            'meta_description' => $category->meta_description,
        ];
    }

    private function getFixedAttributes()
    {
        $filters = [
            'country_id' => [
                'class' => Country::class,
                'name' => 'Made In',
                'key' => 'countries',
            ],
            'product_type_id' => [ 
                'class' => ProductType::class,
                'name' => 'Product Type',
                'key' => 'product_types',
            ]
        ];        

        $fixed_attributes = [];

        foreach ($filters as $column => $object) {
            $keys = Product::where('published', true)->pluck($column)->filter()->unique()->toArray();

            $values = $object['class']::select('id', 'name')->whereIn('id', $keys)->get();

            $fixed_attributes[$object['key']] = [
                'name' => $object['name'],
                'attribute_values' => $values
            ];
        }
        return $fixed_attributes;
    }

    private function getUniqueAttributes()
    {
        $attributes = [];
        foreach ($this->collection as $data) {
            $attributes = array_merge($attributes, json_decode($data->attributes, true) ?? []);
        }
        $attributes = array_unique($attributes);

        if (count($attributes) > 0) {
            $unique_attributes = Attribute::with('attribute_values:id,value,attribute_id')
            ->select('id', 'name')
            ->whereIn('id', $attributes)
            ->get();
            
        } else {
            $unique_attributes = collect([]);
        }

        return $unique_attributes->unique('id');
    }

    protected function convertToChoiceOptions($data)
    {
        $result = array();
        $order = 1;
        if ($data) {
            foreach ($data as $key => $choice) {
                $item['order'] = $order++;
                $item['name'] = $choice->attribute_id;
                $item['title'] = optional(Attribute::find($choice->attribute_id))->getTranslation('name');
                $item['options'] = $choice->values;
                array_push($result, $item);
            }
        }
        return $result;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

}
