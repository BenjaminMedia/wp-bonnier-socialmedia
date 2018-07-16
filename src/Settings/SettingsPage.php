<?php

namespace Bonnier\WP\SoMe\Settings;

use Bonnier\WP\SoMe\Providers\FacebookProvider;
use Bonnier\WP\SoMe\Providers\PinterestProvider;
use Bonnier\WP\SoMe\Repositories\FacebookRepository;
use Bonnier\WP\SoMe\Services\FacebookAccessTokenService;
use Bonnier\WP\SoMe\Services\PinterestAccessTokenService;
use Bonnier\WP\SoMe\SoMe;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class SettingsPage
{
    const SETTINGS_KEY = 'bp_some_settings';
    const SETTINGS_GROUP = 'bp_some_settings_group';
    const SETTINGS_SECTION = 'bp_some_settings_section';
    const SETTINGS_PAGE = 'bp_some_settings_page';
    const NOTICE_PREFIX = 'Bonnier SoMe: ';
    const INSTAGRAM_ID = 'bp_some_instagram_id';

    private $fbSettingsFields = [
        'fb_client_id' => [
            'type' => 'text',
            'name' => 'Facebook Client ID'
        ],
        'fb_client_secret' => [
            'type' => 'password',
            'name' => 'Facebook Client Secret'
        ],
        'fb_redirect_uri' => [
            'type' => 'text',
            'name' => 'Facebook OAuth Redirect URI',
            'value' => '',
            'readonly' => true
        ]
    ];

    private $ptSettingsFields = [
        'pt_client_id' => [
            'type' => 'text',
            'name' => 'Pinterest App ID'
        ],
        'pt_client_secret' => [
            'type' => 'password',
            'name' => 'Pinterest App Secret'
        ],
        'pt_redirect_uri' => [
            'type' => 'text',
            'name' => 'Pinterest OAuth Redirect URI',
            'value' => '',
            'readonly' => true
        ]
    ];

    private $settingsValues;

    public function __construct()
    {
        $this->settingsValues = get_option(self::SETTINGS_KEY);
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'register_settings']);

        add_action('bp_some_loaded', [$this, 'bootstrap']);
    }

    public function add_plugin_page()
    {
        add_options_page(
            'SoMe Settings',
            'Bonnier SoMe',
            'manage_options',
            self::SETTINGS_PAGE,
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page()
    {
        ?>
        <div class="wrap">
            <?php
            if ($error = $_GET['error'] ?? null) {
                $this->print_error($error);
            }
            ?>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::SETTINGS_PAGE);
                submit_button();
                ?>
            </form>
            <?php
            $this->renderFacebook();
            $this->renderPinterest();
            ?>
        </div>
        <?php
    }

    public function register_settings()
    {
        if ($this->languages_is_enabled()) {
            $this->enable_language_fields();
        }

        register_setting(
            self::SETTINGS_GROUP,
            self::SETTINGS_KEY,
            [$this, 'sanitize_input']
        );

        add_settings_section(
            self::SETTINGS_SECTION . '_FB',
            'Bonnier SoMe Settings<hr />Facebook (Instagram) Settings',
            [$this, 'print_fb_section_info'],
            self::SETTINGS_PAGE
        );

        foreach ($this->fbSettingsFields as $sKey => $sField) {
            add_settings_field(
                $sKey, // ID
                $sField['name'], // Title
                [$this, $sKey], // Callback
                self::SETTINGS_PAGE, // Page
                self::SETTINGS_SECTION . '_FB' // Section
            );
        }

        add_settings_section(
            self::SETTINGS_SECTION . '_PT',
            'Pinterest Settings',
            [$this, 'print_pt_section_info'],
            self::SETTINGS_PAGE
        );

        foreach ($this->ptSettingsFields as $sKey => $sField) {
            add_settings_field(
                $sKey, // ID
                $sField['name'], // Title
                [$this, $sKey], // Callback
                self::SETTINGS_PAGE, // Page
                self::SETTINGS_SECTION . '_PT' // Section
            );
        }
    }

    public function sanitize_input($input)
    {
        $sanitizedInput = [];

        foreach (array_merge($this->fbSettingsFields, $this->ptSettingsFields) as $sKey => $sField) {
            if (isset($input[$sKey])) {
                if ($sField['type'] === 'checkbox') {
                    $sanitizedInput[$sKey] = absint($input[$sKey]);
                }
                if (in_array($sField['type'], ['text', 'select', 'password'])) {
                    $sanitizedInput[$sKey] = sanitize_text_field($input[$sKey]);
                }
            }
        }

        return $sanitizedInput;
    }

    public function print_fb_section_info()
    {
        print 'Create a Facebook app and enter settings below:';
    }

    public function print_pt_section_info()
    {
        print 'Create a Pinterest app and enter settings below:';
    }

    public function __call($function, $arguments)
    {
        if (!isset($this->fbSettingsFields[$function]) && !isset($this->ptSettingsFields[$function])) {
            return false;
        }

        $field = $this->fbSettingsFields[$function] ?? $this->ptSettingsFields[$function];
        $this->create_settings_field($field, $function);
    }

    public function getFacebookClient($locale = null)
    {
        return [
            'client_id' => $this->get_setting_value('fb_client_id', $locale),
            'client_secret' => $this->get_setting_value('fb_client_secret', $locale),
            'redirect_uri' => SoMe::instance()->getRoutes()->getFacebookCallbackRoute(true)
        ];
    }

    public function getFacebookClientID($locale = null)
    {
        return $this->get_setting_value('fb_client_id', $locale);
    }

    public function getFacebookClientSecret($locale = null)
    {
        return $this->get_setting_value('fb_client_secret', $locale);
    }

    public function getFacebookRedirectUri($locale = null)
    {
        return $this->get_setting_value('fb_redirect_uri', $locale);
    }

    /**
     * @param string|null $locale
     * @return array [
     * 'client_id' => string,
     * 'client_secret' => string,
     * 'redirect_uri' => string
     * ]
     */
    public function getPinterestClient($locale = null)
    {
        return [
            'client_id' => $this->get_setting_value('pt_client_id', $locale),
            'client_secret' => $this->get_setting_value('pt_client_secret', $locale),
            'redirect_uri' => SoMe::instance()->getRoutes()->getPinterestCallbackRoute(true),
        ];
    }

    public function getPinterestClientID($locale = null)
    {
        return $this->get_setting_value('pt_client_id', $locale);
    }

    public function getPinterestClientSecret($locale = null)
    {
        return $this->get_setting_value('pt_client_secret', $locale);
    }

    public function getPinterestRedirectUri($locale = null)
    {
        return $this->get_setting_value('pt_redirect_uri', $locale);
    }

    function print_error($error)
    {
        $out = "<div class='error settings-error notice is-dismissible'>";
        $out .= "<strong>" . self::NOTICE_PREFIX . "</strong><p>$error</p>";
        $out .= "</div>";
        print $out;
    }

    private function get_setting_value($settingKey, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->get_current_locale();
        }
        if (!$this->settingsValues) {
            $this->settingsValues = get_option(self::SETTINGS_KEY);
        }

        if ($locale) {
            $settingKey = $locale . '_' . $settingKey;
        }

        if (isset($this->settingsValues[$settingKey]) && !empty($this->settingsValues[$settingKey])) {
            return $this->settingsValues[$settingKey];
        }
        return false;
    }

    private function enable_language_fields()
    {
        $langEnabledFieldsFB = [];
        $langEnabledFieldsPT = [];

        foreach ($this->get_languages() as $language) {
            foreach ($this->fbSettingsFields as $fieldKey => $settingsField) {
                $localeFieldKey = $language->locale . '_' . $fieldKey;
                $langEnabledFieldsFB[$localeFieldKey] = $settingsField;
                $langEnabledFieldsFB[$localeFieldKey]['name'] .= ' ' . $language->locale;
                $langEnabledFieldsFB[$localeFieldKey]['locale'] = $language->locale;
                if ($fieldKey === 'fb_redirect_uri') {
                    $langEnabledFieldsFB[$localeFieldKey]['value'] = SoMe::instance()->
                    getRoutes()->
                    getFacebookCallbackRoute($uri = true);
                }
            }
            foreach ($this->ptSettingsFields as $fieldKey => $settingsField) {
                $localeFieldKey = $language->locale . '_' . $fieldKey;
                $langEnabledFieldsPT[$localeFieldKey] = $settingsField;
                $langEnabledFieldsPT[$localeFieldKey]['name'] .= ' ' . $language->locale;
                $langEnabledFieldsPT[$localeFieldKey]['locale'] = $language->locale;
                if ($fieldKey === 'pt_redirect_uri') {
                    $langEnabledFieldsPT[$localeFieldKey]['value'] = SoMe::instance()->
                    getRoutes()->
                    getFacebookCallbackRoute($uri = true);
                }
            }
        }

        $this->fbSettingsFields = $langEnabledFieldsFB;
        $this->ptSettingsFields = $langEnabledFieldsPT;
    }

    private function languages_is_enabled()
    {
        return function_exists('Pll') && PLL()->model->get_languages_list();
    }

    private function get_languages()
    {
        if ($this->languages_is_enabled()) {
            return PLL()->model->get_languages_list();
        }
        return false;
    }

    /**
     * Get the current language by looking at the current HTTP_HOST
     *
     * @return null|PLL_Language
     */
    private function get_current_language()
    {
        if ($this->languages_is_enabled()) {
            if ($language = PLL()->model->get_language(pll_current_language())) {
                return $language;
            } else {
                return PLL()->model->get_language(pll_default_language());
            }
        }
        return null;
    }

    private function get_current_locale()
    {
        $currentLang = $this->get_current_language();
        return $currentLang ? $currentLang->locale : null;
    }

    private function get_select_field_options($field)
    {
        if (isset($field['options_callback'])) {
            $options = $this->{$field['options_callback']}($field['locale']);
            if ($options) {
                return $options;
            }
        }

        return [];
    }

    private function getCurrentUrl()
    {
        return sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) ? "https" : "http",
            $_SERVER['HTTP_HOST'],
            $_SERVER['REQUEST_URI']
        );
    }

    private function create_settings_field($field, $fieldKey)
    {
        $fieldName = self::SETTINGS_KEY . "[$fieldKey]";
        $fieldOutput = false;

        if ($field['type'] === 'text') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ?
                esc_attr($this->settingsValues[$fieldKey]) :
                ($field['value'] ?? '');
            $readonly = null;
            if ($field['readonly'] ?? false) {
                $readonly = 'readonly="readonly"';
            }
            $fieldOutput = sprintf(
                '<input type="text" name="%s" value="%s" %s class="regular-text" />',
                $fieldName,
                $fieldValue,
                $readonly
            );
        }
        if ($field['type'] === 'password') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ?
                esc_attr($this->settingsValues[$fieldKey]) :
                ($field['value'] ?? '');
            $readonly = null;
            if ($field['readonly'] ?? false) {
                $readonly = 'readonly="readonly"';
            }
            $fieldOutput = sprintf(
                '<input type="password" name="%s" value="%s" %s class="regular-text" />',
                $fieldName,
                $fieldValue,
                $readonly
            );
        }
        if ($field['type'] === 'checkbox') {
            $checked = isset($this->settingsValues[$fieldKey]) && $this->settingsValues[$fieldKey] ? 'checked' : '';
            $fieldOutput = "<input type='hidden' value='0' name='$fieldName'>";
            $fieldOutput .= "<input type='checkbox' value='1' name='$fieldName' $checked />";
        }
        if ($field['type'] === 'select') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ?
                $this->settingsValues[$fieldKey] :
                ($field['value'] ?? '');
            $fieldOutput = "<select name='$fieldName'>";
            $options = $this->get_select_field_options($field);
            foreach ($options as $option) {
                $selected = ($option['system_key'] === $fieldValue) ? 'selected' : '';
                $fieldOutput .= "<option value='" .$option['system_key'] . "' $selected >" .
                    $option['system_key'] .
                    "</option>";
            }
            $fieldOutput .= "</select>";
        }

        if ($fieldOutput) {
            print $fieldOutput;
        }
    }

    private function renderFacebook()
    {
        if ($this->getFacebookClientID() && $this->getFacebookClientSecret()) {
            ?>
            <div>
                <h3>Connect with Facebook (Instagram)</h3>
                <?php
                if ($fbAccessToken = with(new FacebookAccessTokenService())->get()) {
                    $logout = SoMe::instance()->getRoutes()->getFacebookLogoutRoute($uri = true);
                    try {
                        $fbUser = with(new FacebookProvider())->getResourceOwner($fbAccessToken);
                        ?>
                        <p>Logged into Facebook as <strong><?php echo $fbUser->getName(); ?></strong></p>
                        <p><a href="<?php echo $logout . '?redirect_uri=' . urlencode($this->getCurrentUrl()); ?>"
                              class="button button-secondary">Click here to log out of Facebook</a></p>
                        <?php
                        $this->renderInstagramAccountSelector();
                    } catch (IdentityProviderException $e) {
                        $location = sprintf('%s?redirect_uri=%s', $logout, urlencode($this->getCurrentUrl()));
                        ?>
                        <script type="text/javascript">
                          window.location = "<?php echo $location; ?>";
                        </script>
                        <?php
                        wp_die();
                    }
                } else {
                    $fbAuth = SoMe::instance()->getRoutes()->getFacebookAuthorizeRoute($uri = true);
                    ?>
                    <a href="<?php echo $fbAuth . '?redirect_uri=' . urlencode($this->getCurrentUrl()); ?>"
                       class="button">Click here to connect with Facebook (Instagram)</a>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }

    private function renderInstagramAccountSelector()
    {
        $accounts = with(new FacebookRepository())->getInstagramAccounts();
        $instagramID = get_option(self::INSTAGRAM_ID);
        if ($instagramID) {
            echo sprintf('<h4>Instagram ID: %s</h4>', $instagramID);
        } else {
            echo '<h4>No Instagram account selected</h4>';
        }
        if (!empty($accounts)) {
            echo sprintf('<form method="post" action="%s">', SoMe::instance()->getRoutes()->getFacebookOptionRoute());
            echo '<input type="hidden" name="option" value="instagram_account" />';
            echo sprintf('<input type="hidden" name="redirect_uri" value="%s" />', $this->getCurrentUrl());
            echo '<fieldset><legend>Select Instagram Account</legend>';
            echo '<select name="instagram_account_id" style="min-width: 20%;">';
            echo '<option>SELECT ACCOUNT</option>';
            foreach ($accounts as $account) {
                echo sprintf('<option value="%s">%s</option>', $account['id'], $account['name']);
            }
            echo '</select>';
            echo '<br /><br />';
            echo '<button type="submit" class="button button-primary">Save Selected Instagram Account</button>';
            echo '</fieldset>';
            echo '</form>';
        }
    }

    private function renderPinterest()
    {
        if ($this->getPinterestClientID() && $this->getPinterestClientSecret()) {
            ?>
            <div>
                <h3>Connect with Pinterest</h3>
                <?php
                if ($pinAccessToken = with(new PinterestAccessTokenService())->get()) {
                    $logout = SoMe::instance()->getRoutes()->getPinterestLogoutRoute();
                    try {
                        $pinUser = with(new PinterestProvider())->getResourceOwner($pinAccessToken);
                        ?>
                        <p>Logged into Pinterest as <a href="<?php echo $pinUser->getUrl(); ?>"
                                                       target="_blank"><?php echo $pinUser->getFirstName(); ?></a></p>
                        <p><a href="<?php echo $logout . '?redirect_uri=' . urlencode($this->getCurrentUrl()); ?>"
                              class="button button-secondary">Click here to log out of Pinterest</a></p>
                        <?php
                    } catch (IdentityProviderException $e) {
                        $location = sprintf('%s?redirect_uri=%s', $logout, urlencode($this->getCurrentUrl()));
                        ?>
                        <script type="text/javascript">
                          window.location = "<?php echo $location; ?>";
                        </script>
                        <?php
                        wp_die();
                    }
                } else {
                    $pinAuth = SoMe::instance()->getRoutes()->getPinterestAuthorizeRoute($uri = true);
                    ?>
                    <a href="<?php echo $pinAuth . '?redirect_uri=' . urlencode($this->getCurrentUrl()); ?>"
                       class="button button-primary">Click here to connect with Pinterest</a>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }
}
