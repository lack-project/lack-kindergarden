<?php

namespace Lack\Kindergarden\Driver\OpenAi;

class OpenAiClient
{

    const OPENAI_DEFAULT_MODEL = "gpt-4o";
    const OPENAI_DEFAULT_REASONING_MODEL = "gpt-o1";


    private array $requests = [];
    private $multiHandle;

    private string $apiKey;
    private string $defaultModel;

    public function __construct(string $apiKey = null, string $defaultModel = self::OPENAI_DEFAULT_MODEL)
    {
        $this->apiKey = $apiKey;
        $this->defaultModel = $defaultModel;
        $this->multiHandle = curl_multi_init();
    }

    public function createRequest(string $model = null): OpenAiRequest
    {
        return new OpenAiRequest($this->apiKey, $model ?? $this->defaultModel);
    }

    public function addRequest(OpenAiRequest $request): void
    {
        $request->prepareCurlHandle();
        $ch = $request->getCurlHandle();
        curl_multi_add_handle($this->multiHandle, $ch);
        $this->requests[(int)$ch] = $request;
    }

    public function executeAll(): void
    {
        $running = null;

        do {
            $status = curl_multi_exec($this->multiHandle, $running);
            if ($status > CURLM_OK) {
                throw new \Exception('Curl Multi Error: ' . curl_multi_strerror($status));
            }

            // Handle completed requests
            while ($info = curl_multi_info_read($this->multiHandle)) {
                $ch = $info['handle'];
                $request = $this->requests[(int)$ch];
                $request->processCurlResult();
                curl_multi_remove_handle($this->multiHandle, $ch);
                unset($this->requests[(int)$ch]);
            }

            // Allow adding new requests while running
            usleep(1000); // Sleep for 1ms to prevent CPU spinning
        } while ($running > 0 || !empty($this->requests));

        curl_multi_close($this->multiHandle);
    }
}
