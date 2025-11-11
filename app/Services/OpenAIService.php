<?php
namespace App\Services;

class OpenAIService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.openai.com/v1/responses';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function sendRequest($data)
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer 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',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Request Error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
