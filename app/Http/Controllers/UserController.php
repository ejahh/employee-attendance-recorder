<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'user_name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone_number' => 'required',
            'profile_photo' => 'nullable|file|image|max:2048',
            'user_type' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'A required field is missing or invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $validated['password'] = bcrypt($validated['password']);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $validated['profile_photo'] = $path;
        }

        $user = User::create($validated);

        return response()->json([
            'message' => 'User added successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => 'No user exists with the provided ID.'
            ], 404);
        }
    }

       /**
     * Display a listing of the resources by multiple IDs.
     */
    public function multiple(Request $request)
    {
        $ids = $request->query('ids');
        if (!$ids) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }
        $idArray = array_map('intval', explode(',', $ids));
        $users = User::whereIn('id', $idArray)->get();
        return response()->json($users);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required',
            'middle_name' => 'nullable',
            'last_name' => 'sometimes|required',
            'user_name' => 'sometimes|required|unique:users,user_name,' . $id,
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|min:8',
            'phone_number' => 'nullable',
            'profile_photo' => 'nullable|file|image|max:2048',
            'user_type' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'A required field is missing or invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }

    /**
     * Bulk update multiple users.
     * Accepts an array of updates: [{id: 1, field1: value, ...}, ...]
     */
    public function updateMultiple(Request $request)
    {
        $updates = $request->input('updates');
        if (!is_array($updates) || empty($updates)) {
            return response()->json([
                'message' => 'No updates provided',
                'errors' => ['updates' => ['The updates array is required.']]
            ], 422);
        }

        $results = [];
        foreach ($updates as $update) {
            if (!isset($update['id'])) {
                $results[] = [
                    'id' => null,
                    'status' => 'failed',
                    'errors' => ['id' => ['User ID is required.']]
                ];
                continue;
            }
            $user = User::find($update['id']);
            if (!$user) {
                $results[] = [
                    'id' => $update['id'],
                    'status' => 'failed',
                    'errors' => ['id' => ['User not found.']]
                ];
                continue;
            }
            $validator = Validator::make($update, [
                'first_name' => 'sometimes|required',
                'middle_name' => 'nullable',
                'last_name' => 'sometimes|required',
                'user_name' => 'sometimes|required|unique:users,user_name,' . $user->id,
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|nullable|min:8',
                'phone_number' => 'nullable',
                'profile_photo' => 'nullable',
                'user_type' => 'sometimes|required',
            ]);
            if ($validator->fails()) {
                $results[] = [
                    'id' => $user->id,
                    'status' => 'failed',
                    'errors' => $validator->errors()
                ];
                continue;
            }
            $validated = $validator->validated();
            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }
            $user->update($validated);
            $results[] = [
                'id' => $user->id,
                'status' => 'success',
                'user' => $user
            ];
        }
        return response()->json([
            'message' => 'Bulk update completed',
            'results' => $results
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
 
