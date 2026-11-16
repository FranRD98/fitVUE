<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Support\SdrImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // El plan nunca se fía del cliente: toda cuenta nace gratuita y solo
        // StripeController la sube a Premium/Pro tras confirmar el pago.
        $user = User::create([
            'name' => $data['name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
            'plan_id' => 1,
        ]);

        try {
            $user->notify(new WelcomeNotification);
        } catch (\Throwable $e) {
            report($e);
        }

        $token = $user->createToken('fitvue')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $token = $user->createToken('fitvue')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        SdrImage::flattenStored($user->profile_image);

        return response()->json($user);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }
}
