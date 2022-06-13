<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Throwable;

class ProductImageController extends Controller
{
    //show
    public function Index()
    {
        $images = ProductImage::all();
        if (count($images) > 0) {
            return response()->json([
                'data' => $images,
                'success' => true,
                'message' => 'Images were fetched'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No Images were fetched'
            ]);
        }

    }

    //create
    public function Create(Request $request)
    {
        if (!$request->hasFile('images')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        $allowedfileExtension = ['pdf', 'jpg', 'png'];
        $files = $request->file('images');
        $errors = [];

        foreach ($files as $file) {

            $extension = $file->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);

            if ($check) {
                $rand = rand('111111111', '999999999');
                foreach ($request->images as $mediaFiles) {

                    $path = $mediaFiles->store('public/images');
                    $name = $mediaFiles->getClientOriginalName();

                    //store image file into directory and db
                    $save = new ProductImage();
                    $save->random_id = $rand;
                    $save->image = $path;
                    $save->save();
                }
            } else {
                return response()->json(['invalid_file_format'], 422);
            }

            return response()->json(['file_uploaded'], 200);

        }
    }

    public function store(Request $request)
    {

        if (!$request->hasFile('image')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        $allowedfileExtension = ['pdf', 'jpg', 'png'];
        $files = $request->file('image');
        $errors = [];
        $i = 0;

        foreach ($files as $file) {

            $extension = $file->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);

            /** If the request has image */
            if ($check !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wrong Image Format'
                ]);
            }
        }

        if ($check) {
            $rand = rand('111111111', '999999999');
            foreach ($request->image as $key => $image) {

                /** Make a new filename with extension */
                $filename = time() .$key. rand(1, 30) . '.' . $image->getClientOriginalExtension();

                /**
                 * Get real image path using
                 * @class Intervention\Image\Facades\Image
                 *
                 */
                $img = Image::make($image->getRealPath());

                /** Set image dimension to conserve aspect ratio */
                $img->fit(300, 300);

                /** Get image stream to store the image else the tmp file will be stored */
                $img->stream();

                /** Make a new filename with extension */
                $path = Storage::disk('local')->put('public/image2/' . $filename, $img);

                /** Update the image index in the data array to update the image path to be stored in database */
                $data['image'] = $filename;

                /** Insert the data in the database */
                $product = new ProductImage();
                $i = $i + 1;
                $product->random_id = $rand;
                $product->image = $data['image'];
                $product->save();

            }
        }
        $count = count($request->image);
        if ($i === $count) {
            return response()->json([
                'data' => $product,
                'success' => true,
                'message' => 'Image created successfully'
            ]);
        } elseif ($i !== $count || $i < $count) {
            $remaining = $count - $i;
            return response()->json([
                'data' => $product,
                'success' => true,
                'message' => $i . ' created successfully ,' . $remaining . ' remained',
            ]);
        } else {
            return response()->json([
                'data' => $product,
                'success' => false,
                'message' => 'Image creation unsuccessful'
            ]);
        }
    }

    public function Update(Request $request, $id)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        if (count($request->image) > 1) {
            return response()->json(['multiple_image_uploaded'], 400);
        }
        $data = $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($data) {
            $rand = rand('111111111', '999999999');
            $image = $request->image;

            /** Make a new filename with extension */
            $filename = time() . rand(1, 30) . '.' . $image->getClientOriginalExtension();

            /**
             * Get real image path using
             * @class Intervention\Image\Facades\Image
             *
             */
            $img = Image::make($image->getRealPath());

            /** Set image dimension to conserve aspect ratio */
            $img->fit(300, 300);

            /** Get image stream to store the image else the tmp file will be stored */
            $img->stream();

            /** Make a new filename with extension */
            $path = Storage::disk('local')->put('public/image2/' . $filename, $img);

            /** Update the image index in the data array to update the image path to be stored in database */
            $data['image'] = $filename;

            $model = ProductImage::where('id', $id)->first();
            if (!$model) {
                return response()->json(['id_not_found'], 400);
            }
            /** Check Image in our Storage Folder to update */
            if (Storage::disk('local')->exists('public/image2/' . $model->image)) {
                Storage::disk('local')->delete('public/image2/' . $model->image);
            }
            /** Insert the data in the database */
            $model->image = $data['image'];
            $model->save();

        }
        if ($model->save()) {
            return response()->json([
                'data' => $model,
                'success' => true,
                'message' => 'Image Update successfully'
            ]);
        } else {
            return response()->json([
                'data' => $model,
                'success' => false,
                'message' => 'Image Updation unsuccessful'
            ]);
        }
    }

    public function Delete($id)
    {
        if ($id) {
            $model = ProductImage::find($id);
            if ($model) {
                if (Storage::disk('local')->exists('public/image2/' . $model->image)) {
                    Storage::disk('local')->delete('public/image2/' . $model->image);
                }
                $model->delete();
                return response()->json([
                    'data' => $model,
                    'success' => true,
                    'message' => 'Image Delete successfully'
                ]);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Image Deletion unsuccessful'
        ]);

    }
}
