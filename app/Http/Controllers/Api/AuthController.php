<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            // |confirmed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('admin');

        // $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل المستخدم بنجاح',
            'user' => $user,
            // 'token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 90
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Pssword or Email',
            ], 401);
        }

        $user = Auth::user();
        $role = $user->roles->pluck('name')->first();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $user->id,
                'workspace_id' => $user->workspace_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
            ],
            // 'expires_in' => auth('api')->factory()->getTTL() * 120
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    public function profile()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'لم يتم العثور على المستخدم'], 404);
        }

        return response()->json($user);
    }

     public function redirectToGoogle()
     {
         return response()->json([
             'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
         ]);
     }

     public function handleGoogleCallback(Request $request)
     {
         try {
             $googleUser = Socialite::driver('google')->stateless()->user();

             $user = User::firstOrCreate(
                 ['email' => $googleUser->getEmail()],
                 [
                     'name' => $googleUser->getName(),
                     'password' => Hash::make(uniqid()),
                 ]
             );

             Auth::login($user);
             $token = JWTAuth::fromUser($user);
             return redirect()->away("http://localhost:3000/auth/callback?token=$token&user=" . urlencode(json_encode($user)));
          
         } catch (\Exception $e) {
             return response()->json(['error' => $e], 500);
         }
     }

    public function redirectToGitHub()
    {
        return Socialite::driver('github')->stateless()->redirect();
    }


    public function handleGitHubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $githubUser->getEmail()],
                [
                    'name' => $githubUser->getName(),
                    'password' => Hash::make(uniqid()),
                ]
            );
            $user = User::where('email', $githubUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                    'email' => $githubUser->getEmail(),
                    'password' => bcrypt(str()->random(12)),
                ]);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                // 'expires_in' => auth('api')->factory()->getTTL() * 120,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }
}
