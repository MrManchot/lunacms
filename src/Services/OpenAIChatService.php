<?php

namespace LunaCMS\Services;

use Exception;

class OpenAIChatService
{
    private string $apiUrl;
    private string $apiKey;
    private string $defaultModel;
    private float $defaultTemperature;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['api_url'] ?? 'https://api.openai.com/v1/';
        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';

        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key not set. Provide it in .env as OPENAI_API_KEY.');
        }

        $this->defaultModel = $config['default_model'] ?? 'gpt-4o-mini';
        $this->defaultTemperature = $config['default_temperature'] ?? 0.7;
    }

    public function chatCompletion(string $message, ?string $model = null, ?float $temperature = null): ?string
    {
        $model = $model ?? $this->defaultModel;
        $temperature = $temperature ?? $this->defaultTemperature;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => $temperature
        ];

        $response = $this->sendRequest('chat/completions', $payload);

        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }

        return null;
    }

    private function sendRequest(string $endpoint, array $payload): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtrim($this->apiUrl, '/') . '/' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('cURL Error: ' . $curlError);
            return null;
        }

        if ($httpCode !== 200) {
            error_log('OpenAI API Error: HTTP ' . $httpCode);
            return null;
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return null;
        }

        return $decodedResponse;
    }
}
