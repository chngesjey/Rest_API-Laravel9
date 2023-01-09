<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        // get post
        $posts = Post::latest()->paginate(5);

        // return collection of post as a resource 
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation rules
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create post 
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // return response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan !!', $post);
    }

    public function show(Post $post)
    {
        // return single post as a resource
        return new PostResource(true, 'Data post Ditemukan', $post);
    }

    public function update (Request $request, Post $post)
    {
        // validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation rules
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // check if image is not empty
        if($request->hasFile('image')) {

            // up image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // delete old image
            Storage::delete('public/posts/'.$post->image);

            // update post with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);

        } else {
            // update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }
        // return response
        return new PostResource(true, 'Data Post Telah Berhasil Diubah !', $post);
    }

    public function destroy(Post $post)
    {
        // delete image
        Storage::delete('public/posts/'.$post->image);

        // delete post 
        $post->delete();

        // return response
        return new PostResource(true, 'Data Post Telah Berhasil Dihapus', null);
    }
}
