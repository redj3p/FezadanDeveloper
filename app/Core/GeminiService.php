<?php

class GeminiService {
    
    /**
     * Generates SEO title, description, and keywords for a given article content and language using Gemini 2.5 Flash.
     * 
     * @param string $content The article content (HTML or plain text).
     * @param string $lang The target language ('TR' or 'EN').
     * @return array|null Returns ['title' => ..., 'description' => ..., 'keywords' => ...] or null on failure.
     */
    public static function generateSeo(string $content, string $lang = 'TR'): ?array {
        $apiKey = env_value('GEMINI_API_KEY');
        if (empty($apiKey)) {
            error_log('GeminiService Error: GEMINI_API_KEY is not defined in the environment.');
            return null;
        }

        // Clean up content to reduce prompt size
        $plainText = strip_tags($content);
        $plainText = mb_substr($plainText, 0, 10000); // limit to 10k characters for efficiency

        $prompt = "You are an SEO expert. Analyze the following article content and generate:
1. A concise, professional, and engaging SEO meta title (maximum 60 characters) written in the target language ($lang).
2. A concise, professional, and engaging meta description (maximum 160 characters) written in the target language ($lang).
3. A list of 5 to 10 highly relevant SEO keywords as a comma-separated string in the target language ($lang).

Article content:
$plainText";

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'title' => [
                            'type' => 'STRING',
                            'description' => "SEO title in $lang (max 60 characters)."
                        ],
                        'description' => [
                            'type' => 'STRING',
                            'description' => "SEO description in $lang (max 160 characters)."
                        ],
                        'keywords' => [
                            'type' => 'STRING',
                            'description' => "Comma-separated list of SEO keywords in $lang."
                        ]
                    ],
                    'required' => ['title', 'description', 'keywords']
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('GeminiService cURL Error: ' . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log('GeminiService API Error (HTTP ' . $httpCode . '): ' . $response);
            return null;
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('GeminiService JSON Parse Error: ' . json_last_error_msg());
            return null;
        }

        // Parse Gemini response structure
        $candidateText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (empty($candidateText)) {
            error_log('GeminiService Error: Invalid response structure from API.');
            return null;
        }

        $seoData = json_decode($candidateText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('GeminiService Candidate Text JSON Parse Error: ' . json_last_error_msg());
            return null;
        }

        return [
            'title' => trim($seoData['title'] ?? ''),
            'description' => trim($seoData['description'] ?? ''),
            'keywords' => trim($seoData['keywords'] ?? '')
        ];
    }
}
