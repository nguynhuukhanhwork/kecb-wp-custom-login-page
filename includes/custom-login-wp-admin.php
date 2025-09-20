<?php
class CustomLoginWpAdmin {
    private string $option_group = 'custom_login_group';
    private string $asset_url = WP_CUSTOM_LOGIN_PAGE_URL . 'assets/';
    private string $asset_dir_path = WP_CUSTOM_LOGIN_PAGE_DIR_PATH . 'assets/';

    private string $nonce = 'update_login_page_files';
    public function __construct() {
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_post_'.$this->nonce, [$this, 'saveFiles']);
        add_action('login_enqueue_scripts', [$this, 'loadAssets']);
        add_filter( 'login_headerurl', function() {
            return home_url();
        });
    }

    public function registerSettings(): void {
        $option_group = $this->option_group;

        // Logo
        register_setting($option_group, 'login_page_setting_logo_url');
        register_setting($option_group, 'login_page_setting_logo_click_url');

        // Background Image
        register_setting($option_group, 'login_page_setting_background_page_url');

        // Color
        register_setting($option_group, 'login_page_setting_text_color');
        register_setting($option_group, 'login_page_setting_link_color');
        register_setting($option_group, 'login_page_setting_button_login_color');
    }

    public function getSettingLogoUrl(): string {
        return esc_attr(get_option('login_page_setting_logo_url', ''));
    }


    // Logo Click URL
    public function getSettingLogoClickUrl(): string {
        return esc_url(get_option('login_page_setting_logo_click_url', ''));
    }

    // Background Image URL
    public function getSettingBackgroundPageUrl(): string {
        if (!empty(get_option('login_page_setting_background_page_url', ''))){
            return esc_url(get_option('login_page_setting_background_page_url', ''));
        }
        return $this->asset_url . 'bg.jpeg';
    }

    // Text Color
    public function getSettingTextColor(): string {
        return esc_attr(get_option('login_page_setting_text_color', ''));
    }

    // Link Login Color
    public function getSettingLinkColor(): string {
        return esc_attr(get_option('login_page_setting_link_color', ''));
    }

    // Button Login Color
    public function getSettingButtonLoginColor(): string {
        return esc_attr(get_option('login_page_setting_button_login_color', ''));
    }


    public function getContent(string $file_path): string|false {
        // Check file is exist
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        error_log('Error WP Custon Login System: File not found: ' . $file_path);
        return false;
    }

    public function getCSSContent(): string|false {
        $css_file_path = $this->asset_dir_path . 'style.css';
        return $this->getContent($css_file_path);
    }

    public function getJSContent(): string|false {
        $js_file_path = $this->asset_dir_path . 'script.js';
        return $this->getContent($js_file_path);
    }

    public function loadAssets(): void {
        $btn_color  = $this->getSettingButtonLoginColor();
        $bg_url     = $this->getSettingBackgroundPageUrl();
        $logo_url   = $this->getSettingLogoUrl();
        $text_color = $this->getSettingTextColor();
        $link_color = $this->getSettingLinkColor();
        $css = "
            body.login {
                background: url($bg_url) no-repeat center center fixed;
                background-size: cover;
                color: $text_color;
            }
            #login #wp-submit {
                background-color: $btn_color ;
                border-color: $btn_color;
            }
            #login #nav a, 
            .login #backtoblog a {
                color: $link_color;
            } 
            .login h1 a {
                background-image: url($logo_url);
            }
        ";
        wp_add_inline_style('login', $css);


        // Load Style/Script for Login Page (Check file dir path is exist. yes => load URL file)
        if (file_exists($this->asset_dir_path . 'style.css')) {
            wp_enqueue_style('custom-login-page-wp-style', $this->asset_url . 'style.css', [], filemtime($this->asset_url . 'style.css' ));
        }

        if (file_exists( $this->asset_dir_path . 'script.js')) {
            wp_enqueue_script('custom-login-page-wp-script', $this->asset_url . 'script.js', 'jquery', filemtime($this->asset_url . 'script.js') );
        }
    }

    public function registerAdminMenu(): void {
        add_options_page(
                'Cấu hình Login',
                'Login Page',
                'manage_options',
                'custom-login-wp-admin',
                [$this, 'renderAdminMenu']
        );
    }

    public function saveFiles(): void {
        check_admin_referer($this->nonce);

        if (!current_user_can('manage_options')) {
            wp_die('Không có quyền');
        }

        // Lưu CSS/JS vào file
        if (isset($_POST['login_page_setting_css'])) {
            file_put_contents($this->asset_dir_path . 'style.css', wp_unslash($_POST['login_page_setting_css']));
        }

        if (isset($_POST['login_page_setting_js'])) {
            file_put_contents($this->asset_dir_path . 'script.js', wp_unslash($_POST['login_page_setting_js']));
        }

        // Redirect lại trang settings
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
    }

    public function renderAdminMenu(): void {
        ?>
        <div class="wrap">
            <h2>Cấu hình Login Admin</h2>
            <form method="post" action="options.php">

                <?php settings_fields($this->option_group); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label>Login Logo URL</label></th>
                        <td>
                            <input type="url" class="regular-text" name="login_page_setting_logo_url"
                                   value="<?= $this->getSettingLogoUrl() ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Login Logo URL chuyển trang</label></th>
                        <td>
                            <input type="url" class="regular-text" name="login_page_setting_logo_click_url"
                                   value="<?= $this->getSettingLogoClickUrl() ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>Background Login URL</label></th>
                        <td>
                            <input type="url" class="regular-text" name="login_page_setting_background_page_url"
                                   value="<?= $this->getSettingBackgroundPageUrl(); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>Text color</label> <br></th>
                        <td>
                            <input type="color" class="regular-text " name="login_page_setting_text_color" style="width: 50px"
                                   value="<?= $this->getSettingTextColor() ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>Link color</label> <br></th>
                        <td>
                            <input type="color" class="regular-text " name="login_page_setting_link_color" style="width: 50px"
                                   value="<?= $this->getSettingLinkColor() ?>">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label>Button login color</label> <br></th>
                        <td>
                            <input type="color" class="regular-text " name="login_page_setting_button_login_color" style="width: 50px"
                                   value="<?= $this->getSettingButtonLoginColor() ?>">
                        </td>
                    </tr>
                </table>

                <?php submit_button("Cập nhật Settings"); ?>
            </form>

            <h2>CSS - JS cho Login Admin</h2>
            <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")) ?>">
                <input type="hidden" name="action" value="<?php echo $this->nonce;  ?>">
                <?php wp_nonce_field($this->nonce);?>

                <div class="form-field">
                    <label>CSS Style for Login Page</label>
                    <textarea class="large-text code" name="login_page_setting_css" rows="15"><?php echo esc_textarea($this->getCSSContent()) ?></textarea>
                </div>

                <div class="form-field">
                    <label class="regular-text">JS Script for Login Page</label>
                    <textarea class="large-text code" name="login_page_setting_js" rows="15"><?php echo esc_textarea($this->getJSContent()) ?></textarea>
                </div>

                <?php submit_button("Update CSS/JS");?>
            </form>
        </div>
        <?php
    }
}

new CustomLoginWpAdmin();
