<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    public function storeAvatar(Request $request){
        $request->validate([
            'avatar' => 'required|image|max:3000'
        ]);

        $user = auth()->user();

        $filename = $user->id.'-'.uniqid().'.jpg';

        $img = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/'.$filename, $img);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if($oldAvatar != "/fallback-avatar.jpg"){
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        return back()->with('succes', 'Avatar changed successfully');
    }

    public function showAvatarForm(){
        return view('avatar-form');
    }

    private function getSharedData($user){
        $currentlyFollowing = 0;

        if(auth()->check()){
            $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id],['followeduser','=',$user->id]])->count();
        }

        View::share('sharedData',['currentlyFollowing' =>$currentlyFollowing,
            'avatar'=> $user->avatar,
            'username'=>$user->username,
            'postCount'=>$user->posts()->count(),
            'followerCount'=>$user->followers()->count(),
            'followingCount'=>$user->followingTheseUsers()->count()]);
    }

    public function profile(User $user){

        $this->getSharedData($user);

        return view('profile-posts',
        ['posts'=>$user->posts()->latest()->get()]);
    }

    public function profileFollowers(User $user){

        $this->getSharedData($user);

        return view('profile-followers',
            ['followers'=>$user->followers()->latest()->get()]);
    }

    public function profileFollowing(User $user){

        $this->getSharedData($user);

        return view('profile-following',
            ['following'=>$user->followingTheseUsers()->latest()->get()]);
    }

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
