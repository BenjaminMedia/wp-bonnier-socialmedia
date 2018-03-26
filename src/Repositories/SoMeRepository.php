<?php

namespace Bonnier\WP\SoMe\Repositories;

class SoMeRepository
{
    private $facebook;
    private $pinterest;
    
    public function __construct()
    {
        $this->facebook = new FacebookRepository();
        $this->pinterest = new PinterestRepository();
    }
    
    public function getFeed($cursor = null, $items = 10)
    {
        $pinterestCursor = null;
        $instagramCursor = null;
        if($cursor) {
            $cursors = unserialize(base64_decode($cursor));
            $pinterestCursor = $cursors['pin'] ?? null;
            $instagramCursor = $cursors['ins'] ?? null;
        }
        
        $pinterest = $this->pinterest->getLatestPins(floor($items/2), $pinterestCursor);
        $instagram = $this->facebook->getLatestInstagramPosts(ceil($items/2), $instagramCursor);
        
        $nextCursor = [];
        
        $response = [
            'feed' => []
        ];
        
        if($pinterest) {
            $response['feed']['pinterest'] = $pinterest->data ?? [];
            $nextCursor['pin'] = $pinterest->page->cursor;
        }
        if($instagram) {
            $response['feed']['instagram'] = $instagram->data ?? [];
            $nextCursor['ins'] = $instagram->paging->cursors->after;
        }
        
        $response['cursor'] = base64_encode(serialize($nextCursor));
        
        return $response;
    }
}
