<?php

namespace App\Http\Controllers;

use App\Support\LibraryData;
use Illuminate\Http\JsonResponse;

class LibraryController extends Controller
{
    public function __construct(private readonly LibraryData $libraryData)
    {
    }

    public function bootstrap(): JsonResponse
    {
        return response()->json($this->libraryData->all());
    }
}
