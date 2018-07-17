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
    
    public function getFeed($offset = 0, $items = 10)
    {
        $pinterest = $instagram = null;

        if ($this->pinterest->isActive() && $this->facebook->isActive()) {
            $pinterest = $this->pinterest->getLatestPins(floor($items / 2), $offset);
            $instagram = $this->facebook->getLatestInstagramPosts(ceil($items / 2), $offset);
        } elseif ($this->pinterest->isActive()) {
            $pinterest = $this->pinterest->getLatestPins($items, $offset);
        } else {
            $instagram = $this->facebook->getLatestInstagramPosts($items, $offset);
        }

        $response = [
            'feed' => [],
            'offset' => $offset + $items,
        ];
        
        if ($pinterest) {
            $response['feed']['pinterest'] = $pinterest ?? [];
        }
        if ($instagram) {
            $response['feed']['instagram'] = $instagram ?? [];
        }

        return $response;
    }
}
