<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function __construct()
    {
        $permissions = [
            'index' => 'view-blogs',
            'store' => 'create-blogs',
            'update' => 'edit-blogs',
            'destroy' => 'delete-blogs',
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware("permission:{$permission}")->only($method);
        }
    }

    public function index()
    {
        $blogs = Blog::latest()->get();
        return view('dashboard.blogs.index', compact('blogs'));
    }

    public function create()
    {
        $categories = Category::where('status', 1)->get();
        return view('dashboard.blogs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'excerpt'     => 'nullable|string',
            'heading_one' => 'required|string|max:255',
            'description_one' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'tags'        => 'nullable|string',
            'image_one'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_two'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_three' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_four'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image uploads
        $imageFields = ['image_one', 'image_two', 'image_three', 'image_four'];
        $images = [];
        foreach ($imageFields as $field) {
            $images[$field] = null;
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('blog_images'), $filename);
                $images[$field] = 'blog_images/' . $filename;
            }
        }

        Blog::create([
            'title'               => $request->title,
            'excerpt'             => $request->excerpt,
            'heading_one'         => $request->heading_one,
            'description_one'     => $request->description_one,
            'image_one'           => $images['image_one'],
            'heading_two'         => $request->heading_two,
            'description_two'     => $request->description_two,
            'description_two_one'   => $request->description_two_1,
            'description_two_two'   => $request->description_two_2,
            'description_two_three' => $request->description_two_3,
            'description_two_four'  => $request->description_two_4,
            'description_two_five'  => $request->description_two_5,
            'description_two_six'   => $request->description_two_6,
            'description_two_seven' => $request->description_two_7,
            'image_two'           => $images['image_two'],
            'heading_three'       => $request->heading_three,
            'description_three'   => $request->description_three,
            'image_three'         => $images['image_three'],
            'image_four'          => $images['image_four'],
            'tags'                => $request->tags,
            'author'              => $request->author,
            'author_note'         => $request->author_note,
            'created_by'          => Auth::id(),
            'category_id'         => $request->category_id,
            'subcategory_id'      => $request->subcategory_id,
            'header_image_btn'    => $request->header_image_btn,
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog created successfully.');
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $subcategories = Subcategory::where('category_id', $blog->category_id)->get();

        return view('dashboard.blogs.edit', compact('blog', 'categories', 'subcategories'));
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'excerpt'     => 'nullable|string',
            'heading_one' => 'required|string|max:255',
            'description_one' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'tags'        => 'nullable|string',
            'image_one'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_two'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_three' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_four'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image uploads (keep old if not replaced)
        $imageFields = ['image_one', 'image_two', 'image_three', 'image_four'];
        $images = [];
        foreach ($imageFields as $field) {
            $images[$field] = $blog->$field; // keep old image
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('blog_images'), $filename);
                $images[$field] = 'blog_images/' . $filename;
            }
        }

        $blog->update([
            'title'               => $request->title,
            'excerpt'             => $request->excerpt,
            'heading_one'         => $request->heading_one,
            'description_one'     => $request->description_one,
            'image_one'           => $images['image_one'],
            'heading_two'         => $request->heading_two,
            'description_two'     => $request->description_two,
            'description_two_one'   => $request->description_two_1,
            'description_two_two'   => $request->description_two_2,
            'description_two_three' => $request->description_two_3,
            'description_two_four'  => $request->description_two_4,
            'description_two_five'  => $request->description_two_5,
            'description_two_six'   => $request->description_two_6,
            'description_two_seven' => $request->description_two_7,
            'image_two'           => $images['image_two'],
            'heading_three'       => $request->heading_three,
            'description_three'   => $request->description_three,
            'image_three'         => $images['image_three'],
            'image_four'          => $images['image_four'],
            'tags'                => $request->tags,
            'author'              => $request->author, // Updated from form
            'author_note'         => $request->author_note,
            'category_id'         => $request->category_id,
            'subcategory_id'      => $request->subcategory_id,
            'header_image_btn'    => $request->header_image_btn,
            'status'              => $request->status ?? 1,
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog updated successfully.');
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();

        return redirect()->route('blogs.index')->with('success', 'Blog deleted successfully.');
    }
}
