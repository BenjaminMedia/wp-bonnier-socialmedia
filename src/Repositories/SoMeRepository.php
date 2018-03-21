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
            $pinterestCursor = $cursors['pin'];
            $instagramCursor = $cursors['ins'];
        }
        
        $pinterest = $this->pinterest->getLatestPins(floor($items/2), $pinterestCursor);
        $instagram = $this->facebook->getLatestInstagramPosts(ceil($items/2), $instagramCursor);
        
        $nextCursor = [];
        
        if($pinterest && isset($pinterest->page)) {
            $nextCursor['pin'] = $pinterest->page->cursor;
        }
        if($instagram && isset($instagram->paging)) {
            $nextCursor['ins'] = $instagram->paging->cursors->after;
        }
        
        return [
            'feed' => [
                'pinterest' => $pinterest->data ?? [],
                'instagram' => $instagram->data ?? [],
            ],
            'cursor' => base64_encode(serialize($nextCursor))
        ];
    }
}
