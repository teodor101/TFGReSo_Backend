<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $fields = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|confirmed'
            ]);

            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password'])
            ]);

            return response([
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $fields = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string'
            ]);

            $user = User::where('email', $fields['email'])->first();

            if (!$user || !Hash::check($fields['password'], $user->password)) {
                return response([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            $token = $user->createToken('myapptoken')->plainTextToken;

            return response([
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Sesión cerrada',
            ]);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }


    public function profile(Request $request)
    {
        try {

            $user = User::findOrFail($request->user()->id);

            return response([
                'user' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                "eeeerrr"
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $fields = $request->validate([
                'name' => 'required|string',
                'email' => [
                    'required',
                    'string',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
            ]);

            $user->update([
                'name' => $fields['name'],
                'email' => $fields['email'],
            ]);

            return response([
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentUserWithPosts(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $userWithPosts = User::findOrFail($userId)
                ->posts()
                ->withCount('likes as likes_count')
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

            return response([
                'user' => $userWithPosts,
            ], 200);
        } catch (\Exception $e) {
            return response([
                $e->getMessage()
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            
            // Eliminar todos los tokens del usuario
            $user->tokens()->delete();
            
            // Eliminar todos los posts del usuario
            $user->posts()->delete();
            
            // Eliminar el usuario
            $user->delete();

            return response()->json([
                'message' => 'Cuenta eliminada correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => 'Error al eliminar la cuenta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
