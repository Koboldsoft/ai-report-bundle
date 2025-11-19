<?php
namespace Koboldsoft\AiReportBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiChatService
{

    private HttpClientInterface $client;

    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $openAiApiKey)
    {
        $this->client = $client;
        $this->apiKey = $openAiApiKey;
    }

    /**
     * Send a message to GPT-4 and return the assistant’s reply.
     */
    public function chat(array $messages, string $model = 'gpt-4.1'): ?string
    {
        $response = $this->client->request('POST', 'https://api.openai.com/v1/responses', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'model' => $model,
                // The new endpoint expects 'input', not 'messages'
                'input' => $messages
            ]
        ]);

        $data = $response->toArray(false);

        // The output format is different from the chat/completions API
        // For simple text output:
        if (isset($data['output'][0]['content'][0]['text'])) {
            return trim($data['output'][0]['content'][0]['text']);
        }

        // In case of error, return null
        return null;
    }

    public function chatCurl($message)
    {
        $url = "https://api.openai.com/v1/responses";

        $data = [
            "model" => "gpt-4.1", // or "gpt-4.1-turbo" if GPT-5 isn’t available yet
            "input" => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $this->apiKey"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            
            $text = $data['output'][0]['content'][0]['text'] ?? $data['response']['output'][0]['content'][0]['text'] ?? null;
            
            
            return $text;
        }
        curl_close($ch);
    }
}

