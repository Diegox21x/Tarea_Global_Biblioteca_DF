<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function index()
    {
        return view('library');
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Muestra formulario de login']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->libraryData->all();
        $email = strtolower((string) $request->input('email'));

        foreach ($data['users'] as $user) {
            if (strtolower($user['email']) === $email) {
                return response()->json(['user' => $user]);
            }
        }

        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(['message' => "Mostrar sesión {$id}"]);
    }

    public function edit(string $id): JsonResponse
    {
        return response()->json(['message' => "Editar sesión {$id}"]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => "Actualizar sesión {$id}"]);
    }

    public function destroy(string $id = 'current'): JsonResponse
    {
        return response()->json(['message' => "Sesión {$id} cerrada"]);
    }
}
