<?php

namespace Bonnier\WP\SoMe\Services;

use League\OAuth2\Client\Token\AccessToken;

class AccessTokenService implements AccessTokenServiceContract
{
    protected $accessTokenStoreKey;
    
    /**
     * @param AccessToken $accessToken
     *
     * @return bool
     */
    public function save(AccessToken $accessToken): bool
    {
        return add_option($this->accessTokenStoreKey, $accessToken->jsonSerialize());
    }
    
    /**
     * @return AccessTokenService|null
     */
    public function get(): ?AccessToken
    {
        if ($storedToken = get_option($this->accessTokenStoreKey)) {
            return new AccessToken($storedToken);
        }
        
        return null;
    }
    
    /**
     * @return bool
     */
    public function delete(): bool
    {
        return delete_option($this->accessTokenStoreKey);
    }
}
