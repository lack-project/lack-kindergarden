<?php

namespace Lack\Kindergarden\CogWerk;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessage;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Cog;
use Lack\Kindergarden\CogToChatSerializer\JsonCogToChatSerializer;
use Lack\Kindergarden\Driver\OpenAi\OpenAiClient;
use Lack\Kindergarden\Kindergarden;


class CogWerk
{

    /**
     * @var Cog[]
     */
    private array $cogs = [];

    private OpenAiClient $client;

    public function __construct(protected readonly CogWerkFlavorEnum $flavor = CogWerkFlavorEnum::DEFAULT)
    {
        $this->client = Kindergarden::defaults()->getClient();
    }



    public function getClient(): OpenAiClient
    {
        return $this->client;
    }

    public function addCredentials($provider, $accessKey): CogWerk
    {
        $this->providerCredentials[$provider] = $accessKey;
        return $this;
    }


    public function addCog(Cog $cog): CogWerk
    {
        $this->cogs[] = $cog;
        return $this;
    }

    public function setPipeline(array $cogs): CogWerk
    {

        foreach ($this->cogs as $cog) {
            if (!($cog instanceof Cog)) {
                throw new \Exception("CogWerk can only contain Cog instances");
            }
        }
        $this->cogs = $cogs;
        return $this;
    }






    private function sendDataToCogs($data, $request) {
        $curIndex = -1;
        $next = function($data) use ($request, &$curIndex, &$next) : mixed {
            $curIndex++;
            if ($curIndex < count($this->cogs)) {
                return $this->cogs[$curIndex]->processChunk($request->getChat(), $request, $data, $next);
            }
            return null;
        };
        return $next($data);
    }


    /**
     * @template T
     * @return T
     * @param Cog<T>|class-string<T> $outputCog
     * @throws \Exception
     */
    public function run($outputCog = null)
    {
        if ($outputCog !== null) {
            if ($outputCog instanceof Cog) {
                $this->addCog($outputCog);
            } elseif (is_string($outputCog)) {
                // find the cog class
                foreach ($this->cogs as $cog) {
                    if ($cog instanceof $outputCog) {
                        $outputCog = $cog;
                        break;
                    }
                }
            } else {
                throw new \Exception("Invalid outputCog: must be instance of Cog or class-string");
            }

        } else {
            // The last cog is the output cog
            $outputCog = $this->cogs[count($this->cogs) - 1];
        }
        $chatSerializer = new JsonCogToChatSerializer();


        $model = match ($this->flavor) {
            CogWerkFlavorEnum::DEFAULT => Kindergarden::defaults()->getDefaultModel(),
            CogWerkFlavorEnum::REASONING => Kindergarden::defaults()->getDefaultReasoningModel()
        };

        $request = $this->client->createRequest($model);

        $chat = $chatSerializer->convert($this->cogs);
        $request->setChat($chat);

        $request->enableStreaming(function($data) use ($request) {

            $this->sendDataToCogs($data, $request);
        });
        $this->sendDataToCogs(new Cog\Type\StartOfStream(), $request);
        $request->execute();
        $this->sendDataToCogs(new Cog\Type\EndOfStream($request->completedNaturally()), $request);


        return $outputCog;

    }

}
