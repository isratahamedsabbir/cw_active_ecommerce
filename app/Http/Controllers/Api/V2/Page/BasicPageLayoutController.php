<?php

namespace App\Http\Controllers\Api\V2\Page;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\BusinessSettingService;
use Exception;

class BasicPageLayoutController extends Controller
{
    protected $businessSettingService;

    public function __construct(BusinessSettingService $businessSettingService)
    {
        $this->businessSettingService = $businessSettingService;
    }

    public function show()
    {
        try {
            $typeKeys = [
                'site_icon',
                'header_logo',
                'topbar_banner_link',
                'helpline_number',
                'helpline_email',
                'footer_title',
                'footer_address',
                'footer_logo',
                'copyright',
                'contact_email',
                'contact_phone',
                'contact_whatsapp',
                'facebook_link',
                'twitter_link',
                'instagram_link',
                'youtube_link',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'meta_image'
            ];
            $singleImage = ['site_icon', 'header_logo', 'footer_logo', 'meta_image'];
            $multipleImage = [];
            $filteredCms = $this->businessSettingService->dataLoad($typeKeys, $singleImage, $multipleImage);

            $filteredCms['seo'] = [
                'meta_title' => $filteredCms['meta_title'] ?? null,
                'meta_description' => $filteredCms['meta_description'] ?? null,
                'meta_keywords' => $filteredCms['meta_keywords'] ?? null,
                'meta_image' => $filteredCms['meta_image'] ?? null,
            ];
            unset($filteredCms['meta_title'], $filteredCms['meta_description'], $filteredCms['meta_keywords'], $filteredCms['meta_image']);

            $pages = Page::where('status', 1)->get(['id', 'title', 'slug']);
            $pages->map(function ($page) {
                $page->url = config('app.frontend') .'/'. $page->slug;
                return $page;
            });

            $data = [
                'status' => true,
                'message' => 'fetched successfully',
                'data' => $filteredCms,
                'pages' => $pages
            ];
        } catch (Exception $e) {
            $data = [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
        return response()->json($data, 200);
    }
}
