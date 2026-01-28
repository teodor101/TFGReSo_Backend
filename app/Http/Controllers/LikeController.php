<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function togglePostLike(Request $request, Post $post)
    {
        try {
            $userId = $request->user()->id;

            $existing = Like::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                $existing->delete();
                $liked = false;
            } else {
                Like::create([
                    'post_id' => $post->id,
                    'user_id' => $userId,
                    'comment_id' => null,
                ]);
                $liked = true;
            }

            $likesCount = Like::where('post_id', $post->id)->count();

            return response()->json([
                'post_id' => $post->id,
                'liked' => $liked,
                'likes_count' => $likesCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el like',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

