<?php

namespace Koboldsoft\AiReportBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiChatService
{
    private HttpClientInterface $client;
    private string $apiKey;

    private string $promptOver25 = '';
    private string $promptUnder25 = '';

    public function __construct(HttpClientInterface $client, string $openAiApiKey)
    {
        $this->client = $client;
        $this->apiKey = $openAiApiKey;

        // Basis-Pfad relativ zur Service-Klasse:
        // .../src/Service  ->  .../src/Resources/prompts
        $baseDir = __DIR__ . '/../Resources/prompts';

        $over25File  = $baseDir . '/over25.txt';
        $under25File = $baseDir . '/under25.txt';

        if (is_readable($over25File)) {
            $this->promptOver25 = trim((string) file_get_contents($over25File));
        }

        if (is_readable($under25File)) {
            $this->promptUnder25 = trim((string) file_get_contents($under25File));
        }
    }

    /**
     * Zugriff auf den Over-25-Prompt (aus over25.txt)
     */
    public function getPromptOver25(): string
    {
        return $this->promptOver25;
    }

    /**
     * Zugriff auf den Under-25-Prompt (aus under25.txt)
     */
    public function getPromptUnder25(): string
    {
        return $this->promptUnder25;
    }

    /**
     * Allgemeiner Chat-Aufruf: du gibst einfach dein "input"-Array rein.
     * Beispiel:
     *   $service->chat([$service->getPromptOver25(), $userText]);
     */
    public function chat(array $messages, string $model = 'gpt-4.1'): ?string
    {
        $response = $this->client->request('POST', 'https://api.openai.com/v1/responses', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'input' => $messages,
            ],
        ]);

        $data = $response->toArray(false);

        if (isset($data['output'][0]['content'][0]['text'])) {
            return trim($data['output'][0]['content'][0]['text']);
        }

        return null;
    }

    /**
     * cURL-Variante – falls du sie weiterhin brauchst.
     * Auch hier kannst du z.B. $this->getPromptOver25() voranstellen.
     */
    public function chatCurl(string $message): ?string
    {
        $url = 'https://api.openai.com/v1/responses';

        $payload = [
            'model' => 'gpt-5.6-luna',
            'input' => $message,
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "Authorization: Bearer {$this->apiKey}",
            ],
            CURLOPT_POSTFIELDS => json_encode(
                $payload,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
            ),
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException(
                'OpenAI-cURL-Fehler: ' . $error
            );
        }

        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \RuntimeException(
                'Ungültige OpenAI-Antwort: ' . $response
            );
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            $errorMessage = $data['error']['message']
                ?? 'Unbekannter API-Fehler';

            $errorCode = $data['error']['code']
                ?? $data['error']['type']
                ?? 'unknown';

            throw new \RuntimeException(sprintf(
                'OpenAI API HTTP %d [%s]: %s',
                $httpStatus,
                $errorCode,
                $errorMessage
            ));
        }

        foreach ($data['output'] ?? [] as $output) {
            foreach ($output['content'] ?? [] as $content) {
                if (
                    ($content['type'] ?? null) === 'output_text'
                    && isset($content['text'])
                ) {
                    return $content['text'];
                }
            }
        }

        throw new \RuntimeException(
            'Die OpenAI-Antwort enthält keinen Ausgabetext: '
            . json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }
}
