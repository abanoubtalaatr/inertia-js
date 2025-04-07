<?php

namespace App\Repositories;

use App\Models\Advantage;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdvantageRepository implements AdvantageRepositoryInterface
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function getAll()
    {
        return Advantage::with('translations')->get();
    }

    public function getById($id)
    {
        return Advantage::with('translations')->findOrFail($id);
    }

    public function createAdvantage($request)
    {
        try {
            $data = $request->all();
            $supportedLanguages = config('settings.supportedLanguages');

            $advantage = new Advantage;
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('advantages', 'public');
                $advantage->image = $data['image'];
            }
            $advantage->save();

            if (isset($data['translations'])) {
                foreach ($data['translations'] as $locale => $translation) {
                    if (in_array($locale, $supportedLanguages)) {
                        $advantage->translateOrNew($locale)->title = $translation['title'] ?? '';
                        $advantage->translateOrNew($locale)->description = $translation['description'] ?? '';
                    }
                }
                $advantage->save();
            }

            return $advantage->fresh('translations');
        } catch (\Exception $e) {
            Log::error('Advantage creation failed: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function updateAdvantage($request, $id)
    {

        try {
            $advantage = Advantage::findOrFail($id);
            $data = $request->all();

            $supportedLanguages = config('settings.supportedLanguages');

            if (isset($data['translations'])) {
                foreach ($data['translations'] as $locale => $translation) {
                    if (in_array($locale, $supportedLanguages)) {
                        $advantage->translateOrNew($locale)->title = $translation['title'] ?? '';
                        $advantage->translateOrNew($locale)->description = $translation['description'] ?? '';
                    }
                }
            }

            if ($request->hasFile('image')) {

                if ($advantage->image && Storage::disk('public')->exists($advantage->image)) {
                    $this->fileUploadService->deleteFile($advantage->image);
                }
                $imagePath = $request->file('image')->store('advantages', 'public');
                $advantage->image = $imagePath;
            }

            $advantage->save(); // Save all changes (translations and image) at once

            return $advantage->fresh('translations');
        } catch (\Exception $e) {
            Log::error('Advantage update failed: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function deleteAdvantage($id)
    {
        try {
            $advantage = Advantage::findOrFail($id);
            if ($advantage->image && Storage::disk('public')->exists($advantage->image)) {
                $this->fileUploadService->deleteFile($advantage->image);
            }
            $advantage->delete(); // This will also delete translations due to cascading

            return true;
        } catch (\Exception $e) {
            Log::error('Advantage deletion failed: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
