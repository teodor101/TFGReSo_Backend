<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, Post $post)
    {
        try {
            // Autenticación opcional: si viene Bearer token, computamos "liked"
            $authUser = auth('sanctum')->user();
            $userId = $authUser?->id;

            $query = Comment::where('post_id', $post->id)
                ->with('user:id,name,email')
                ->withCount('likes as likes_count')
                ->orderBy('created_at', 'asc');

            if ($userId) {
                $comments = $query
                    ->withCount([
                        'likes as liked_by_me' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        }
                    ])
                    ->get()
                    ->map(function ($comment) {
                        $comment->liked = ($comment->liked_by_me ?? 0) > 0;
                        unset($comment->liked_by_me);
                        return $comment;
                    });
            } else {
                $comments = $query->get();
            }

            return response()->json([
                'comments' => $comments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los comentarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, Post $post)
    {
        try {
            $fields = $request->validate([
                'comment' => 'required|string|max:255',
            ]);

            $comment = Comment::create([
                'comment' => $fields['comment'],
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
            ]);

            $comment->load('user:id,name,email');
            $comment->likes_count = 0;
            $comment->liked = false;

            return response()->json([
                'comment' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el comentario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, Comment $comment)
    {
        try {
            if ($request->user()->id !== $comment->user_id) {
                return response()->json([
                    'message' => 'No tienes permiso para eliminar este comentario',
                ], 403);
            }

            // Evitar likes huérfanos
            Like::where('comment_id', $comment->id)->delete();

            $comment->delete();

            return response()->json([
                'message' => 'Comentario eliminado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el comentario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

