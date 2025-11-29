<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class SubcategoryController extends Controller
{
    public function __construct()
    {
        $permissions = [
            'index'   => 'view-subcategories',
            'store'   => 'create-subcategories',
            'update'  => 'edit-subcategories',
            'destroy' => 'delete-subcategories',
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware("permission:{$permission}")->only($method);
        }
    }

    public function index()
    {
        $subcategories = Subcategory::with(['category', 'creator', 'editor'])->get();
        return view('dashboard.pages.subcategories', compact('subcategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:subcategories,name',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $subcategory = Subcategory::create($request->only([
            'category_id',
            'name',
            'description',
            'status'
        ]));

        return redirect()->route('subcategories.index')->with('success', 'Subcategory created successfully.');
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:subcategories,name,' . $subcategory->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $original = $subcategory->getOriginal();

        $subcategory->update($request->only([
            'category_id',
            'name',
            'description',
            'status'
        ]));

        return redirect()->route('subcategories.index')->with('success', 'Subcategory updated successfully.');
    }

    public function destroy(Subcategory $subcategory)
    {
        $attributes = $subcategory->getAttributes();
        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'Subcategory deleted successfully.');
    }

    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)
            ->where('status', 1)
            ->get();

        return response()->json($subcategories);
    }
}
