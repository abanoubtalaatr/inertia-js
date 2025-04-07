<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'image' => $this->image,
            'title' => $this->title ?? '',
            'description' => $this->description ?? '',
            'image_url' => ImageHelper::getFullImageUrl($this->image),
            'base_path' => \request()->getSchemeAndHttpHost().'/storage/',
            // 'translations' => $this->translations->map(function ($translation) {
            //     return [
            //         'locale' => $translation->locale,
            //         'title' => $translation->title,
            //         'description' => $translation->description,
            //     ];
            // }),
            'sections' => $this->sections ? $this->sections->map(function ($section) {
                return [
                    'type' => $section->type,
                    'title' => $section->title,
                    'description' => $section->description,
                    'image' => $this->image,
                    'image_url' => ImageHelper::getFullImageUrl($this->image),
                    'base_path' => \request()->getSchemeAndHttpHost().'/storage/',
                    //         'translations' => $section->translations->map(function ($translation) {
                    //             return [
                    //                 'locale' => $translation->locale,
                    //                 'title' => $translation->title,
                    //                 'description' => $translation->description,
                    //             ];
                    //         }),
                ];
            }) : [],
        ];
    }
}
