<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Auth;

class OpenAiContoller extends Controller
{
    public function translateMessage(Request $request, OpenAIService $openAIService)
    {
        $request->validate([
            'message_id' => 'required|exists:conversation_messages,id',
            'target_language' => 'required|string',
        ]);

        $message = ConversationMessage::find($request->input('message_id'));

        if (!in_array(Auth::user()->id, $message->conversation->members->pluck('user_id')->toArray())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission Denied',
            ], 403);
        }

        $data = [
            'model' => 'gpt-5',
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
                    'content' => 'Translate message from the user to ' . $request->input('target_language')
                ],
                (object) [
                    'role' => 'user',
                    'content' => $message->message
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
        
        $response = $openAIService->sendRequest($data);
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
        return response()->json([
            'status' => 'error',
            'message' => 'No valid message output found.',
        ], 500);
        return response()->json($response['output']);
    }

}
