<?php

namespace App\Http\Controllers;

use App\Services\CinOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CinOcrController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'cin_image' => 'required|image|max:5120',
        ]);

        return response()->json(
            app(CinOcrService::class)->extract($request->file('cin_image'))
        );
    }
}
