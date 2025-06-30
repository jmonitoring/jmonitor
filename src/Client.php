<?php

declare(strict_types=1);

namespace Jmonitor;

use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Jmonitor\Exceptions\InvalidServerResponseException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    private const BASE_URL = 'https://collector.jmonitor.io';

    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var RequestFactoryInterface&StreamFactoryInterface
     */
    private $messageFactory;

    /**
     * @var string
     */
    private $projectApiKey;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(string $projectApiKey, ?ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->messageFactory = $factory ?? ($this->httpClient instanceof RequestFactoryInterface && $this->httpClient instanceof StreamFactoryInterface ? $this->httpClient : new Psr17Factory());
        $this->projectApiKey = $projectApiKey;

        $this->baseUrl = $env['JMONITOR_COLLECTOR_URL'] ?? self::BASE_URL;
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    public function sendMetrics(array $metrics): void
    {
        $request = $this->createRequest('POST', $this->baseUrl.'/metrics', $this->buildHeaders(), json_encode($metrics));
        $response = $this->sendRequest($request);
        // $jsonResponse = json_decode($response, true);
    }

    private function createRequest(string $method, string $uri, array $headers = [], ?string $body = null): RequestInterface
    {
        $request = $this->messageFactory->createRequest($method, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        if ($body === null) {
            return $request;
        }

        $stream = $this->messageFactory->createStream($body);

        if ($stream->isSeekable()) {
            $stream->seek(0);
        }

        return $request->withBody($stream);
    }

    private function sendRequest(RequestInterface $request): string
    {
        $response = $this->httpClient->sendRequest($request);

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 300) {
            throw new InvalidServerResponseException((string) $request->getUri(), $statusCode);
        }

        return (string) $response->getBody();
    }

    private function buildHeaders(): array
    {
        return [
            'X-JMONITOR-VERSION' => Jmonitor::VERSION,
            'X-JMONITOR-API-KEY' => $this->projectApiKey,
        ];
    }
}
