<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinOcrService
{
    public function extract(UploadedFile $image): array
    {
        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.mistral.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => 'pixtral-large-latest',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Extract the following fields from this Moroccan CIN (Carte d'Identité Nationale) card image. Return ONLY a JSON object with these keys: \"name\" (full name in Latin characters), \"cin\" (CIN number), \"date_of_birth\" (format: YYYY-MM-DD), \"address\" (full address). If a field cannot be read, set it to null.",
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64}",
                                ],
                            ],
                        ],
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'max_tokens' => 500,
            ]);

            $content = $response->json('choices.0.message.content');

            return [
                'success' => true,
                'data' => json_decode($content, true),
            ];
        } catch (\Exception $e) {
            Log::error('CIN OCR error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Failed to process CIN image. Please enter details manually.',
            ];
        }
    }
}
