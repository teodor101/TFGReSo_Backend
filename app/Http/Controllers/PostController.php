<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        try {
            $request->merge([
                'user_id' => $request->user()->id,
            ]);

            $post = $request->validate([
                'content' => 'required|string',
                'user_id' => 'required|integer',
            ]);

            return response()->json($post, 201);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
        $post = Post::findOrFail($id);
        $post->delete();
        
         
        return response(
                'Post Eliminado'
            , 200);
        }catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }
}
