<?php

namespace Bonnier\WP\SoMe\Repositories;


use Bonnier\WP\SoMe\Services\FacebookAccessTokenService;
use Bonnier\WP\SoMe\Settings\SettingsPage;

class FacebookRepository extends BaseRepository
{
    private $instagramID;
    public function __construct()
    {
        $this->instagramID = get_option(SettingsPage::INSTAGRAM_ID);
        parent::__construct('https://graph.facebook.com/v2.12/', with(new FacebookAccessTokenService())->get());
    }
    
    public function getInstagramAccounts()
    {
        $accounts = $this->get('me/accounts', [
            'fields' => 'instagram_business_account.fields(name)'
        ]);
        if($accounts && $accounts->data) {
            return collect($accounts->data)->map(function($account) {
                return [
                    'id' => $account->instagram_business_account->id,
                    'name' => $account->instagram_business_account->name
                ];
            });
        }
        
        return null;
    }
    
    public function getLatestInstagramPosts($limit = 10, $cursor = null, $fields = 'media_url,permalink,caption')
    {
        if(!$this->instagramID) {
            return null;
        }
        
        $query = [
            'limit' => $limit,
            'fields' => $fields
        ];
        
        if($cursor) {
            $query['after'] = $cursor;
        }
        
        return $this->get($this->instagramID . '/media', $query);
    }
}
