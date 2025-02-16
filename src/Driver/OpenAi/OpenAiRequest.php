<?php

namespace Lack\Kindergarden\Driver\OpenAi;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\ChatRequestDriver;
use Lack\Kindergarden\ChatSerializer;

class OpenAiRequest implements ChatRequestDriver
{
    private string $apiKey;
    private ?\Closure $callback = null;
    private ?string $response = null;
    private ?bool $completedNaturally = null;
    private \CurlHandle|null $curlHandle = null;
    private array $headers = [];
    private array $options = [];


    private ChatSerializer $chatSerializer;

    private Chat $chat;

    public function __construct(string $apiKey, string $model)
    {
        $this->apiKey = $apiKey;
        $this->chatSerializer = new OpenAiChatSerializer();
        $this->chatSerializer->model = $model;
    }

    public function setChat(Chat $chat): ChatRequestDriver
    {
        $this->chat = $chat;
        return $this;
    }

    public function getChat() : Chat
    {
        return $this->chat;
    }


    public function enableStreaming(?callable $callback = null): void
    {
        $this->chatSerializer->stream = true;
        $this->callback = $callback;
    }

    public function disableStreaming(): void
    {
        $this->chatSerializer->stream = false;
        $this->callback = null;
    }

    /**
     * This method is used interanally. You sohuld not call it directly.
     *
     * Call execute() method instead. or use the Client to schedule multiple requests.
     *
     * @return void
     * @internal
     */
    public function prepareCurlHandle(): void
    {
        $this->curlHandle = curl_init('https://api.openai.com/v1/chat/completions');

        $this->chatSerializer->stream = $this->callback !== null;
        $payload = $this->chatSerializer->serialize($this->chat, $this);

        $this->headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . trim($this->apiKey),
        ];

        $payload = json_encode($payload);
        if ( ! $payload) {
            throw new \Exception('Failed to json_encode chat payload');
        }

        $this->options = [
            CURLOPT_RETURNTRANSFER => !$this->chatSerializer->stream,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload
        ];

        if ($this->chatSerializer->stream && $this->callback) {
            $this->options[CURLOPT_WRITEFUNCTION] = function ($ch, $data) {
                $this->response .= $data;
                $lines = explode("\n", $data);
                //print_r ($lines);
                foreach ($lines as $line) {
                    if (strpos($line, 'data: ') === 0) {
                        $content = substr($line, 6);
                        /*
                        if (trim($content) === '[DONE]') {
                            $this->completedNaturally = true;
                            return strlen($data);
                        }
                        */
                        $decoded = json_decode($content, true);
                        if (isset($decoded['choices'][0]['delta']['content'])) {
                            ($this->callback)($decoded['choices'][0]['delta']['content']);
                        }
                        if (isset($decoded['choices'][0]['finish_reason'])) {
                            $this->completedNaturally = $decoded['choices'][0]['finish_reason'] === 'stop';
                        }
                    }
                }
                return strlen($data);
            };
        }

        curl_setopt_array($this->curlHandle, $this->options);
    }

    /**
     * This method is used interanally. You sohuld not call it directly.
     *
     * Call execute() method instead. or use the Client to schedule multiple requests.
     *
     * @return void
     * @internal
     * @throws \Exception
     */
    public function processCurlResult(): void
    {
        if (curl_errno($this->curlHandle)) {
            throw new \Exception('Curl Error: ' . curl_error($this->curlHandle));
        }

        $httpStatus = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($httpStatus !== 200) {
            $result = curl_multi_getcontent($this->curlHandle) ?: curl_exec($this->curlHandle);

            if ($this->chatSerializer->stream) {
                $result = $this->response;
            }

            throw new \Exception('HTTP Error: ' . $httpStatus . ' - ' . $result);
        }

        if ( ! $this->chatSerializer->stream) {
            $result = curl_multi_getcontent($this->curlHandle) ?: curl_exec($this->curlHandle);
            $decoded = json_decode($result, true);

            if (isset($decoded['choices'][0]['finish_reason'])) {
                $this->completedNaturally = $decoded['choices'][0]['finish_reason'] === 'stop';
            }
            $this->response = $decoded['choices'][0]['message']['content'] ?? null;
        }

        curl_close($this->curlHandle);
    }

    public function execute(): void
    {
        $this->prepareCurlHandle();
        curl_exec($this->curlHandle);
        $this->processCurlResult();
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function completedNaturally(): ?bool
    {
        return $this->completedNaturally;
    }

    public function getCurlHandle()
    {
        return $this->curlHandle;
    }
}
