<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private const ROLES = ['usuario', 'tecnico', 'admin'];

    public function __construct()
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        return view('users.index', [
            'active' => 'users',
            'title' => 'Usuarios',
            'subtitle' => 'Administra los roles de acceso de los usuarios registrados',
            'users' => User::orderBy('name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:'.implode(',', self::ROLES)],
        ]);

        if ($user->is(auth()->user()) && $data['role'] !== 'admin') {
            abort(403);
        }

        $user->role = $data['role'];
        $user->save();

        return redirect()->route('users.index')
            ->with('status', 'Rol del usuario actualizado correctamente.');
    }
}
