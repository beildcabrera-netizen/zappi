<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrador');
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->orderBy('nombre')->paginate(20)->withQueryString();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|string|in:administrador,vendedor,cajero',
            'puede_cobrar' => 'boolean',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'rol' => $validated['rol'],
            'puede_cobrar' => $validated['puede_cobrar'] ?? false,
            'telefono' => $validated['telefono'] ?? null,
            'activo' => true,
        ]);

        $user->assignRole($validated['rol']);

        return Redirect::route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $usuario->load('roles');

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8|confirmed',
            'rol' => 'required|string|in:administrador,vendedor,cajero',
            'puede_cobrar' => 'boolean',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ]);

        $data = $validated;

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $usuario->update($data);
        $usuario->syncRoles([$validated['rol']]);

        return Redirect::route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $usuario->delete();

        return Redirect::route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
