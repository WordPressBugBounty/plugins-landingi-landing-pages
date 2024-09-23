<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Model\Landing;

class LandendApiClientService
{
    private $guzzle;
    private $landingListPath = '/api/render';
    private $disallowedStatusCodes = [301, 302, 307, 308];

    public function __construct($url)
    {
        $this->guzzle = $this->createClient($url);
    }

    /**
     * @param Landing $landing
     * @param string $currentHost
     * @param string $currentPath
     * @param string|null $conversionHash
     *
     * @return array
     */
    public function getLandingFromApi(Landing $landing, $currentHost, $currentPath, $conversionHash = null)
    {
        $data = [
            'export_hash' => $landing->getHash(),
            'tid' => $landing->getTestId()
        ];

        if ($conversionHash) {
            $data['conversion_hash'] = $conversionHash;
        }

        $headers = [
            'X-export-source' => 'wordpress',
            'X-export-host' => $currentHost,
            'X-export-path' => $currentPath,
            'User-Agent' => sprintf('Landingi Wordpress Plugin PHP/%s', PHP_VERSION),
        ];

        try {
            $response = $this->get($this->landingListPath, $data, $headers);
        } catch (RequestException $e) {
            return $this->handleExceptionResponses($e);
        }

        return array_merge(
            ['status_code' => $response->getStatusCode()],
            json_decode($response->getBody()->getContents(), true)
        );
    }

    private function createClient($url)
    {
        return new Client([
            'base_uri' => $url,
            'verify' => false,
        ]);
    }

    private function get($path, array $params, array $headers = [])
    {
        return $this->guzzle->get($path, [
            'query' => $params,
            'headers' => $headers,
        ]);
    }

    /**
     * @param RequestException $e
     * @return array
     */
    private function handleExceptionResponses(RequestException $e)
    {
        $response = $e->getResponse();

        if (null === $response) {
            return [
                'status_code' => 500,
                'content' => null,
            ];
        }

        if ($response->getStatusCode() === 404) {
            return [
                'status_code' => 404,
                'content' => $response->getBody()->getContents(),
            ];
        }

        if (in_array($response->getStatusCode(), $this->disallowedStatusCodes, true)) {
            return [
                'status_code' => 500,
                'content' => null,
            ];
        }

        return [
            'status_code' => $response->getStatusCode(),
            'content' => null,
        ];
    }
}
