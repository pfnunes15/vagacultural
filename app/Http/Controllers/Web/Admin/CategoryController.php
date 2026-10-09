<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->with('parent')->orderBy('position')->orderBy('id')->paginate(50),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true]),
            'parents' => Category::query()->orderBy('position')->get(),
            'locales' => config('locales.supported'),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->payload();
        $data['slug'] = Slug::unique(Category::class, $data['name'][config('locales.default')] ?? 'category');
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Categoria criada.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()->whereKeyNot($category->id)->orderBy('position')->get(),
            'locales' => config('locales.supported'),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->payload());

        return redirect()->route('admin.categories.index')->with('status', 'Categoria atualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Categoria removida.');
    }
}
