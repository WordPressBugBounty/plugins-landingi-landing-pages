<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient\InvalidTokenException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient\LandingiApiErrorException;

class ApiClientService
{
    private $guzzle;
    private $landingListPath = 'wordpress/landings';

    public function __construct($url, $apiToken)
    {
        $this->guzzle = $this->createClient($url, $apiToken);
    }

    /**
     * @param int $page
     * @param string $searchPhrase
     *
     * @throws InvalidTokenException
     * @throws LandingiApiErrorException
     *
     * @return mixed
     */
    public function getLandingsForAccount($page = 1, $searchPhrase = '')
    {
        try {
            $request = $this->get($this->landingListPath, ['page' => $page, 'searchPhrase' => $searchPhrase]);
        } catch (ClientException $exception) {
            throw new InvalidTokenException();
        } catch (ServerException $exception) {
            throw new LandingiApiErrorException();
        }

        return json_decode($request->getBody()->getContents(), true);
    }

    private function createClient($url, $apiToken)
    {
        return new Client([
            'base_uri' => $url,
            'headers' => [
                'apiKey' => $apiToken
            ],
            'verify' => false,
        ]);
    }

    private function get($path, $params)
    {
        return $this->guzzle->get($path, [
            'query' => $params
        ]);
    }
}
