<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User as UserModel;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = UserModel::all();
        if ($users->count() > 0) {
            return response()->json(
                $users,
                200
            );
        } else {
            return response()->json([
                'users' => 'No users found',
            ], 400);
        }


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Hash the password
            $hashedPassword = Hash::make($request->input('password'));

            // Create new user
            $newUser = UserModel::create([
                'username' => $request->input('username'),
                'password' => $hashedPassword,
                'email' => $request->input('email'),
                'phone_number' => $request->input('phone_number'),
                'address_id' => $request->input('address_id'),
                'permission' => $request->input('permission'),
                'iban' => $request->input('iban'),
                'nif' => $request->input('nif'),
                'holder' => $request->input('holder'),
                'enabled' => true,
            ]);

            return response()->json([
                'user' => $newUser,
                'token' => JWTAuth::fromUser($newUser, ['exp' => time() + 60 * 60 * 24 * 3]),
            ], 201);
        } catch (\Exception $e) {
            // Log error
            \Log::error('Error creating user: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'error' => 'Erro ao criar usuário',
                'erro' => $e
            ], 400);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validate the request data
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required',
            ]);
            // Retrieve user by username
            $user = UserModel::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json(['error' => 'Credenciais inválidas'], 401);
            }

            // Verify the password
            if (!Hash::check($request->input('password'), $user->password)) {
                return response()->json(['error' => 'Credenciais inválidas'], 401);
            }

            // Authentication successful
            return response()->json([
                'user' => $user,
                'token' => JWTAuth::fromUser($user, ['exp' => 60 * 24 * 3]),
            ]);
        } catch (\Exception $e) {
            // Log error
            \Log::error('Error authenticating user: ' . $e->getMessage());

            // Return error response
            return response()->json(['error' => 'Erro ao fazer login'], 400);
        }

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the user by ID
        $user = UserModel::find($id);
        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'User not found'], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = UserModel::find($id);

        // Get all request data
        $updateData = $request->all();

        // If a password is provided, hash it before updating
        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        // Update the user
        $user->update($updateData);

        return response()->json([
            'user' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the user by ID
        $user = UserModel::find($id);

        // Check if user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Disable the user
        $user->enabled = false;
        $user->save();

        // Disable associated addresses
        $user->addresses()->update(['enabled' => false]);

        $user->formulas()->update(['enabled' => false]);

        return response()->json(['message' => 'User disabled successfully'], 200);
    }

    public function userAddress($id)
    {
        $user = UserModel::with('address')->find($id);

        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'User not found'], 400);
        }
    }

    public function getUsersWithAddresses()
    {
        $users = UserModel::with('address')->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 400);
        }

        return response()->json(['users' => $users], 200);
    }

    public function getAllPharmacies()
    {
        $pharmacies = UserModel::where('permission', 1)->get();

        if ($pharmacies->isEmpty()) {
            return response()->json(['message' => 'No pharmacies found'], 400);
        }

        return response()->json($pharmacies, 200);
    }
}
