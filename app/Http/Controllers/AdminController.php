<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function index(): JsonResponse
    {
        $data = $this->libraryData->all();

        usort($data['books'], fn ($a, $b) => $b['timesLoaned'] <=> $a['timesLoaned']);
        usort($data['users'], fn ($a, $b) => $b['lateReturns'] <=> $a['lateReturns']);

        $durations = [];
        foreach ($data['loans'] as $loan) {
            if ($loan['returned_at']) {
                $durations[] = (strtotime($loan['returned_at']) - strtotime($loan['borrowed_at'])) / 86400;
            }
        }

        return response()->json([
            'topBooks' => array_slice($data['books'], 0, 5),
            'usersWithDelays' => array_slice($data['users'], 0, 5),
            'avgReturnDays' => empty($durations) ? 0 : round(array_sum($durations) / count($durations), 2),
            'settings' => $data['settings'],
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Formulario de ajustes admin']);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->update($request, 'settings');
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(['message' => "Detalle admin {$id}"]);
    }

    public function edit(string $id): JsonResponse
    {
        return response()->json(['message' => "Editar admin {$id}"]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->libraryData->all();
        $data['settings']['maxBooks'] = (int) $request->input('maxBooks', $data['settings']['maxBooks']);
        $data['settings']['loanDays'] = (int) $request->input('loanDays', $data['settings']['loanDays']);
        $this->libraryData->write($data);

        return response()->json($data['settings']);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json(['message' => "No aplica borrar {$id} en panel admin"]);
    }
}
