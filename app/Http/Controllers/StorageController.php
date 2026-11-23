<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    public function show(string $path)
    {
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $filePath = Storage::disk('public')->path($path);
        return response()->file($filePath);
    }
}
