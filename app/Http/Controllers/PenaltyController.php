<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->libraryData->all()['penalties']);
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Formulario para asignar penalización']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->libraryData->all();
        $userId = (int) $request->input('user_id');
        $days = (int) $request->input('days', 7);

        $penalty = [
            'id' => empty($data['penalties']) ? 1 : max(array_column($data['penalties'], 'id')) + 1,
            'user_id' => $userId,
            'until' => date('Y-m-d', strtotime("+{$days} days")),
            'reason' => (string) $request->input('reason', 'Retraso en devolución'),
        ];

        foreach ($data['users'] as &$user) {
            if ($user['id'] === $userId) {
                $user['penaltyUntil'] = $penalty['until'];
            }
        }

        $data['penalties'][] = $penalty;
        $this->libraryData->write($data);

        return response()->json($penalty, 201);
    }

    public function show(string $id): JsonResponse
    {
        foreach ($this->libraryData->all()['penalties'] as $penalty) {
            if ((string) $penalty['id'] === $id) {
                return response()->json($penalty);
            }
        }

        return response()->json(['message' => 'Penalización no encontrada'], 404);
    }

    public function edit(string $id): JsonResponse
    {
        return response()->json(['message' => "Formulario edición penalización {$id}"]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->libraryData->all();

        foreach ($data['penalties'] as &$penalty) {
            if ((string) $penalty['id'] === $id) {
                $penalty['reason'] = (string) $request->input('reason', $penalty['reason']);
                $this->libraryData->write($data);

                return response()->json($penalty);
            }
        }

        return response()->json(['message' => 'Penalización no encontrada'], 404);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = $this->libraryData->all();

        foreach ($data['penalties'] as $penalty) {
            if ((string) $penalty['id'] === $id) {
                foreach ($data['users'] as &$user) {
                    if ($user['id'] === $penalty['user_id']) {
                        $user['penaltyUntil'] = null;
                    }
                }
            }
        }

        $data['penalties'] = array_values(array_filter($data['penalties'], fn (array $penalty) => (string) $penalty['id'] !== $id));
        $this->libraryData->write($data);

        return response()->json(['message' => 'Penalización eliminada']);
    }
}
