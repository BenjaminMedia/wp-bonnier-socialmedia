<?php

namespace Bonnier\WP\SoMe\Http;

use Bonnier\Willow\MuPlugins\LanguageProvider;
use Bonnier\WP\SoMe\Http\Controllers\FacebookController;
use Bonnier\WP\SoMe\Http\Controllers\PinterestController;
use WP_REST_Server;

class Routes
{
    const BASE_PREFIX = 'wp-json';
    
    const PLUGIN_PREFIX = 'bp-some';
    
    const FACEBOOK_CALLBACK = 'facebook/callback';
    
    const FACEBOOK_AUTHORIZE = 'facebook/authorize';
    
    const FACEBOOK_LOGOUT = 'facebook/logout';
    
    const FACEBOOK_OPTIONS = 'facebook/options';
    
    const PINTEREST_CALLBACK = 'pinterest/callback';
    
    const PINTEREST_AUTHORIZE = 'pinterest/authorize';
    
    const PINTEREST_LOGOUT = 'pinterest/logout';
    
    private $homeUrl;
    
    public function __construct()
    {
        $this->homeUrl = preg_replace('#^http://#', 'https://', LanguageProvider::getHomeUrl('/'));
        if (substr($this->homeUrl, 0, 11) !== 'https://api') {
            if (substr($this->homeUrl, 0, 14) === 'https://admin.') {
                $this->homeUrl = str_replace('https://admin.', 'https://api.', $this->homeUrl);
            } else {
                $this->homeUrl = str_replace('https://', 'https://api.', $this->homeUrl);
            }
        }
        
        add_action('rest_api_init', function () {
            $facebookController = new FacebookController();
            $pinterestController = new PinterestController();
            register_rest_route(self::PLUGIN_PREFIX, self::FACEBOOK_CALLBACK, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$facebookController, 'callback'],
            ]);
            register_rest_route(self::PLUGIN_PREFIX, self::FACEBOOK_AUTHORIZE, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$facebookController, 'authorize']
            ]);
            register_rest_route(self::PLUGIN_PREFIX, self::FACEBOOK_LOGOUT, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$facebookController, 'logout']
            ]);
            register_rest_route(self::PLUGIN_PREFIX, self::FACEBOOK_OPTIONS, [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$facebookController, 'options']
            ]);
            register_rest_route(self::PLUGIN_PREFIX, self::PINTEREST_CALLBACK, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$pinterestController, 'callback']
            ]);
            register_rest_route(self::PLUGIN_PREFIX, self::PINTEREST_AUTHORIZE, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$pinterestController, 'authorize']
            ]);
            register_rest_route(self::PLUGIN_PREFIX, self::PINTEREST_LOGOUT, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$pinterestController, 'logout']
            ]);
        });
    }
    
    public function getHomeUrl()
    {
        return $this->homeUrl;
    }
    
    public function getUri($route)
    {
        return sprintf(
            '%s/%s',
            trim($this->homeUrl, '/'),
            trim($this->getRoute($route), '/')
        );
    }
    
    public function getRoute($route)
    {
        return sprintf(
            '/%s/%s/%s',
            static::BASE_PREFIX,
            static::PLUGIN_PREFIX,
            trim($route, '/')
        );
    }
    
    public function getFacebookCallbackRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::FACEBOOK_CALLBACK);
        }
        
        return $this->getRoute(self::FACEBOOK_CALLBACK);
    }
    
    public function getFacebookAuthorizeRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::FACEBOOK_AUTHORIZE);
        }
        
        return $this->getRoute(self::FACEBOOK_AUTHORIZE);
    }
    
    public function getFacebookLogoutRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::FACEBOOK_LOGOUT);
        }
        
        return $this->getRoute(self::FACEBOOK_LOGOUT);
    }
    
    public function getFacebookOptionRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::FACEBOOK_OPTIONS);
        }
        
        return $this->getRoute(self::FACEBOOK_OPTIONS);
    }
    
    public function getPinterestCallbackRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::PINTEREST_CALLBACK);
        }
        
        return $this->getRoute(self::PINTEREST_CALLBACK);
    }
    
    public function getPinterestAuthorizeRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::PINTEREST_AUTHORIZE);
        }
        
        return $this->getRoute(self::PINTEREST_AUTHORIZE);
    }
    
    public function getPinterestLogoutRoute($uri = false)
    {
        if ($uri) {
            return $this->getUri(self::PINTEREST_LOGOUT);
        }
        
        return $this->getRoute(self::PINTEREST_LOGOUT);
    }
}
