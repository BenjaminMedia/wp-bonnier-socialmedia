<?php

namespace Bonnier\WP\SoMe\Http\Controllers;

use Bonnier\WP\SoMe\Services\AccessTokenServiceContract;
use Bonnier\WP\SoMe\SoMe;
use League\OAuth2\Client\Provider\AbstractProvider;
use WP_REST_Request;
use WP_REST_Response;

class OAuthController implements OAuthControllerContract
{
    /** @var AbstractProvider */
    private $provider;
    
    /** @var AccessTokenServiceContract */
    private $accessTokenService;
    
    private $homeUri;
    
    public function __construct(AbstractProvider $provider, AccessTokenServiceContract $accessTokenService)
    {
        $this->provider = $provider;
        $this->accessTokenService = $accessTokenService;
        $this->homeUri = SoMe::instance()->getRoutes()->getHomeUrl();
    }
    
    public function authorize(WP_REST_Request $request): WP_REST_Response
    {
        $redirectUri = $request->get_param('redirect_uri') ?? $this->homeUri;
        
        $authUrl = $this->provider->getAuthorizationUrl();
        
        $_SESSION['SoMeState'] = $this->provider->getState();
        $_SESSION['SoMeRedirect'] = $redirectUri;
        
        return $this->redirect($authUrl);
    }
    
    public function callback(WP_REST_Request $request): WP_REST_Response
    {
        if (!$this->isStateValid($request->get_param('state') ?? null)) {
            // Request has been tinkered with - let's forget about it and return home.
            return $this->redirect($this->homeUri);
        }
    
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $request->get_param('code') ?? null
        ]);
    
        $redirectUri = $_SESSION['SoMeRedirect'] ?? $this->homeUri;
    
        if ($accessToken->getToken()) {
            $this->accessTokenService->save($accessToken);
        }
    
        return $this->redirect($redirectUri);
    }
    
    public function logout(WP_REST_Request $request): WP_REST_Response
    {
        $this->accessTokenService->delete();
        
        $redirectUri = $request->get_param('redirect_uri') ?? $this->homeUri;
        
        return $this->redirect($redirectUri);
    }
    
    protected function isStateValid(string $state): bool
    {
        return isset($_SESSION['SoMeState']) &&
            hash_equals($_SESSION['SoMeState'], $state);
    }
    
    /**
     * @param string $location
     *
     * @return WP_REST_Response
     */
    protected function redirect($location): WP_REST_Response
    {
        return new WP_REST_Response(null, 302, ['Location' => $location]);
    }
}
