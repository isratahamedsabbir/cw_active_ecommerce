<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Jobs\SendMetaCapiEventJob;
use App\Services\MetaConversionApiService;
use Illuminate\Http\Request;

class MetaCapiController extends Controller
{
    public function event(Request $request, MetaConversionApiService $service)
    {
        $request->validate([
            'event_name' => 'required|string|in:AddToCart,InitiateCheckout,ViewContent',
            'event_id' => 'required|string',
            'event_source_url' => 'required|string',
            'user_data' => 'sometimes|array',
            'custom_data' => 'sometimes|array',
        ]);

        if ($service->isEnabled()) {
            SendMetaCapiEventJob::dispatch(
                $request->input('event_name'),
                $request->input('custom_data', []),
                $request->input('user_data', []),
                $request->input('event_id'),
                $request->input('event_source_url'),
                $request->ip(),
                $request->userAgent()
            );
        }

        return response()->json(['result' => true]);
    }
}
