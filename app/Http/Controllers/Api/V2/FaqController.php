<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\FaqCollection;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        return new FaqCollection(Faq::with('products')->latest()->get());
    }
}
