<?php

namespace App\Services;

use App\Enums\MoroccanBank;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChequeOcrService
{
    private const BANK_VALUES = [
        'ATTIJARI', 'CIH', 'BP', 'BMCE', 'CREDIT DU MAROC', 'BARID BANK',
        'CREDIT AGRICOLE', 'SOCIETE GENERALE', 'BMCI', 'CFG BANK',
        'AL AKHDAR BANK', 'BANK OF AFRICA', 'UMNIA BANK', 'BTI BANK', 'CASH PLUS',
    ];

    public function extract(UploadedFile $image): array
    {
        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType();
        $bankList = implode(', ', self::BANK_VALUES);

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
                                'text' => "Extract the following fields from this Moroccan bank cheque image. Return ONLY a JSON object with these keys: \"nature\" (cheque number), \"amount\" (numeric amount as a number, not string), \"bank\" (the bank name — MUST be one of these exact values: {$bankList}. Match the bank logo/name on the cheque to the closest value from this list), \"echeance\" (date on the cheque, format: DD/MM/YYYY), \"account_holder\" (name of the account holder/drawer). If a field cannot be read, set it to null.",
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
            $data = json_decode($content, true);

            // Normalize bank to a valid MoroccanBank enum value
            if (! empty($data['bank'])) {
                $data['bank'] = $this->matchBank($data['bank']);
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Cheque OCR error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Failed to process cheque image. Please enter details manually.',
            ];
        }
    }

    private function matchBank(string $rawBank): ?string
    {
        $normalized = Str::upper(trim($rawBank));

        // Exact match first
        if (MoroccanBank::tryFrom($normalized)) {
            return $normalized;
        }

        // Fuzzy match: find the closest enum value
        $bestMatch = null;
        $bestScore = 0;

        foreach (self::BANK_VALUES as $bankValue) {
            similar_text($normalized, $bankValue, $percent);
            if ($percent > $bestScore && $percent >= 60) {
                $bestScore = $percent;
                $bestMatch = $bankValue;
            }

            // Also check if either contains the other
            if (Str::contains($normalized, $bankValue) || Str::contains($bankValue, $normalized)) {
                return $bankValue;
            }
        }

        return $bestMatch;
    }
}
