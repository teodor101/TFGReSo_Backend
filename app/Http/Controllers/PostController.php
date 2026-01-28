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
        try {
            $posts = Post::with('user:id,name,email')
                ->withCount('likes as likes_count')
                ->withCount('comments as comments_count')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'posts' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => 'Error al obtener los posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function feed(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $posts = Post::with('user:id,name,email')
                ->withCount('likes as likes_count')
                ->withCount('comments as comments_count')
                ->withCount([
                    'likes as liked_by_me' => function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) {
                    $post->liked = ($post->liked_by_me ?? 0) > 0;
                    unset($post->liked_by_me);
                    return $post;
                });

            return response()->json([
                'posts' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => 'Error al obtener el feed',
                'error' => $e->getMessage()
            ], 500);
        }
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

            $fields = $request->validate([
                'content' => 'required|string',
                'user_id' => 'required|integer',
            ]);
            
            $post = Post::create([
                'content' => $fields['content'],
                'user_id' => $fields['user_id'],
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
    public function update(Request $request, Post $post)
    {
        try {
            if ($request->user()->id !== $post->user_id) {
                return response([
                    'message' => 'No tienes permiso para editar este post'
                ], 403);
            }

            $fields = $request->validate([
                'content' => 'required|string',
            ]);

            $post->update([
                'content' => $fields['content'],
            ]);

            return response()->json($post, 200);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try{
        $post = Post::findOrFail($id);

        if ($request->user()->id !== $post->user_id) {
            return response([
                'message' => 'No tienes permiso para eliminar este post'
            ], 403);
        }

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
