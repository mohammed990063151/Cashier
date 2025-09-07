<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::when($request->search, function ($q) use ($request) {

            return $q->whereTranslationLike('name', '%' . $request->search . '%');
        })->latest()->paginate(5);

        return view('dashboard.categories.index', compact('categories'));
    } //end of index

    public function create()
    {
        return view('dashboard.categories.create');
    } //end of create

    public function store(Request $request)
{
    $rules = [
        'name' => ['required', 'unique:categories,name'],
    ];

    $messages = [
        'name.required' => 'حقل الاسم مطلوب',
        'name.unique'   => 'الاسم مستخدم مسبقاً',
    ];

    $request->validate($rules, $messages);

    Category::create($request->all());

    session()->flash('success', 'تمت الإضافة بنجاح');
    return redirect()->route('dashboard.categories.index');
}

    // end of store

    public function edit(Category $category)
    {
        return view('dashboard.categories.edit', compact('category'));
    } //end of edit



public function update(Request $request, Category $category)
{
    $rules = [
        'name' => [
            'required',
            Rule::unique('categories', 'name')->ignore($category->id),
        ],
    ];

    $messages = [
        'name.required' => 'حقل الاسم مطلوب',
        'name.unique'   => 'الاسم مستخدم مسبقاً',
    ];

    $request->validate($rules, $messages);

    $category->update($request->all());

    session()->flash('success', 'تم التعديل بنجاح');
    return redirect()->route('dashboard.categories.index');
}

public function restoreCategory($id)
{
    $category = Category::withTrashed()->findOrFail($id);
    $category->restore();

    session()->flash('success', "تم استرجاع التصنيف: #{$category->id}");
    return redirect()->back();
}

    //end of update

    public function destroy(Category $category)
    {
        $category->delete();
        session()->flash('success', __('تم الحذف بنجاح'));
        return redirect()->route('dashboard.categories.index');
    } //end of destroy

}//end of controller
