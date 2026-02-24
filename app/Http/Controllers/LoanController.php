<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->libraryData->all()['loans']);
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Formulario para solicitar préstamo']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->libraryData->all();
        $userId = (int) $request->input('user_id');
        $bookId = (int) $request->input('book_id');

        $activeLoans = array_filter($data['loans'], fn (array $loan) => $loan['user_id'] === $userId && $loan['returned_at'] === null);
        $settings = $data['settings'];

        $user = null;
        foreach ($data['users'] as $item) {
            if ($item['id'] === $userId) {
                $user = $item;
                break;
            }
        }

        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($user['penaltyUntil'] !== null && strtotime($user['penaltyUntil']) > time()) {
            return response()->json(['message' => 'Usuario penalizado, no puede solicitar préstamos'], 422);
        }

        if (count($activeLoans) >= (int) $settings['maxBooks']) {
            return response()->json(['message' => 'Límite de préstamos alcanzado'], 422);
        }

        foreach ($data['books'] as &$book) {
            if ($book['id'] === $bookId && $book['available']) {
                $loan = [
                    'id' => empty($data['loans']) ? 1 : max(array_column($data['loans'], 'id')) + 1,
                    'user_id' => $userId,
                    'book_id' => $bookId,
                    'borrowed_at' => date('Y-m-d'),
                    'due_date' => date('Y-m-d', strtotime('+' . (int) $settings['loanDays'] . ' days')),
                    'returned_at' => null,
                ];

                $book['available'] = false;
                $book['timesLoaned']++;
                $data['loans'][] = $loan;
                $this->libraryData->write($data);

                return response()->json($loan, 201);
            }
        }

        return response()->json(['message' => 'Libro no disponible'], 422);
    }

    public function show(string $id): JsonResponse
    {
        foreach ($this->libraryData->all()['loans'] as $loan) {
            if ((string) $loan['id'] === $id) {
                return response()->json($loan);
            }
        }

        return response()->json(['message' => 'Préstamo no encontrado'], 404);
    }

    public function edit(string $id): JsonResponse
    {
        return response()->json(['message' => "Formulario edición préstamo {$id}"]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->libraryData->all();

        foreach ($data['loans'] as &$loan) {
            if ((string) $loan['id'] !== $id) {
                continue;
            }

            if ($loan['returned_at'] === null && $request->boolean('return')) {
                $loan['returned_at'] = date('Y-m-d');

                foreach ($data['books'] as &$book) {
                    if ($book['id'] === $loan['book_id']) {
                        $book['available'] = true;
                        break;
                    }
                }
            }

            $this->libraryData->write($data);

            return response()->json($loan);
        }

        return response()->json(['message' => 'Préstamo no encontrado'], 404);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = $this->libraryData->all();
        $data['loans'] = array_values(array_filter($data['loans'], fn (array $loan) => (string) $loan['id'] !== $id));
        $this->libraryData->write($data);

        return response()->json(['message' => 'Préstamo eliminado']);
    }
}
