<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'phone'    => 'required|string|unique:users,phone',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            // Create user
            $user = User::create([
                'phone'      => $validated['phone'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'created_by' => null, // or auth()->id() if created by admin
            ]);

            // Assign default role "racer"
            $defaultRole = Role::where('role_name', 'racer')->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data'    => [
                    'user'  => new UserResource($user->load('roles')),
                    'token' => $token,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 400);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error'   => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Login (email OR phone)
    public function login(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
                'password'   => 'required|string',
            ]);

            $user = User::where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user->tokens()->delete(); // revoke old tokens
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data'    => [
                    'user'  => new UserResource($user->load('roles')),
                    'token' => $token,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated or token missing',
                ], 401);
            }

            $token = $user->currentAccessToken();

            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already logged out',
                ], 200);
            }

            $token->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during logout',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function findUser(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|string', // phone OR email
            ]);

            $user = User::with('roles')
                ->where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User found',
                'data'    => new UserResource($user),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // promote user to be moderator (only administrator can do this)
    public function promoteUser(Request $request, $id)
    {
        try {
            $request->validate([
                'role' => 'required|string|in:racer,moderator,administrator',
            ]);

            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Get target role
            $role = Role::where('role_name', $request->role)->first();

            if (! $role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role',
                ], 400);
            }

            // Attach new role if not already assigned
            if (! $user->roles()->where('role_name', $role->role_name)->exists()) {
                $user->roles()->attach($role->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User promoted successfully',
                'data'    => new UserResource($user->load('roles')),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
