<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;

class SubCategoryController extends Controller
{
    public function index(Request $request){
        $sub_categories = SubCategory::select('sub_categories.*','categories.name as category_name')
        ->latest('sub_categories.id')
        ->join('categories','categories.id','sub_categories.category_id');
        if(!empty($request->get('keyword'))){
            $sub_categories = $sub_categories->where('sub_categories.name','like','%'.$request->get('keyword').'%');
            $sub_categories = $sub_categories->orWhere('categories.name','like','%'.$request->get('keyword').'%');
        }
        $sub_categories = $sub_categories->paginate(10);//latest = orderBy('created_at','DESC')
        $data['sub_categories'] = $sub_categories;
        //dd($category);
        return view('admin.sub_category.list',$data);
    }
    public function create(){
        $category = Category::orderBy('name','ASC')->get();
        $data['categories'] = $category;
        return view('admin.sub_category.create',$data);
    }
    public function store(Request $request){
       $validator = Validator::make($request->all(),[
        'name' => 'required|alpha',
        'slug' => 'required|unique:sub_categories',
        'category' => 'required'
       ]);

       if($validator->passes() ){
            $sub_category = new SubCategory();
            $sub_category->name = $request->name;
            $sub_category->slug = $request->slug;
            $sub_category->status = $request->status;
            $sub_category->category_id = $request->category;
            $sub_category->save();

            $request->session()->flash('success','Sub Category created successfully');

            return response([
                'status' => true,
                'message' => 'Sub Category created successfully!'
            ]);

       }
       else{
        return response([
            'status' => false,
            'errors' => $validator->errors()
        ]);
       }
    }
    public function edit($id, Request $request){
        $subcategory = SubCategory::find($id);
        if(empty($subcategory)){
            $request->session()->flash('error','Record not Found');
            return redirect()->route('sub_categories.index');
        }
        $category = Category::orderBy('name','ASC')->get();
        $data['categories'] = $category;
        $data['subCategory'] = $subcategory;
        return view('admin.sub_category.edit',$data);
    }
    public function update($id, Request $request){
        $subcategory = SubCategory::find($id);
        if(empty($subcategory)){
            $request->session()->flash('error','Record not Found');
            return response([
                'status' => false,
                'notFound' => true,
            ]);
        }
        $validator = Validator::make($request->all(),[
         'name' => 'required|alpha',
         'slug' => 'required|unique:sub_categories,slug,'.$subcategory->id.',id',
         'category' => 'required',
         'status' => 'required'
        ]);

        if($validator->passes() ){
             $subcategory->name = $request->name;
             $subcategory->slug = $request->slug;
             $subcategory->status = $request->status;
             $subcategory->category_id = $request->category;
             $subcategory->save();

             $request->session()->flash('success','Sub Category updated successfully!');
             return response([
                 'status' => true,
                 'message' => 'Sub Category updated successfully!'
             ]);
        }
        else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
     }
     public function destroy($id, Request $request){
        $subcategory = SubCategory::find($id);
        if(empty($subcategory)){
            $request->session()->flash('error','Sub Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Sub Category not found!'
            ]);
        }
        $request->session()->flash('success',' Sub Category Deleted successfully');
        $subcategory->delete();
        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted successfully'
        ]);
    }
}
