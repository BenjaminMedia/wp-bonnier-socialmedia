<?php

namespace Bonnier\WP\SoMe\Http\Controllers;

use Bonnier\WP\SoMe\Providers\PinterestProvider;
use Bonnier\WP\SoMe\Services\PinterestAccessTokenService;

class PinterestController extends OAuthController
{
    public function __construct()
    {
        parent::__construct(new PinterestProvider(), new PinterestAccessTokenService());
    }
}
