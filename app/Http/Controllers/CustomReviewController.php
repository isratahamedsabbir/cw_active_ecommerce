<?php

namespace App\Http\Controllers;

use App\Models\CustomReview;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;

class CustomReviewController extends Controller
{
    public $route = 'custom-reviews';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort_search = $request->search;

        $custom_reviews = CustomReview::when($sort_search, function ($q, $search) {
            return $q->where('name', 'like', '%' . $search . '%');
        })->orderBy('id', 'desc')->paginate(15);

        return view('backend.website_settings.custom_review.index', compact('custom_reviews', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.website_settings.custom_review.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|max:250',
            'thumbnail'     => 'nullable|string|max:255',
            'video_link'    => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $customReview = new CustomReview();
            $customReview->name = $data['name'];
            $customReview->thumbnail = $data['thumbnail'] ?? null;
            $customReview->video_link = $data['video_link'] ?? null;
            $customReview->save();

            session()->put('success', 'Custom review created successfully');
        } catch (Exception $e) {
            session()->put('error', $e->getMessage());
        }

        return redirect()->route('custom-reviews.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomReview $customReview, $id)
    {
        $customReview = CustomReview::findOrFail($id);
        return view('backend.website_settings.custom_review.edit', compact('customReview'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|max:250',
            'thumbnail'     => 'nullable|string|max:255',
            'video_link'    => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $customReview = CustomReview::findOrFail($id);
            $customReview->name = $data['name'];
            $customReview->thumbnail = $data['thumbnail'] ?? $customReview->thumbnail;
            $customReview->video_link = $data['video_link'] ?? null;
            $customReview->save();

            session()->put('success', 'Custom review updated successfully');
        } catch (Exception $e) {
            session()->put('error', $e->getMessage());
        }

        return redirect()->route('custom-reviews.edit', $customReview->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = CustomReview::findOrFail($id);

            if ($data->thumbnail) {
                $upload = \App\Models\Upload::find($data->thumbnail);
                if ($upload) {
                    $upload->delete();
                }
            }

            $data->delete();
            flash(translate('Custom review has been deleted successfully'))->success();
        } catch (Exception $e) {
            flash(translate('Something went wrong'))->error();
        }

        return back();
    }

    public function status(Request $request)
    {
        try {
            $data = CustomReview::findOrFail($request->id);
            $data->status = $request->status;
            $data->save();
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
}
