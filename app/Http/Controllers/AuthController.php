<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'error' => 'Credenciales inválidas'
            ], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'id' => User::where('username', $request->username)->value('id')
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}


?>