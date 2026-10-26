<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FaqCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'question' => $data->getTranslation('question'),
                    'answer' => $data->getTranslation('answer'),
                    'status' => (int) $data->status,
                    'products' => $data->products->map(function($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->getTranslation('name'),
                        ];
                    }),
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
