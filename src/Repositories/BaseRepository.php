<?php

namespace Bonnier\WP\SoMe\Repositories;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use League\OAuth2\Client\Token\AccessToken;

class BaseRepository implements RepositoryContract
{
    protected $client;
    protected $active;
    
    public function __construct(string $base_uri, AccessToken $accessToken = null)
    {
        $this->active = false;
        if ($accessToken && $accessToken->getToken()) {
            $this->active = true;
            $this->client = $this->createClient($base_uri, $accessToken);
        }
    }
    
    public function createClient(string $base_uri, AccessToken $accessToken = null): Client
    {
        $config = [
            'base_uri' => $base_uri,
            'headers' => [
                'Accept' => 'application/json'
            ],
        ];
        
        if ($accessToken) {
            $config['headers'] = [
                'Authorization' => 'Bearer ' . $accessToken->getToken()
            ];
        }
    
        return new Client($config);
    }
    
    public function get(string $uri, array $query): ?\stdClass
    {
        try {
            $response = $this->client->get($uri, [
                'query' => $query
            ]);
        } catch (ClientException $e) {
            return null;
        }
    
        $result = json_decode($response->getBody()->getContents());
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
    
        return null;
    }
    
    public function post(string $uri, array $params): ?\stdClass
    {
        try {
            $response = $this->client->post($uri, [
                'params' => $params,
            ]);
        } catch (ClientException $e) {
            return null;
        }
        
        $result = json_decode($response->getBody()->getContents());
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
        
        return null;
    }

    public function isActive()
    {
        return $this->active;
    }
}
