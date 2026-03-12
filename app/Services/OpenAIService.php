<?php
namespace App\Services;

use App\Models\ConversationMessage;

class OpenAIService
{
    protected $apiKey;
    const API_URL = 'https://api.openai.com/v1/responses';
    const AI_MODEL = 'gpt-5';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Send request to OpenAI API
     * 
     * @param mixed $data
     * 
     * @return array
     */
    public function sendRequest($data): array
    {
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->apiKey,
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

    /**
     * Translate given message to target language
     * 
     * @param int $messageId
     * @param string $targetLanguage
     * 
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function translateMessage(int $messageId, string $targetLanguage) {
        $message = ConversationMessage::find($messageId)->message;

        $data = [
            'model' => self::AI_MODEL,
            'input' => (array) [
                (object) [
                    'role' => 'system',
                    'content' => "You are a language translator that translates the input message to 
                        target language specified by the developer. Only accept valid target languages. 
                        If the target language is not valid, respond with an error message.
                        If there are any inapporpriate words in the input message, replace them with 
                        **** in the translated text.",
                ],
                (object) [
                    'role' => 'developer',
                    'content' => 'Translate message from the user to ' . $targetLanguage
                ],
                (object) [
                    'role' => 'user',
                    'content' => $message
                ],
            ],
            "text" => (object) [
                "format" => (object) [
                    "type" => "json_schema",
                    "name" => "translation_response",
                    "schema" => (object) [
                        "type" => "object",
                        "properties" => (object) [
                            "translated_text" => (object) [
                                "type" => "string",
                                "description" => "The translated text in the target language"
                            ],
                            "detected_language" => (object) [
                                "type" => "string",
                                "description" => "The original language of the input text"
                            ]
                        ],
                        "additionalProperties" => false,
                        "required" => (array) ["translated_text", "detected_language"],
                    ],
                    "strict" => true,
                ]
            ]
        ];
        
        $response = $this->sendRequest($data);
        foreach ($response['output'] as $output) {
            if ($output['type'] === 'message') {
                foreach ($output['content'] as $content) {
                    if ($content['type'] === 'output_text') {
                        $jsonContent = json_decode($content['text'], true);
                        return response()->json([
                            'status' => 'success',
                            'translated_text' => $jsonContent['translated_text'],
                            'detected_language' => $jsonContent['detected_language'],
                        ]);
                    }
                }
            }
        }

        return $response;
    }
}
