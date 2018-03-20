<?php

namespace Bonnier\WP\SoMe\Repositories;

class SoMeRepository
{
    private $instagram;
    private $pinterest;
    
    public function __construct()
    {
        $this->instagram = new InstagramRepository();
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
        
        // PLACEHOLDER INSTAGRAM //
        $instagram = new \stdClass();
        $instagram->data = [];
        $instagram->page = new \stdClass();
        $instagram->page->cursor = null;
        $instagram->page->next = null;
        // END PLACEHOLDER INSTAGRAM //
        
        $nextCursor = [];
        
        if($pinterest && isset($pinterest->page)) {
            $nextCursor['pin'] = $pinterest->page->cursor;
        }
        if($instagram && isset($instagram->page)) {
            $nextCursor['ins'] = $instagram->page->cursor;
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
