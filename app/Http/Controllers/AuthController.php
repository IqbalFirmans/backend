<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function first_register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password'
        ];

        $validator = Validator::make($request->all(), $rules);

        $errors = [];

        if (User::where('email', $request->email)->exists()) {
            $errors['email'] = 'Email already exists';
        }

        if ($request->password !== $request->password_confirmation) {
            $errors['password'] = 'The password field confirmation does not match.';
        }

        if (!empty($errors) && $validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $errors,$validator->errors()
            ], 400);
        }

        User::create($validator->validated());

        return response()->json('Register Success');
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|confirmed'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ],400);

        User::create($validator->validated());

        return response()->json(['message' => 'Register success']);
    }


    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!Auth::attempt($validator->validate())) {
            return response()->json([
                'message' => 'Email or Password incorect'
            ], 400);
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => Auth::user(), 'token' => $token]);
    }

    public function logout(Request $request)
    {
        if (!auth()->guard('sanctum')->check()) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        auth()->guard('sanctum')->user()->tokens()->delete();

        return response()->json(['message' => 'Logout Success']);
    }
}
