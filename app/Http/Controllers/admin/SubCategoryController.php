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
        'name' => 'required',
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
}
