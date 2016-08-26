<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Http\Requests;

class HomeController extends Controller
{
    public function getHome(){
        $user = Auth::user();
        if ($user){
            return view('dashboard', compact('user'));
        }else{
            return view('welcome');
        }
    }
    
    public function getAbout(){
        return view('about', ['user' => Auth::user()]);
    }
    
    public function getContact(){
        return view('contact', ['user' => Auth::user()]);
    }
}
