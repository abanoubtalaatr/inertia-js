<?php

namespace App\Http\Controllers;

use App\Models\AboutUsSection;
use App\Models\AboutUsItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AboutUsController extends Controller
{
    public function edit()
    {
        $section = AboutUsSection::with('items')->firstOrNew();
        $supportedLanguages = ['en', 'ar', 'fr', 'tl', 'ur'];

        return Inertia::render('Admin/AboutUs/Edit', [
            'aboutUs' => [
                'id' => $section->id,
                'image1' => $section->image_path1 ? Storage::url($section->image_path1) : null,
                'image2' => $section->image_path2 ? Storage::url($section->image_path2) : null,
                'items' => $section->items->map(function($item) use ($supportedLanguages) {
                    $itemData = ['id' => $item->id, 'order' => $item->order];

                    foreach ($supportedLanguages as $lang) {
                        $itemData['translations'][$lang] = [
                            'title' => $item->{"title_$lang"} ?? '',
                            'description' => $item->{"description_$lang"} ?? ''
                        ];
                    }

                    return $itemData;
                })->toArray()
            ],
            'supportedLanguages' => $supportedLanguages
        ]);
    }

    public function update(Request $request)
{
    $supportedLanguages = ['en', 'ar', 'fr', 'tl', 'ur'];

    // Handle file uploads
    $data = [
        'image_path1' => $this->getStoragePath($request->image_path1),
        'image_path2' => $this->getStoragePath($request->image_path2)
    ];

    if ($request->hasFile('image1')) {
        if ($request->image_path1) {
            Storage::disk('public')->delete($this->getStoragePath($request->image_path1));
        }
        $data['image_path1'] = $request->file('image1')->store('about-us-images', 'public');
    }

    if ($request->hasFile('image2')) {
        if ($request->image_path2) {
            Storage::disk('public')->delete($this->getStoragePath($request->image_path2));
        }
        $data['image_path2'] = $request->file('image2')->store('about-us-images', 'public');
    }

    $section = AboutUsSection::updateOrCreate(
        ['id' => $request->id ?? null],
        $data
    );

    // Handle items
    if ($request->items) {
        $section->items()->delete();

        foreach ($request->items as $itemData) {
            $itemFields = ['order' => $itemData['order'] ?? 0];

            foreach ($supportedLanguages as $lang) {
                // Check both possible structures
                if (isset($itemData['translations'][$lang])) {
                    // New structure with translations array
                    $itemFields["title_$lang"] = $itemData['translations'][$lang]['title'] ?? '';
                    $itemFields["description_$lang"] = $itemData['translations'][$lang]['description'] ?? '';
                } else {
                    // Old direct property structure
                    $itemFields["title_$lang"] = $itemData["title_$lang"] ?? '';
                    $itemFields["description_$lang"] = $itemData["description_$lang"] ?? '';
                }
            }

            $section->items()->create($itemFields);
        }
    }

    return redirect()->back()->with('success', 'About Us section updated successfully');
}

    protected function getStoragePath($path)
    {
        if (!$path) return null;

        if (strpos($path, '/storage/') !== false) {
            return str_replace('/storage/', '', $path);
        }

        return $path;
    }
}