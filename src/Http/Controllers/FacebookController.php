<?php

namespace Bonnier\WP\SoMe\Http\Controllers;

use Bonnier\WP\SoMe\Providers\FacebookProvider;
use Bonnier\WP\SoMe\Services\FacebookAccessTokenService;
use Bonnier\WP\SoMe\Settings\SettingsPage;
use WP_REST_Request;
use WP_REST_Response;

class FacebookController extends OAuthController
{
    public function __construct()
    {
        parent::__construct(new FacebookProvider(), new FacebookAccessTokenService());
    }
    
    public function options(WP_REST_Request $request): WP_REST_Response
    {
        $currentUser = wp_get_current_user();
        if(!$currentUser || user_can($currentUser, 'manage_options')) {
            return $this->redirect($request->get_param('redirect_uri') ?: $this->homeUri);
        }
        
        switch($request->get_param('option')) {
            case 'instagram_account':
                return $this->saveInstagramAccount($request);
        }
        
        return $this->errorRedirect($request, 'Could not save setting!');
    }
    
    private function saveInstagramAccount(WP_REST_Request $request)
    {
        $instagramId = $request->get_param('instagram_account_id');
        if(!$instagramId) {
            return $this->errorRedirect($request, 'No Instagram Account ID Submitted.');
        }
        
        if(add_option(SettingsPage::INSTAGRAM_ID, $instagramId)) {
            return $this->redirect($request->get_param('redirect_uri') ?: $this->homeUri);
        }
        
        return $this->errorRedirect($request, 'Error saving Instagram account id');
    }
    
    private function errorRedirect(WP_REST_Request $request, string $message)
    {
        if($redirect = $request->get_param('redirect_uri')) {
            if(str_contains($redirect, '?')) {
                return $this->redirect($redirect . '&error=' . urlencode($message));
            }
            return $this->redirect($redirect . '?error=' . urlencode($message));
        }
    
        return $this->redirect($this->homeUri);
    }
}
