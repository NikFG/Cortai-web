<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller {

    public function index() {
        $user = User::all();

        return response()->json($user);
    }


    public function store(Request $request) {

    }


    public function show($id) {

    }

    public function update(Request $request, $id) {

    }


    public function destroy($id) {

    }
}
