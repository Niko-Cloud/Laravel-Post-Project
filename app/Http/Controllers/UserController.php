<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function showCorrectHomepage(){
        if (auth()->check()){
            return view('homepage-feed');
        }else{
            return view('homepage');
        }
    }

    public function register(Request $request){
        $incomingFields = $request->validate([
            'username' => ['min:4','max:20','required',Rule::unique('users','username')],
            'email' => ['email','required',Rule::unique('users','email')],
            'password' => ['required','min:8','confirmed']
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);

        auth()->login($user);

        return redirect('/')->with('success', 'Account Created');
    }

    public function login(Request $request){
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if(auth()->attempt(['username'=>$incomingFields['loginusername'], 'password'=>$incomingFields['loginpassword']])){
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully login');
        }else{
            return redirect('/')->with('failed', 'Wrong authentication');
        }
    }

    public function logout(){
        auth()->logout();
        return redirect('/')->with('success', 'You are now logout');
    }
}
