<?php

namespace Bonnier\WP\SoMe\Http;

use Bonnier\WP\SoMe\Http\Controllers\InstagramController;
use Bonnier\WP\SoMe\Http\Controllers\InstagramOAuthController;
use Bonnier\WP\SoMe\Http\Controllers\PinterestController;
use WP_REST_Server;

class Routes
{
    const BASE_PREFIX = 'wp-json';
    
    const PLUGIN_PREFIX = 'bp-some';
    
    const INSTAGRAM_CALLBACK = 'instagram/callback';
    
    const PINTEREST_CALLBACK = 'pinterest/callback';
    
    const PINTEREST_AUTHORIZE = 'pinterest/authorize';
    
    const PINTEREST_LOGOUT = 'pinterest/logout';
    
    private $homeUrl;
    
    public function __construct()
    {
        if(function_exists('pll_home_url')) {
            $this->homeUrl = preg_replace('#^http://#', 'https://', pll_home_url());
        } else {
            $this->homeUrl = preg_replace('#^http://#', 'https://', home_url('/'));
        }
        
        add_action('rest_api_init', function() {
            $instagramController = new InstagramController();
            $pinterestController = new PinterestController();
            register_rest_route(self::PLUGIN_PREFIX, self::INSTAGRAM_CALLBACK, [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$instagramController, 'callback'],
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
            trim($this->homeUrl,'/'),
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
    
    public function getInstagramCallbackRoute($uri = false)
    {
        if($uri) {
            return $this->getUri(self::INSTAGRAM_CALLBACK);
        }
        
        return $this->getRoute(self::INSTAGRAM_CALLBACK);
    }
    
    public function getPinterestCallbackRoute($uri = false)
    {
        if($uri) {
            return $this->getUri(self::PINTEREST_CALLBACK);
        }
        
        return $this->getRoute(self::PINTEREST_CALLBACK);
    }
    
    public function getPinterestAuthorizeRoute($uri = false)
    {
        if($uri) {
            return $this->getUri(self::PINTEREST_AUTHORIZE);
        }
        
        return $this->getRoute(self::PINTEREST_AUTHORIZE);
    }
    
    public function getPinterestLogoutRoute($uri = false)
    {
        if($uri) {
            return $this->getUri(self::PINTEREST_LOGOUT);
        }
        
        return $this->getRoute(self::PINTEREST_LOGOUT);
    }
}
