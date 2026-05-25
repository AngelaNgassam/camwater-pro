<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Operateur;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Détecter si c'est une requête API ou Web
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->apiLogin($request);
        }

        // Login Web
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::guard('web')->attempt(['login' => $credentials['login'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'login' => 'Les identifiants ne sont pas valides.',
        ])->onlyInput('login');
    }

    private function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Identifiants incorrects.'
                ], 401);
            }

            $operateur = JWTAuth::user();

            return response()->json([
                'message' => 'Connexion réussie.',
                'token' => $token,
                'operateur' => [
                    'id' => $operateur->id,
                    'nom' => $operateur->nom,
                    'prenom' => $operateur->prenom,
                    'login' => $operateur->login,
                    'role' => $operateur->role,
                ]
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération du token.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Détecter si c'est une requête API ou Web
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->apiLogout($request);
        }

        // Logout Web
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function apiLogout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'message' => 'Déconnexion réussie.'
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion.'
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $operateur = JWTAuth::parseToken()->authenticate();
            return response()->json($operateur, 200);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide.'
            ], 401);
        }
    }
}
