<?php

namespace Bonnier\WP\SoMe\Providers;

use Bonnier\WP\SoMe\ResourceOwners\FacebookResourceOwner;
use Bonnier\WP\SoMe\SoMe;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class FacebookProvider extends AbstractProvider
{
    private $endpoint;
    private $graphUrl;
    
    public function __construct()
    {
        $this->endpoint = 'https://www.facebook.com/v2.12/';
        $this->graphUrl = 'https://graph.facebook.com/v2.12/';
        $client = SoMe::instance()->getSettings()->getFacebookClient();
        
        parent::__construct([
            'clientId' => $client['client_id'],
            'clientSecret' => $client['client_secret'],
            'redirectUri' => preg_replace('#^http://#', 'https://', $client['redirect_uri']),
        ]);
    }
    
    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->endpoint . 'dialog/oauth';
    }
    
    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->graphUrl . 'oauth/access_token';
    }
    
    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->graphUrl . 'me?access_token=' . $token->getToken();
    }
    
    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['manage_pages', 'instagram_basic'];
    }
    
    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() > 299) {
            throw new IdentityProviderException(
                $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }
    
    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new FacebookResourceOwner($response);
    }
}
