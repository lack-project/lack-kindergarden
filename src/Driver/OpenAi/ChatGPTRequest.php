<?php

namespace Lack\KiKi\Driver\OpenAi;

class ChatGPTRequest
{
    private string $apiKey;
    private string $model;
    private array $messages = [];
    private int $maxTokens = 150;
    private float $temperature = 1.0;
    private bool $stream = false;
    private ?\Closure $callback = null;
    private ?string $response = null;
    private ?bool $completedNaturally = null;
    private $curlHandle = null;
    private array $headers = [];
    private array $options = [];

    public function __construct(string $apiKey, string $model = 'gpt-4')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function addMessage(string $role, string $content): void
    {
        $this->messages[] = ['role' => $role, 'content' => $content];
    }

    public function setMaxTokens(int $maxTokens): void
    {
        $this->maxTokens = $maxTokens;
    }

    public function setTemperature(float $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function enableStreaming(?callable $callback = null): void
    {
        $this->stream = true;
        $this->callback = $callback;
    }

    public function disableStreaming(): void
    {
        $this->stream = false;
        $this->callback = null;
    }

    public function prepareCurlHandle(): void
    {
        $this->curlHandle = curl_init('https://api.openai.com/v1/chat/completions');

        $payload = [
            'model' => $this->model,
            'messages' => $this->messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'stream' => $this->stream,
        ];

        $this->headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        $this->options = [
            CURLOPT_RETURNTRANSFER => !$this->stream,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ];

        if ($this->stream && $this->callback) {
            $this->options[CURLOPT_WRITEFUNCTION] = function ($ch, $data) {
                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    if (strpos($line, 'data: ') === 0) {
                        $content = substr($line, 6);
                        if (trim($content) === '[DONE]') {
                            $this->completedNaturally = true;
                            return strlen($data);
                        }
                        $decoded = json_decode($content, true);
                        if (isset($decoded['choices'][0]['delta']['content'])) {
                            ($this->callback)($decoded['choices'][0]['delta']['content']);
                        }
                    }
                }
                return strlen($data);
            };
        }

        curl_setopt_array($this->curlHandle, $this->options);
    }

    public function processCurlResult(): void
    {
        if (curl_errno($this->curlHandle)) {
            throw new \Exception('Curl Error: ' . curl_error($this->curlHandle));
        }

        $httpStatus = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($httpStatus !== 200) {
            $result = curl_multi_getcontent($this->curlHandle) ?: curl_exec($this->curlHandle);
            throw new \Exception('HTTP Error: ' . $httpStatus . ' - ' . $result);
        }

        if (!$this->stream) {
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
