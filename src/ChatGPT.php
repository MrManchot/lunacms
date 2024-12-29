<?php

declare(strict_types=1);

namespace LunaCMS;

use Exception;

class ChatGPTClient
{
    private string $apiUrl;
    private string $apiKey;
    private string $defaultModel;
    private float $defaultTemperature;

    public function __construct()
    {
        $openAiConfig = Config::getConfigVar('openai') ?? [];

        $this->apiUrl = $openAiConfig['api_url'] ?? 'https://api.openai.com/v1/';
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->defaultModel = $openAiConfig['default_model'] ?? 'gpt-4o-mini';
        $this->defaultTemperature = $openAiConfig['default_temperature'] ?? 0.7;

        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key not set. Provide it in .env as OPENAI_API_KEY.');
        }
    }

    public function chatCompletion(
        string $message,
        ?string $model = null,
        ?float $temperature = null
    ): ?array {
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
            $data = $response['choices'][0]['message']['content'];
            $data = str_replace(['```json', '```html', '```'], '', $data);
            return json_decode(trim($data), true);
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
        curl_close($ch);

        if ($httpCode === 200 && $response !== false) {
            return json_decode($response, true);
        }

        return null;
    }
}
