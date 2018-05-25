<?php

namespace Bonnier\WP\SoMe\Repositories;

use Bonnier\WP\SoMe\Services\PinterestAccessTokenService;

class PinterestRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('https://api.pinterest.com/', with(new PinterestAccessTokenService())->get());
    }
    
    public function getLatestPins($limit = 10, $cursor = null, $fields = 'url,note,media,image')
    {
        $query = [
            'limit' => $limit,
            'fields' => $fields
        ];

        if ($cursor) {
            $query['cursor'] = $cursor;
        }
        
        return $this->get('v1/me/pins/', $query);
    }
}
