<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        return view('categories.index', [
            'active' => 'categories',
            'title' => 'Categorías',
            'subtitle' => 'Administra las categorías disponibles para los tickets',
            'categories' => Category::orderBy('name')->paginate(10),
        ]);
    }

    public function create()
    {
        return view('categories.create', [
            'active' => 'categories',
            'title' => 'Nueva categoría',
            'subtitle' => 'Registra una categoría para clasificar las solicitudes',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('status', 'Categoría creada correctamente.');
    }

    public function show(Category $category)
    {
        return view('categories.show', [
            'active' => 'categories',
            'title' => 'Detalle de categoría',
            'subtitle' => 'Información de la categoría',
            'category' => $category,
        ]);
    }

    public function edit(Category $category)
    {
        return view('categories.edit', [
            'active' => 'categories',
            'title' => 'Editar categoría',
            'subtitle' => 'Actualiza el nombre de la categoría',
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->exists()) {
            return back()->withErrors(['category' => 'No se puede eliminar una categoría con tickets asociados.']);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('status', 'Categoría eliminada correctamente.');
    }
}
