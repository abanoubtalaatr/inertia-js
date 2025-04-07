<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Advantage\StoreAdvantageRequest;
use App\Http\Requests\Admin\Advantage\UpdateAdvantageRequest;
use App\Models\Advantage;
use App\Repositories\AdvantageRepositoryInterface;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;

class AdvantageWebController extends Controller
{
    protected $repository;

    protected $fileUploadService;

    public function __construct(AdvantageRepositoryInterface $repository, FileUploadService $fileUploadService)
    {
        $this->repository = $repository;
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $advantages = $this->repository->getAll();

        return inertia('Advantages/Index', ['advantages' => $advantages]);
    }

    public function create()
    {
        return inertia('Advantages/Create');
    }

    public function store(StoreAdvantageRequest $request)
    {
        $this->repository->createAdvantage($request);

        return redirect()->route('advantages.index')->with('success', __('messages.data_created_successfully'));
    }

    public function edit($id)
    {
        $advantage = $this->repository->getById($id);

        return inertia('Advantages/Edit', ['advantage' => $advantage]);
    }

    public function update(UpdateAdvantageRequest $request, $id)
    {
        $advantage = Advantage::findOrFail($id);
        $data = $request->all();
        \Illuminate\Support\Facades\Log::info('Request Data:', $data); // Log incoming data
        // dd($data); // Uncomment to debug

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

        $advantage->save();

        return redirect()->route('advantages.index')->with('success', __('messages.data_updated_successfully'));
    }

    public function destroy($id)
    {
        $this->repository->deleteAdvantage($id);

        return redirect()->route('advantages.index')->with('success', __('messages.data_deleted_successfully'));
    }
}
