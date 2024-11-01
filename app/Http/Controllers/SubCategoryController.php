<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allCategories = SubCategory::all();
        $subCategory = SubCategory::paginate($request->input('limit', 10));
        $finalResult = $request->input('limit') ? $subCategory : $allCategories;
        return $finalResult;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $subCategory = new SubCategory();
        $request->validate([
            'category_id'=> 'required|exists:categories,id',
            'title' => 'required',
            'image' => 'nullable|image',
        ]);
        $subCategory->title = $request->title;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = date('YmdHis'). 'subCategory' . '.' . $file->getClientOriginalExtension();
            $path = 'images';
            $file->move($path, $filename);
            $subCategory->image = url('/') . '/images/' . $filename;
        }
        $subCategory->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subCategory, $id)
    {
        return SubCategory::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategory $subCategory, $id, Request $request)
    {
        $subCategory = SubCategory::findOrFail($id);
        $request->validate([
            'title' => 'required',
            'category_id'=> 'required|exists:categories,id',
        ]);
        $subCategory->title = $request->title;
        if ($request->hasFile('image')) {
            $oldpath = public_path() . '/images/' . substr($subCategory['image'], strrpos($subCategory['image'], '/') + 1);

            if (File::exists($oldpath)) {
                File::delete($oldpath);
            }
            $file = $request->file('image');
            $filename = date('YmdHis') . 'subCategory' . '.' . $file->getClientOriginalExtension();
            $subCategory->image = url('/') . '/images/' . $filename;
            $path = 'images';
            $file->move($path, $filename);
        }
        $subCategory->save();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $subCategory)
    {
        //
    }

     // Search On Users
     public function search(Request $request)
     {
            $query = $request->input('title');
            $results = SubCategory::where('title', 'like', "%$query%")->get();
            return response()->json($results);
     }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory, $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $path = public_path() . '/images/' . substr($subCategory['image'], strrpos($subCategory['image'], '/') + 1);

        if (File::exists($path)) {
            File::delete($path);
        }
        $subCategory->delete();
    }
}
