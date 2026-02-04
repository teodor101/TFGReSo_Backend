<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Buscar usuarios por nombre o email
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');

            if (empty($query) || strlen($query) < 2) {
                return response([
                    'users' => []
                ], 200);
            }

            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->select('id', 'name', 'email')
                ->limit(20)
                ->get();

            return response([
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => 'Error al buscar usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener perfil público de un usuario
     */
    public function show(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $posts = $user->posts()
                ->withCount('likes as likes_count')
                ->withCount('comments as comments_count')
                ->orderBy('created_at', 'desc')
                ->get();

            $isFollowing = false;
            if ($request->user()) {
                $isFollowing = $request->user()->following()->where('followed_id', $id)->exists();
            }

            return response([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'followers_count' => $user->followers()->count(),
                    'following_count' => $user->following()->count(),
                    'is_following' => $isFollowing,
                ],
                'posts' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => 'Usuario no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Seguir o dejar de seguir a un usuario
     */
    public function toggleFollow(Request $request, $id)
    {
        try {
            $userToFollow = User::findOrFail($id);
            $currentUser = $request->user();

            // No puedes seguirte a ti mismo
            if ($currentUser->id === $userToFollow->id) {
                return response([
                    'message' => 'No puedes seguirte a ti mismo'
                ], 400);
            }

            $isFollowing = $currentUser->following()->where('followed_id', $id)->exists();

            if ($isFollowing) {
                // Dejar de seguir
                $currentUser->following()->detach($id);
                $message = 'Has dejado de seguir a ' . $userToFollow->name;
            } else {
                // Seguir
                $currentUser->following()->attach($id);
                $message = 'Ahora sigues a ' . $userToFollow->name;
            }

            return response([
                'message' => $message,
                'is_following' => !$isFollowing,
                'followers_count' => $userToFollow->followers()->count()
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

