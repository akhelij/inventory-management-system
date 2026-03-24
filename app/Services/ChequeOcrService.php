<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChequeOcrService
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
                                'text' => 'Extract the following fields from this Moroccan bank cheque image. Return ONLY a JSON object with these keys: "nature" (cheque number), "amount" (numeric amount as a number, not string), "bank" (bank name), "echeance" (date on the cheque, format: DD/MM/YYYY), "account_holder" (name of the account holder/drawer). If a field cannot be read, set it to null.',
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
            Log::error('Cheque OCR error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Failed to process cheque image. Please enter details manually.',
            ];
        }
    }
}
