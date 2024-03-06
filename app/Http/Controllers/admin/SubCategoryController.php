<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class SubCategoryController extends Controller
{
    public function create(){
        $category = Category::orderBy('name','ASC')->get();
        $data['categories'] = $category;
        return view('admin.sub_category.create',$data);
    }
}
