<?php

namespace Bonnier\WP\SoMe\Repositories;

use Bonnier\WP\SoMe\Helpers\Storage;
use Bonnier\WP\SoMe\Services\FacebookAccessTokenService;
use Bonnier\WP\SoMe\Settings\SettingsPage;
use Illuminate\Support\Collection;

class FacebookRepository extends BaseRepository
{
    const STORAGE_KEY = 'instagramPosts';

    private $instagramID;

    public function __construct()
    {
        $this->instagramID = get_option(SettingsPage::INSTAGRAM_ID);
        parent::__construct('https://graph.facebook.com/v2.12/', with(new FacebookAccessTokenService())->get());
    }
    
    public function getInstagramAccounts()
    {
        return Storage::remember('instagram-accounts', function () {
            $accounts = $this->get('me/accounts', [
                'fields' => 'instagram_business_account.fields(name)'
            ]);
            if ($accounts && $accounts->data) {
                return collect($accounts->data)->map(function ($account) {
                    return [
                        'id' => $account->instagram_business_account->id,
                        'name' => $account->instagram_business_account->name
                    ];
                });
            }
            return null;
        }, 24 * HOUR_IN_SECONDS);
    }
    
    public function getLatestInstagramPosts($limit = 10, $offset = 0): ?Collection
    {
        if ($posts = Storage::get(self::STORAGE_KEY)) {
            return collect($posts)->slice($offset, $limit);
        }

        return null;
    }

    public function fetchInstagramPosts()
    {
        $response = $this->get(
            sprintf('%s/media', $this->instagramID),
            [
            'limit' => 500,
            'fields' => 'media_url,permalink,caption'
            ]
        );

        if (isset($response->data)) {
            return $response->data;
        }

        return null;
    }
}
