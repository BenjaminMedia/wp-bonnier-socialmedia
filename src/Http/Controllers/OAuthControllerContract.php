<?php

namespace Bonnier\WP\SoMe\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

interface OAuthControllerContract
{
    public function authorize(WP_REST_Request $request): WP_REST_Response;
    
    public function callback(WP_REST_Request $request): WP_REST_Response;
    
    public function logout(WP_REST_Request $request): WP_REST_Response;
}
