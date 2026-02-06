<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('rol')
            ->rol($request->query('rol'))
            ->name($request->query('name'));
        if($request->has('all')){
            return UserResource::collection($query->get());
        }
        return UserResource::collection($query->paginate(10));
    }
}
