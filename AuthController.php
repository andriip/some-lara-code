<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;

class AuthController extends Controller
{

    public function registerPage()
    {
        return view('admin.users.user_create');
    }

    public function loginPage(Request $request)
    {
        if(Auth::check()) {
            return redirect('admin');
        }
        if($request->isMethod('post')) {
            $data = $request->only('email', 'password', 'remember');
            if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']],
                $data['remember'] == null ? false : true)) {
                return redirect('admin');
            } else {
                return redirect()->back()->with('login_error', 'Неверный логин или пароль')
                    ->withInput($request->only('email'));
            }
        } else {
            return view('admin.users.user_login');
        }
    }

    public function logOut()
    {
        Auth::logout();
        return redirect('/admin/login');
    }

    public function createUser(Request $request)
    {
        $data = $request->only('password', 'email', 'name', 'password_confirmation');
        $successOrFail = User::createUser($data);
        if($successOrFail == true) {
            return redirect()->back()->with('success_register', 'Пользователь успешно зарегистрирован!');
        } else {
            return redirect()->back()->withErrors($successOrFail)->withInput($request->except('_token', 'password',
                'password_confirmation'));
        }
    }

}
