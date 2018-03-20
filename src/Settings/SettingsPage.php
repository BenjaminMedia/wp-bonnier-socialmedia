<?php

namespace Bonnier\WP\SoMe\Settings;

use Bonnier\WP\SoMe\Providers\InstagramProvider;
use Bonnier\WP\SoMe\Providers\PinterestProvider;
use Bonnier\WP\SoMe\Services\InstagramAccessTokenService;
use Bonnier\WP\SoMe\Services\PinterestAccessTokenService;
use Bonnier\WP\SoMe\SoMe;

class SettingsPage
{
    const SETTINGS_KEY = 'bp_some_settings';
    const SETTINGS_GROUP = 'bp_some_settings_group';
    const SETTINGS_SECTION = 'bp_some_settings_section';
    const SETTINGS_PAGE = 'bp_some_settings_page';
    const NOTICE_PREFIX = 'Bonnier SoMe: ';
    
    private $igSettingsFields = [
        'ig_client_id' => [
            'type' => 'text',
            'name' => 'Instagram Client ID'
        ],
        'ig_client_secret' => [
            'type' => 'text',
            'name' => 'Instagram Client Secret'
        ],
        'ig_redirect_uri' => [
            'type' => 'text',
            'name' => 'Instagram OAuth Redirect URI',
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
            'type' => 'text',
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
    
    public function bootstrap()
    {
        $this->igSettingsFields['ig_redirect_uri']['value'] = SoMe::instance()->getRoutes()->getInstagramCallbackRoute($uri = true);
        $this->ptSettingsFields['pt_redirect_uri']['value'] = SoMe::instance()->getRoutes()->getPinterestCallbackRoute($uri = true);
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
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::SETTINGS_PAGE);
                submit_button();
                ?>
            </form>
            <?php
            $redirectUri = urlencode($this->getCurrentUrl());
            if($this->getInstagramClientID() && $this->getInstagramClientSecret()) {
                ?>
                <div>
                    <h3>Connect with Instagram</h3>
                    <?php
                    if ($instaAccessToken = with(new InstagramAccessTokenService())->get()) {
                        $instaUser = with(new InstagramProvider())->getResourceOwner($instaAccessToken);
                        ?>
                        <p>Logged into Instagram as <a href="<?php echo $instaUser->getUrl(); ?>"
                                                       target="_blank"><?php echo $instaUser->getName(); ?></a></p>
                        <?php
                    } else {
                        $instaAuth = SoMe::instance()->getRoutes()->getInstagramCallbackRoute($uri = true);
                        ?>
                        <a href="<?php echo $instaAuth . '?redirect_uri=' . $redirectUri; ?>" class="button">Click here to connect with Instagram</a>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            if($this->getPinterestClientID() && $this->getPinterestClientSecret()) {
                ?>
                <div>
                    <h3>Connect with Pinterest</h3>
                    <?php
                    if ($pinAccessToken = with(new PinterestAccessTokenService())->get()) {
                        $pinUser = with(new PinterestProvider())->getResourceOwner($pinAccessToken);
                        $logout = SoMe::instance()->getRoutes()->getPinterestLogoutRoute();
                        ?>
                        <p>Logged into Pinterest as <a href="<?php echo $pinUser->getUrl(); ?>"
                                                       target="_blank"><?php echo $pinUser->getFirstName(); ?></a></p>
                        <p><a href="<?php echo $logout . '?redirect_uri=' . $redirectUri; ?>" class="button button-secondary">Click here to log out of Pinterest</a></p>
                        <?php
                    } else {
                        $pinAuth = SoMe::instance()->getRoutes()->getPinterestAuthorizeRoute($uri = true);
                        ?>
                        <a href="<?php echo $pinAuth . '?redirect_uri=' . $redirectUri; ?>" class="button button-primary">Click here to connect with Pinterest</a>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
    
    public function register_settings()
    {
        if($this->languages_is_enabled()) {
            $this->enable_language_fields();
        }
        
        register_setting(
                self::SETTINGS_GROUP,
                self::SETTINGS_KEY,
                [$this, 'sanitize_input']
        );
        
        add_settings_section(
                self::SETTINGS_SECTION . '_IG',
                'Bonnier SoMe Settings<hr />Instagram Settings',
                [$this, 'print_ig_section_info'],
                self::SETTINGS_PAGE
        );
        
        foreach ($this->igSettingsFields as $sKey => $sField) {
            add_settings_field(
                $sKey, // ID
                $sField['name'], // Title
                [$this, $sKey], // Callback
                self::SETTINGS_PAGE, // Page
                self::SETTINGS_SECTION . '_IG' // Section
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
        
        foreach (array_merge($this->igSettingsFields, $this->ptSettingsFields) as $sKey => $sField) {
            if (isset($input[$sKey])) {
                if ($sField['type'] === 'checkbox') {
                    $sanitizedInput[$sKey] = absint($input[$sKey]);
                }
                if ($sField['type'] === 'text' || $sField['type'] === 'select') {
                    $sanitizedInput[$sKey] = sanitize_text_field($input[$sKey]);
                }
            }
        }
    
        return $sanitizedInput;
    }
    
    public function print_ig_section_info()
    {
        print 'Create an Instagram app and enter settings below:';
    }
    
    public function print_pt_section_info()
    {
        print 'Create a Pinterest app and enter settings below:';
    }
    
    public function __call($function, $arguments)
    {
        if(!isset($this->igSettingsFields[$function]) && !isset($this->ptSettingsFields[$function])) {
            return false;
        }
        
        $field = $this->igSettingsFields[$function] ?? $this->ptSettingsFields[$function];
        $this->create_settings_field($field, $function);
    }
    
    public function getInstagramClient($locale = null)
    {
        return [
            'client_id' => $this->get_setting_value('ig_client_id', $locale),
            'client_secret' => $this->get_setting_value('ig_client_secret', $locale),
            'redirect_uri' => $this->get_setting_value('ig_redirect_uri', $locale)
        ];
    }
    
    public function getInstagramClientID($locale = null)
    {
        return $this->get_setting_value('ig_client_id', $locale);
    }
    
    public function getInstagramClientSecret($locale = null)
    {
        return $this->get_setting_value('ig_client_secret', $locale);
    }
    
    public function getInstagramRedirectUri($locale = null)
    {
        return $this->get_setting_value('ig_redirect_uri', $locale);
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
            'redirect_uri' => $this->get_setting_value('pt_redirect_uri', $locale),
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
        if(is_null($locale)) {
            $locale = $this->get_current_locale();
        }
        if(!$this->settingsValues) {
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
        $languageEnabledFieldsIG = [];
        $languageEnabledFieldsPT = [];
        
        foreach ($this->get_languages() as $language) {
            foreach ($this->igSettingsFields as $fieldKey => $settingsField) {
                $localeFieldKey = $language->locale . '_' . $fieldKey;
                $languageEnabledFieldsIG[$localeFieldKey] = $settingsField;
                $languageEnabledFieldsIG[$localeFieldKey]['name'] .= ' ' . $language->locale;
                $languageEnabledFieldsIG[$localeFieldKey]['locale'] = $language->locale;
            }
            foreach ($this->ptSettingsFields as $fieldKey => $settingsField) {
                $localeFieldKey = $language->locale . '_' . $fieldKey;
                $languageEnabledFieldsPT[$localeFieldKey] = $settingsField;
                $languageEnabledFieldsPT[$localeFieldKey]['name'] .= ' ' . $language->locale;
                $languageEnabledFieldsPT[$localeFieldKey]['locale'] = $language->locale;
            }
        }
        
        $this->igSettingsFields = $languageEnabledFieldsIG;
        $this->ptSettingsFields = $languageEnabledFieldsPT;
        
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
            return PLL()->model->get_language(pll_current_language());
        }
        return null;
    }
    
    private function get_current_locale() {
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
            $fieldValue = isset($this->settingsValues[$fieldKey]) ? esc_attr($this->settingsValues[$fieldKey]) : ($field['value'] ?? '');
            $readonly = null;
            if($field['readonly'] ?? false) {
                $readonly = 'readonly="readonly"';
            }
            $fieldOutput = sprintf('<input type="text" name="%s" value="%s" %s class="regular-text" />', $fieldName, $fieldValue, $readonly);
        }
        if ($field['type'] === 'checkbox') {
            $checked = isset($this->settingsValues[$fieldKey]) && $this->settingsValues[$fieldKey] ? 'checked' : '';
            $fieldOutput = "<input type='hidden' value='0' name='$fieldName'>";
            $fieldOutput .= "<input type='checkbox' value='1' name='$fieldName' $checked />";
        }
        if ($field['type'] === 'select') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ? $this->settingsValues[$fieldKey] : ($field['value'] ?? '');
            $fieldOutput = "<select name='$fieldName'>";
            $options = $this->get_select_field_options($field);
            foreach ($options as $option) {
                $selected = ($option['system_key'] === $fieldValue) ? 'selected' : '';
                $fieldOutput .= "<option value='" . $option['system_key'] . "' $selected >" . $option['system_key'] . "</option>";
            }
            $fieldOutput .= "</select>";
        }
        
        if ($fieldOutput) {
            print $fieldOutput;
        }
    }
}
