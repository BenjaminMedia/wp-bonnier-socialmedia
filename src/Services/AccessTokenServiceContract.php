<?php


namespace Bonnier\WP\SoMe\Services;


use League\OAuth2\Client\Token\AccessToken;

interface AccessTokenServiceContract
{
    public function save(AccessToken $accessToken): bool;
    
    public function get(): ?AccessToken;
    
    public function delete(): bool;
}
