<?php namespace App\Http\Controllers;


use App\User;
use App\Roles;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;

class UsersController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
		$search = '';
        if($request->has('search')) {
            $search = \Purifier::clean($request->get('search'));
            // Замена спецсимволов:
            $search_clean = str_replace(['%', "'", '"', '_', "\\"], ['\%', "\'", '\"', '\_', "\\\\"], $search);
            $users = User::where('name', 'LIKE', '%'.$search_clean.'%')
                ->orWhere('email', 'LIKE', '%'.$search_clean.'%')->paginate(20);
            $users->setPath(url('/').'/admin/users?search='.$search);
        } else {
            $users = User::paginate(20);
        }
        $page = 'users.index';
        View::share(['page' => $page]);
		return view('admin.users.users_index', ['search' => $search, 'users' => $users]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
        $roles = Roles::all();
        $page = 'users.create';
        View::share(['page' => $page]);
		return view('admin.users.user_create', ['roles' => $roles]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$data = $request->only('password', 'email', 'name', 'role');
		$data['password_confirmation'] = $data['password'];
		$successOrFail = User::createUser($data);
		if($successOrFail == true) {
			return redirect()->back()->with('success', 'Пользователь успешно зарегистрирован!');
		} else {
			return redirect()->back()->withErrors($successOrFail)->withInput($request->except('_token', 'password'));
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$user = User::find($id);
		$roles = Roles::all();
		return view('admin.users.user_edit', ['user' => $user, 'roles' => $roles]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$data = Input::only('name', 'password', 'email', 'role');
		$data['password_confirmation'] = $data['password'];
		$successOrFail = User::updateUser($data, $id);
		if($successOrFail == true) {
			return redirect()->back()->with('success', 'Изменения успешно сохранены!');
		} else {
			return redirect()->back()->withErrors($successOrFail)->withInput($request->except('_token', 'password'));
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$users = User::find($id);
		$users->delete();
		// redirect
		Session::flash('message', 'Successfully deleted the user!');
		return Redirect::to('users');
	}

}
