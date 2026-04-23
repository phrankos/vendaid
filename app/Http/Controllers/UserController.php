<?php

namespace App\Http\Controllers;
use App\Models\Role;
use App\Models\User;

use Hash;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // public function index()
    // {
    //     $data = User::all();
    //     $headers = [
    //         'name' => ['id' =>  'ID',
    //             'name' =>  "Name",
    //             'email' =>  "Email Address",
    //             'role_id' =>  'Role',
    //             'email_verified_at' =>  "Verified At",
    //             'created_at' => "Created At",
    //             'updated_at' =>  "Updated At"
    //         ],
    //         'type' => ['id' =>  'numeric',
    //             'email_verified_at' =>  "datetime",
    //             'created_at' => "datetime",
    //             'updated_at' =>  "datetime"
    //         ],
    //         'dropdown' => ['role_id' => 'role',
    //         ]
    //     ];

    //     return Inertia::render('Users', [
    //         'data' => $data,
    //         'headers' => $headers,
    //         'dropdownOptions' => [
    //             'role_id' => Role::all(["id", "role"]),
    //         ],
    //     ]);
    // }

    public function update(Request $request)
    {
        // Log::channel('stderr')->Info("update request");
        // Log::channel('stderr')->Info($request);
        $id = $request->input('id');
        $user = User::find($id);

        $request->validate([            
            'name' => 'required',
            'email' => 'required',
            'role_id' => 'required|numeric',
        ]);
        if ($request->input('password') != null) {
            $user->update($request->all());
        } else {
            // Log::channel('stderr')->Info("Password is null");
            $user->update($request->except('password'));
        }

        return redirect()->back()->with([
            'success' => 'User updated successfully.',
            'data' => User::all(),
        ]);
            
    }

    public function store(Request $request)
    {
        // Log::channel('stderr')->Info("store request");
        // Log::channel('stderr')->Info($request);
        // Log::channel('stderr')->Info($request->input('password'));
        $user = new User();
        $user->password = $request->input('password');
        $user->fill($request->all());
        $user->save();
        return redirect()->back()->with([
            'success' => 'User created successfully.',
            'data' => User::all()
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->back()->with('success','User Succesfully Deleted');
    }
}
