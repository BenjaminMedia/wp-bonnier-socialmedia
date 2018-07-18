<?php

namespace Bonnier\WP\SoMe\Repositories;

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
        if ($pins = $this->retrieveData(self::STORAGE_KEY)) {
            return $pins->slice($offset, $limit);
        }

        return null;
    }

    public function storePins()
    {
        if (!$this->isActive()) {
            return;
        }
        $response = $this->get('v1/me/pins', [
            'limit' => 500,
            'fields' => 'url,note,media,image'
        ]);

        if (isset($response->data)) {
            $this->storeData(self::STORAGE_KEY, $response->data);
        }
    }
}
