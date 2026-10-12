<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Model\Landing;
use Psr\Http\Message\ResponseInterface;

class StaticLandingApiClientService
{
    private string $endpoint;
    private string $notFoundUrl;
    private Client $guzzle;

    public function __construct(string $endpoint, string $notFoundUrl)
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->notFoundUrl = $notFoundUrl;
        $this->guzzle = new Client([
            'connect_timeout' => 30,
            'http_errors' => false,
            'timeout' => 30,
        ]);
    }

    public function getLandingFromApi(
        Landing $landing,
        string $currentHost,
        string $currentPath,
        array $getParameters = []
    ): array {
        $headers = $this->createTrackingHeaders($currentHost, $currentPath);

        try {
            $metadataResponse = $this->get($this->buildMetadataUrl($landing), $headers);
        } catch (RequestException $exception) {
            return $this->handleExceptionResponse($exception);
        }

        $metadata = $metadataResponse->getBody()->getContents();
        $landingData = json_decode($metadata);

        if (!is_object($landingData)) {
            return [
                'status_code' => $metadataResponse->getStatusCode(),
                'content' => $this->modifyLandingContent($metadata, $landing),
            ];
        }

        if (
            isset($landingData->statusCode, $landingData->url)
            && in_array($landingData->statusCode, [301, 302], true)
        ) {
            return [
                'status_code' => $landingData->statusCode,
                'redirect' => $this->buildRedirectUrl($landingData, $getParameters),
            ];
        }

        $landingUrl = $this->chooseLandingUrl($landingData);

        try {
            $contentResponse = $this->get($this->ensureUrlHasProtocol($landingUrl), $headers);
        } catch (RequestException $exception) {
            return $this->handleExceptionResponse($exception);
        }

        $content = $contentResponse->getBody()->getContents();

        if ($this->notFoundUrl === $landingUrl) {
            $content = $this->modifyNotFoundContent($content);
        }

        return [
            'status_code' => $contentResponse->getStatusCode(),
            'content' => $this->modifyLandingContent($content, $landing),
        ];
    }

    private function buildMetadataUrl(Landing $landing): string
    {
        return sprintf('%s/%s', $this->endpoint, $landing->getHash());
    }

    private function chooseLandingUrl(object $landingData): string
    {
        if (isset($landingData->statusCode) && 404 === $landingData->statusCode) {
            return $this->notFoundUrl;
        }

        if (
            isset($landingData->published, $landingData->statusCode, $landingData->url)
            && true === $landingData->published
            && 200 === $landingData->statusCode
        ) {
            return $landingData->url;
        }

        return $this->notFoundUrl;
    }

    private function ensureUrlHasProtocol(string $url): string
    {
        $url = trim($url);

        if (1 === preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return sprintf('https://%s', ltrim($url, '/'));
    }

    private function buildRedirectUrl(object $landingData, array $getParameters): string
    {
        $url = $this->ensureUrlHasProtocol($landingData->url);
        $getParams = isset($landingData->get_params) ? $landingData->get_params : false;
        $shouldPreserveGetParameters = filter_var($getParams, FILTER_VALIDATE_BOOLEAN);

        if (true !== $shouldPreserveGetParameters || empty($getParameters)) {
            return $url;
        }

        $query = http_build_query($getParameters);

        if ('' === $query) {
            return $url;
        }

        $fragment = '';
        $fragmentPosition = strpos($url, '#');

        if (false !== $fragmentPosition) {
            $fragment = substr($url, $fragmentPosition);
            $url = substr($url, 0, $fragmentPosition);
        }

        $separator = false === strpos($url, '?') ? '?' : '&';
        $lastCharacter = substr($url, -1);

        if ('?' === $lastCharacter || '&' === $lastCharacter) {
            $separator = '';
        }

        return $url . $separator . $query . $fragment;
    }

    private function createTrackingHeaders(string $currentHost, string $currentPath): array
    {
        return [
            'X-export-source' => 'wordpress',
            'X-export-host' => $currentHost,
            'X-export-path' => $currentPath,
            'User-Agent' => sprintf('Landingi Wordpress Plugin PHP/%s', PHP_VERSION),
        ];
    }

    private function get(string $url, array $headers): ResponseInterface
    {
        return $this->guzzle->get($url, [
            'headers' => $headers,
            'version' => 1.1,
        ]);
    }

    private function handleExceptionResponse(RequestException $exception): array
    {
        $response = $exception->getResponse();

        if (null === $response) {
            return [
                'status_code' => 500,
                'content' => null,
            ];
        }

        return [
            'status_code' => $response->getStatusCode(),
            'content' => $response->getBody()->getContents(),
        ];
    }

    private function modifyNotFoundContent(string $content): string
    {
        return str_replace(
            ['href="/', 'src="/'],
            ['href="https://get.landingi.com/', 'src="https://get.landingi.com/'],
            $content
        );
    }

    private function modifyLandingContent(string $content, Landing $landing): ?string
    {
        $content = preg_replace(
            '#(?<=["\'])(?:\./|/)?assets/js/landend/(frodo|bilbo)\.js(?=(?:\?[^"\']*)?["\'])#',
            'https://landingistats.com/assets/js/landend/$1.js',
            $content
        );

        return preg_replace_callback(
            '/<(form)(\s?(?:[^>"\']|"[^"]*"|\'[^\']*\')*)>/is',
            function (array $match) use ($landing) {
                $exportHash = htmlspecialchars($landing->getHash(), ENT_QUOTES, 'UTF-8');

                return sprintf('<%s data-export-hash="%s"%s>', $match[1], $exportHash, $match[2]);
            },
            $content
        );
    }
}
