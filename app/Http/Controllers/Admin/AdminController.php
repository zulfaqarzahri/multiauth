<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Admin;
use App\Models\User;

class AdminController extends Controller
{
    function check(Request $request){
        $request->validate([
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|min:5|max:30',
        ],[
            'email.exists' => 'This email is not exist in admins table'
        ]);

        $creds = $request->only('email', 'password');
        if(Auth::guard('admin')->attempt($creds)){
            return redirect()->route('admin.home');
        }else{
            return redirect()->route('admin.login')->with('fail', 'Incorrect credentials');
        }
    }

    function home(Request $request){

        $users = User::all();

        return view('dashboard.admin.home', compact('users'));
    }

    function edit(Request $request){

        $user = User::find(base64_decode($request->id));

        return view('dashboard.admin.edit', compact('user'));
    }

    function update(Request $request){

        $request->validate([
            'name' => 'required',
        ]);

        $user = User::find(base64_decode($request->id));
        $user->name = $request->name;
        $save = $user->save();

        if($save){
            return redirect()->route('admin.home')->with('success', 'You updated user successfully');
        }else{
            return redirect()->route('admin.home')->with('fail', 'Something went wrong, failed to update');
        }
    }

    function logout(){
        Auth::guard('admin')->logout();
        return redirect('/');
    }
}
