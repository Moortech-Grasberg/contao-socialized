<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Adapter\Meta;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MetaGraphApiClient
{
    private const BASE_URL = 'https://graph.facebook.com/v19.0';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $params, string $accessToken): array
    {
        $params['access_token'] = $accessToken;

        $response = $this->httpClient->request('POST', self::BASE_URL . '/' . ltrim($endpoint, '/'), [
            'body' => $params,
        ]);

        $data = $response->toArray(false);

        if (isset($data['error'])) {
            throw new MetaApiException(
                $data['error']['message'] ?? 'Unknown Meta API error',
                (int) ($data['error']['code'] ?? 0),
            );
        }

        return $data;
    }
}