<?php

namespace Bonnier\WP\SoMe\Repositories;

use Bonnier\WP\SoMe\Helpers\Storage;
use Bonnier\WP\SoMe\Services\PinterestAccessTokenService;
use Illuminate\Support\Collection;

class PinterestRepository extends BaseRepository
{
    const STORAGE_KEY = 'pinterestPins';

    public function __construct()
    {
        parent::__construct('https://api.pinterest.com/', with(new PinterestAccessTokenService())->get());
    }
    
    public function getLatestPins($limit = 10, $offset = 0): ?Collection
    {
        if ($posts = Storage::get(self::STORAGE_KEY)) {
            return collect($posts)->slice($offset, $limit);
        }

        return null;
    }

    public function fetchPins()
    {
        $response = $this->get('v1/me/pins', [
            'limit' => 500,
            'fields' => 'url,note,media,image'
        ]);

        if (isset($response->data)) {
            return $response->data;
        }

        return null;
    }
}
