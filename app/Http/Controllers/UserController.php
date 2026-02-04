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
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            $posts = $user->posts()
                ->withCount('likes as likes_count')
                ->withCount('comments as comments_count')
                ->orderBy('created_at', 'desc')
                ->get();

            return response([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'followers_count' => $user->followers()->count(),
                    'following_count' => $user->following()->count(),
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
}
