<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->libraryData->all()['books']);
    }

    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Formulario para crear libro']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->libraryData->all();
        $books = $data['books'];

        $new = [
            'id' => empty($books) ? 1 : max(array_column($books, 'id')) + 1,
            'title' => (string) $request->input('title', 'Sin título'),
            'author' => (string) $request->input('author', 'Desconocido'),
            'available' => true,
            'timesLoaned' => 0,
        ];

        $data['books'][] = $new;
        $this->libraryData->write($data);

        return response()->json($new, 201);
    }

    public function show(string $id): JsonResponse
    {
        foreach ($this->libraryData->all()['books'] as $book) {
            if ((string) $book['id'] === $id) {
                return response()->json($book);
            }
        }

        return response()->json(['message' => 'Libro no encontrado'], 404);
    }

    public function edit(string $id): JsonResponse
    {
        return response()->json(['message' => "Formulario edición libro {$id}"]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->libraryData->all();

        foreach ($data['books'] as &$book) {
            if ((string) $book['id'] === $id) {
                $book['title'] = (string) $request->input('title', $book['title']);
                $book['author'] = (string) $request->input('author', $book['author']);
                $book['available'] = (bool) $request->input('available', $book['available']);
                $this->libraryData->write($data);

                return response()->json($book);
            }
        }

        return response()->json(['message' => 'Libro no encontrado'], 404);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = $this->libraryData->all();
        $books = array_values(array_filter($data['books'], fn (array $book) => (string) $book['id'] !== $id));
        $data['books'] = $books;
        $this->libraryData->write($data);

        return response()->json(['message' => 'Libro eliminado']);
    }
}
