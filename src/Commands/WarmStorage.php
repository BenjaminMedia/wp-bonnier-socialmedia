<?php

namespace Bonnier\WP\SoMe\Commands;

use Bonnier\WP\SoMe\Helpers\Storage;
use Bonnier\WP\SoMe\Repositories\FacebookRepository;
use Bonnier\WP\SoMe\Repositories\PinterestRepository;
use WP_CLI;

class WarmStorage
{
    public static function register()
    {
        WP_CLI::add_command('bonnier some', __CLASS__);
    }

    /**
     * Warms the SocialFeed storage
     *
     * ## EXAMPLE
     *     wp bonnier some warm
     */
    public function warm()
    {
        $facebookRepo = new FacebookRepository();
        if ($facebookRepo->isActive() && $posts = $facebookRepo->fetchInstagramPosts()) {
            Storage::set(FacebookRepository::STORAGE_KEY, $posts);
        }
        $pinterestRepo = new PinterestRepository();
        if ($pinterestRepo->isActive() && $pins = $pinterestRepo->fetchPins()) {
            Storage::set(PinterestRepository::STORAGE_KEY, $pins);
        }

        WP_CLI::success('Social Feed Storage Warmed!');
    }

    /**
     * Inspect the SocialFeed storage
     *
     * ## OPTIONS
     *
     * <provider>
     * : The name of the Service Provider
     * ---
     * options:
     *   - facebook
     *   - pinterest
     * ---
     *
     * ## EXAMPLES
     *     wp bonnier some inspect facebook
     */
    public function inspect($args)
    {
        $provider = $args[0];
        if ($provider === 'facebook') {
            $data = Storage::get(FacebookRepository::STORAGE_KEY);
        } else {
            $data = Storage::get(PinterestRepository::STORAGE_KEY);
        }

        if (!$data) {
            WP_CLI::error(sprintf('No data stored for the %s provider!', $provider));
            return;
        }

        WP_CLI::success(sprintf('%s items stored!', count($data)));
        WP_CLI::line(sprintf('Example:'));
        WP_CLI::line(json_encode($data[0], JSON_PRETTY_PRINT));
    }
}
