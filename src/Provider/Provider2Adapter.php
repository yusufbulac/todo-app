<?php

namespace App\Provider;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Provider2Adapter implements ProviderAdapterInterface
{
    private HttpClientInterface $client;
    private string $url = 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/mock-two';
    private string $name = 'Provider2';


    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchTasks(): array
    {
        try {
            $response = $this->client->request('GET', $this->url);
            $data = $response->toArray();

            $tasks = [];
            foreach ($data as $task) {
                $tasks[] = [
                    'name' => 'task' . $task['id'] . ' - p2',
                    'duration' => $task['sure'],
                    'difficulty' => $task['zorluk'],
                    'provider' => $this->name,
                ];
            }

            return $tasks;

        } catch (Exception $e) {
            throw new RuntimeException('An unexpected error occurred: ' . $e->getMessage());
        }
    }
}