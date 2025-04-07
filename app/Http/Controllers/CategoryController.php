<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

// use App\Exports\${this.modelName}Export;
// use App\Imports\${this.modelName}Import;
class CategoryController extends Controller
{
    protected $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;

        $this->middleware('permission:read users', ['only' => ['index']]);
        $this->middleware('permission:create users', ['only' => ['create']]);
        $this->middleware('permission:update users', ['only' => ['update', 'edit']]);
        $this->middleware('permission:delete users', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = $this->repository->getAll();

        return inertia('Category/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No additional data needed
        return inertia('Category/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validated();
        $categorie = $this->repository->create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $categorie)
    {
        $categorie = $this->repository->find($categorie->id);

        return inertia('Category/Show', [
            'categorie' => $categorie,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $categorie)
    {
        $categorie = $this->repository->find($categorie->id);

        // No additional data needed
        return inertia('Category/Edit', [
            'categorie' => $categorie,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $categorie)
    {
        $validated = $request->validated();
        $this->repository->update($validated, $categorie->id);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $categorie)
    {
        $this->repository->delete($categorie->id);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Export the resource to Excel.
     */
    public function export()
    {
        return Excel::download(new CategoryExport, 'categories.xlsx');
    }

    /**
     * Import the resource from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        Excel::import(new CategoryImport, $request->file('file'));

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category imported successfully.');
    }

    /**
     * Get available filters
     */
    public function getFilters()
    {
        return response()->json([
            'filters' => Category::getAvailableFilters(),
        ]);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        cache()->tags('categories')->flush();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Cache cleared successfully.');
    }
}
