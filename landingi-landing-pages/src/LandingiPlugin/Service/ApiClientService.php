<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient\InvalidTokenException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient\LandingiApiErrorException;
use Psr\Http\Message\ResponseInterface;

class ApiClientService
{
    private Client $guzzle;
    private string $landingListPath = 'wordpress/landings';

    public function __construct($url, $apiToken)
    {
        $this->guzzle = $this->createClient($url, $apiToken);
    }

    /**
     * @return mixed
     * @throws LandingiApiErrorException
     * @throws InvalidTokenException
     * @throws \JsonException
     */
    public function getLandingsForAccount(int $page = 1, ?string $searchPhrase = ''): mixed
    {
        try {
            $request = $this->get($this->landingListPath, ['page' => $page, 'searchPhrase' => $searchPhrase]);
        } catch (ClientException $exception) {
            throw new InvalidTokenException($exception->getMessage());
        } catch (ServerException $exception) {
            throw new LandingiApiErrorException();
        }

        return json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function createClient($url, $apiToken): Client
    {
        return new Client([
            'base_uri' => $url,
            'headers' => [
                'apiKey' => $apiToken
            ],
            'verify' => false,
        ]);
    }

    private function get($path, $params): ResponseInterface
    {
        return $this->guzzle->get($path, [
            'query' => $params
        ]);
    }
}
