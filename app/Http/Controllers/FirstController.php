<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FirstController extends Controller
{
    public function homepage(){
        $ourName = "Brad";
        $animals = ['cat','dog','fish'];
        return view("homepage", ['allAnimals'=>$animals,'name'=> $ourName, 'cat' => "MeowMeowMeow"]);
    }

    public function aboutPage(){
        return view('single-post');
    }
}
