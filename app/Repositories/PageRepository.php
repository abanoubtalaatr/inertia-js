<?php

namespace App\Repositories;

use App\Models\Page;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;

class PageRepository implements PageRepositoryInterface
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function getAll()
    {
        return Page::with('translations')->get();
    }

    public function getBySlug(string $slug, $locale)
    {
        return Page::with('translations')->where('slug', $slug)->firstOrFail();
    }

    public function getPageWithSections(string $slug)
    {
        return Page::with('translations', 'sections', 'sections.translations')->where('slug', $slug)->firstOrFail();
    }

    public function updatePage($request, $pageId)
    {
        $page = Page::findOrFail($pageId);
        $data = $request->all();
        $supportedLanguages = config('settings.supportedLanguages'); // ['ar', 'en', 'fr', 'tl', 'ur']

        // Update page translations
        if (isset($data['translations'])) {
            foreach ($data['translations'] as $locale => $translation) {
                if (in_array($locale, $supportedLanguages)) {
                    $page->translateOrNew($locale)->title = $translation['title'] ?? '';
                    $page->translateOrNew($locale)->description = $translation['description'] ?? '';
                }
            }
            $page->save();
        }

        // Update page image
        if ($request->hasFile('image')) {
            if ($page->image && Storage::disk('public')->exists($page->image)) {
                $this->fileUploadService->deleteFile($page->image);
            }
            $data['image'] = $request->file('image')->store('pages', 'public');
        } else {
            unset($data['image']);
        }

        // Update sections
        if (isset($data['sections'])) {
            foreach ($data['sections'] as $sectionData) {
                $section = $page->sections()->updateOrCreate(
                    ['id' => $sectionData['id'] ?? null],
                    ['type' => $sectionData['type'] ?? null]
                );

                // Update section image
                if (isset($sectionData['image']) && $sectionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    if ($section->image) {
                        Storage::disk('public')->delete($section->image);
                    }
                    $section->image = $sectionData['image']->store('sections', 'public');
                    $section->save();
                }

                // Update section translations
                if (isset($sectionData['translations'])) {
                    $section->translations()->delete();
                    foreach ($sectionData['translations'] as $locale => $translation) {
                        if (in_array($locale, $supportedLanguages)) {
                            $section->translateOrNew($locale)->fill([
                                'title' => $translation['title'] ?? '',
                                'description' => $translation['description'] ?? '',
                            ]);
                        }
                    }
                    $section->save();
                }
            }
        }

        // Update page data
        $page->update(collect($data)->except(['translations', 'sections'])->toArray());

        return $page->fresh(['translations', 'sections', 'sections.translations']);
    }
}
