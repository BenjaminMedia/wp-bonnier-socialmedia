<?php

namespace Bonnier\WP\SoMe\Repositories;

use GuzzleHttp\Client;
use League\OAuth2\Client\Token\AccessToken;

interface RepositoryContract
{
    public function createClient(string $base_uri, AccessToken $accessToken): Client;
    
    public function get(string $uri, array $query): ?\stdClass;
    
    public function post(string $uri, array $params): ?\stdClass;
}
