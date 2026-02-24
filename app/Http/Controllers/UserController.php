<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->libraryData->all()['users']);
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Formulario para crear usuario']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->libraryData->all();
        $users = $data['users'];

        $new = [
            'id' => empty($users) ? 1 : max(array_column($users, 'id')) + 1,
            'name' => (string) $request->input('name', 'Nuevo usuario'),
            'email' => (string) $request->input('email', 'usuario@biblioteca.local'),
            'role' => (string) $request->input('role', 'user'),
            'penaltyUntil' => null,
            'lateReturns' => 0,
        ];

        $data['users'][] = $new;
        $this->libraryData->write($data);

        return response()->json($new, 201);
    }

    public function show(string $id): JsonResponse
    {
        foreach ($this->libraryData->all()['users'] as $user) {
            if ((string) $user['id'] === $id) {
                return response()->json($user);
            }
        }

        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    public function edit(string $id): JsonResponse
    {
        return response()->json(['message' => "Formulario edición usuario {$id}"]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->libraryData->all();

        foreach ($data['users'] as &$user) {
            if ((string) $user['id'] === $id) {
                $user['name'] = (string) $request->input('name', $user['name']);
                $user['email'] = (string) $request->input('email', $user['email']);
                $user['role'] = (string) $request->input('role', $user['role']);
                $this->libraryData->write($data);

                return response()->json($user);
            }
        }

        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = $this->libraryData->all();
        $data['users'] = array_values(array_filter($data['users'], fn (array $user) => (string) $user['id'] !== $id));
        $this->libraryData->write($data);

        return response()->json(['message' => 'Usuario eliminado']);
    }
}
