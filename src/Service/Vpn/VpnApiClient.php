<?php

declare(strict_types=1);

namespace App\Service\Vpn;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VpnApiClient implements VpnApiClientInterface
{
    private readonly string $baseUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        string $baseUrl,
        private readonly string $apiKey,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function createWireGuardConfig(int $userId): string
    {
        return $this->createConfig('wireguard', $userId);
    }

    public function createVlessRealityConfig(int $userId): string
    {
        return $this->createConfig('vless-reality', $userId);
    }

    private function createConfig(string $protocol, int $userId): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->baseUrl.'/configs', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'protocol' => $protocol,
                    'user_id' => $userId,
                    'duration_hours' => 24,
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['config'])) {
                throw new \RuntimeException('Invalid API response: missing config data');
            }

            return $data['config'];
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to create VPN config: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteConfig(string $configId): bool
    {
        try {
            $response = $this->httpClient->request('DELETE', $this->baseUrl.'/configs/'.$configId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
            ]);

            return 204 === $response->getStatusCode();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to delete VPN config: '.$e->getMessage(), 0, $e);
        }
    }

    /** @return array<array-key, mixed>|null */
    public function getConfigStatus(string $configId): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $this->baseUrl.'/configs/'.$configId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
            ]);

            if (404 === $response->getStatusCode()) {
                return null;
            }

            return $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to get VPN config status: '.$e->getMessage(), 0, $e);
        }
    }
}
