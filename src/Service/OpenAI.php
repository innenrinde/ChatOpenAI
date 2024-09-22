<?php

namespace App\Service;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Tectalic\OpenAi\Authentication;
use Tectalic\OpenAi\Manager;
use Tectalic\OpenAi\Models\ChatCompletions\CreateRequest;

class OpenAI
{
    /**
     * @var string
     */
    private string $token = ""; // Your private chat gpt key here

    /**
     * @var EntityManagerInterface|null
     */
    private ?EntityManagerInterface $entityManager = null;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $message
     * @return String
     * @throws \Tectalic\OpenAi\ClientException
     */
    public function ask(string $message): ?String
    {
        $settings = $this->entityManager->getRepository(Settings::class)->findOneBy(["name" => Settings::TRAINING]);

        $auth = new Authentication($this->token);
        $openaiClient = Manager::build(new Psr18Client(), $auth);

        $response = $openaiClient->chatCompletions()->create(
            new CreateRequest([
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 1000,
                'stop' => ".",
                'temperature' => 0.1,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $settings ? $settings->getValue() : ""
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ],
                ],
            ])
        )->toModel();

        if (
            $response->choices
            && is_array($response->choices)
            && $response->choices[0]->message
            && $response->choices[0]->message->content
        ) {
            return $response->choices[0]->message->content;
        }

        return null;
    }
}