<?php

namespace Bonnier\WP\SoMe\Http\Controllers;

use Bonnier\WP\SoMe\Providers\InstagramProvider;
use Bonnier\WP\SoMe\Services\InstagramAccessTokenService;

class InstagramController extends OAuthController
{
    public function __construct()
    {
        parent::__construct(new InstagramProvider(), new InstagramAccessTokenService());
    }
}
