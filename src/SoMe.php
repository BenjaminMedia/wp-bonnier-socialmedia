<?php

namespace Bonnier\WP\SoMe;

use Bonnier\WP\SoMe\Http\Routes;
use Bonnier\WP\SoMe\Providers\InstagramProvider;
use Bonnier\WP\SoMe\Providers\PinterestProvider;
use Bonnier\WP\SoMe\Repositories\PinterestRepository;
use Bonnier\WP\SoMe\Repositories\SoMeRepository;
use Bonnier\WP\SoMe\Settings\SettingsPage;

class SoMe
{
    /** @var SoMe */
    private static $instance;
    
    private $settings;
    private $routes;
    private $soMeRepo;
    
    /**
     * @return SoMe
     */
    public static function instance()
    {
        if(self::$instance === null) {
            self::$instance = new self;
            self::$instance->bootstrap();
            do_action('bp_some_loaded');
        }
        return self::$instance;
    }
    
    /**
     * @return SettingsPage
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * @return Routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * @return SoMeRepository
     */
    public function getSoMeRepo()
    {
        return $this->soMeRepo;
    }
    
    private function bootstrap()
    {
        $this->routes = new Routes();
        $this->settings = new SettingsPage();
        $this->soMeRepo = new SoMeRepository();
    
        if(!session_id()) {
            session_start();
        }
    }
}
