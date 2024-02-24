<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

//use File;

class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();
        if(!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
        }
        $category = $categories->paginate(10);//latest = orderBy('created_at','DESC')
        $data['categories'] = $category;
        //dd($category);
        return view('admin.category.list',$data);
    }
    public function create(){
        return view('admin.category.create');
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);
        if($validator->passes()){
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            // save image here
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // generate image
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
               // echo $dPath;exit();
                // Image::make($sPath)->resize(450,600)->save($dPath);
                $manager = new ImageManager(new Driver());
                $img = $manager->read($sPath);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->toJpeg(80)->save($dPath);
                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->flash('success','Category added successfully');

            return response()->json([
                'status' => 'true',
                'message' => 'Category added successfully'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit($id, Request $request){
        $category = Category::find($id);
        $data['category'] = $category;
        if(empty($category)){
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit',$data);
    }
    public function update($id, Request $request){
        $category = Category::find($id);
        if(empty($category)){
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
        ]);
        if($validator->passes()){
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            $oldImage = $category->image;
            // save image here
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'-'.time().'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // generate image
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
               // echo $dPath;exit();
                // Image::make($sPath)->resize(450,600)->save($dPath);
                $manager = new ImageManager(new Driver());
                $img = $manager->read($sPath);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->toJpeg(80)->save($dPath);

                //Delete old image here
                $deleted = File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
                File::delete(public_path().'/uploads/category/'.$oldImage);
                $category->image = $newImageName;
                $category->save();
            }
            $request->session()->flash('success','Category updated successfully');
            return response()->json([
                'status' => 'true',
                'message' => 'Category updated successfully'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function destroy($id, Request $request){
        $categpry = Category::find($id);
        if(empty($category)){
            return redirect()->route('category.index');
        }
        $deleted = File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
        File::delete(public_path().'/uploads/category/'.$oldImage);

        $categpry->delete();
        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
