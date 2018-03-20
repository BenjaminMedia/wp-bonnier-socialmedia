<?php

namespace Bonnier\WP\SoMe\Repositories;


use Bonnier\WP\SoMe\Services\InstagramAccessTokenService;

class InstagramRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('https://graph.facebook.com/v2.12/', with(new InstagramAccessTokenService())->get());
    }
}
