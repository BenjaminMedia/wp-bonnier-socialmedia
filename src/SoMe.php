<?php

namespace Bonnier\WP\SoMe;

use Bonnier\WP\SoMe\Commands\WarmStorage;
use Bonnier\WP\SoMe\Http\Routes;
use Bonnier\WP\SoMe\Repositories\SoMeRepository;
use Bonnier\WP\SoMe\Settings\SettingsPage;

class SoMe
{
    /** @var SoMe */
    private static $instance;
    
    /** @var string Directory of this class */
    private $dir;
    
    /** @var string Basename of this class */
    private $basename;
    
    /** @var string Plugins directory for this plugin */
    private $pluginDir;
    
    /** @var string Plugins url for this plugin */
    private $pluginUrl;
    
    private $settings;
    private $routes;
    private $soMeRepo;
    
    /**
     * @return SoMe
     */
    public static function instance()
    {
        if (self::$instance === null) {
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

    public function bootstrap()
    {
        if (defined('WP_CLI') && WP_CLI) {
            WarmStorage::register();
        }
        $this->routes = new Routes();
        $this->settings = new SettingsPage();
        $this->soMeRepo = new SoMeRepository();

        if (!session_id()) {
            session_start();
        }
    }

    private function __construct()
    {
        // Set plugin file variables
        $this->dir = __DIR__;
        $this->basename = plugin_basename($this->dir);
        $this->pluginDir = plugin_dir_path($this->dir);
        $this->pluginUrl = plugin_dir_url($this->dir);
    }
}
