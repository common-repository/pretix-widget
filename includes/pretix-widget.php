<?php
/**
 * Class Pretix_Widget
 *
 * This is the main class for the Pretix_Widget plugin, which handles various actions and settings.
 *
 * @package Pretix_Widget
 * @version 1.0.00
 */

namespace Pretix_Widget;

final class Pretix_Widget extends Base {
    public $settings;
    public $render;
    public $cache;
    public $debug = false;

    /**
     * Pretix_Widget constructor.
     *
     * Initializes the plugin by setting up settings, cache, render, and other actions.
     *
     * @since 1.0.00
     */
    public function __construct() {
        $this->settings = new Settings($this);
        $this->cache = new Cache($this);
        $this->languages = new Languages($this);
        $this->debug = defined('WP_DEBUG') && WP_DEBUG ? WP_DEBUG : false;
        $this->render = new Render($this); // render class must be called last

        // backend
        add_action('admin_menu', array($this, 'add_plugin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));

        // frontend
        add_shortcode('pretix_widget', array($this->render, 'shortcode_widget'));
        add_shortcode('pretix_widget_button', array($this->render, 'shortcode_button'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // misc
        add_action('plugins_loaded', 'pretix_widget_load_text_domain');

        $this->register_blocks();
    }

    /**
     * Register the plugin blocks for the Gutenberg editor.
     *
     * @since 1.0.00
     */
    public function register_blocks() {
        // Register the pretix-widget-widget block
        register_block_type(
            $this->get_path('gutenberg/src/blocks/pretix-widget/block.json'),
            array('render_callback' => array($this->render, 'block'))
        );
    }

    /**
     * Add the plugin menu to the WordPress admin menu.
     *
     * @since 1.0.00
     */
    public function add_plugin_menu() {
        $this->settings->add_plugin_menu();
    }

    /**
     * Enqueue the assets required for the block editor (Gutenberg).
     *
     * @since 1.0.00
     */
    public function enqueue_block_editor_assets() {
        // Enqueue the JavaScript build for the pretix-widget-button block
        wp_enqueue_script(
            'pretix-widget',
            $this->get_url('gutenberg/dist/pretix-widget.build.js'),
            array('wp-blocks', 'wp-element'),
            filemtime($this->get_path('gutenberg/dist/pretix-widget.build.js')),
            true
        );

        // inject the pretix-widget settings for the block
        wp_localize_script('pretix-widget', 'pretixWidgetDefaults', $this->settings->get_settings());
        // inject the pretix-widget language options for the block
        wp_localize_script('pretix-widget', 'pretixWidgetLanguages', $this->languages->get_list());

        wp_enqueue_style(
            'pretix-widget',
            $this->get_url('assets/css/editor.css'),
            array(),
            filemtime($this->get_path('assets/css/editor.css'))
        );
    }

    /**
     * Load assets only when the shortcode is used or the Gutenberg block is displayed.
     *
     * @since 1.0.00
     */
    public function enqueue_frontend_assets() {
        global $post;
        if (has_shortcode($post->post_content, 'pretix_widget') || has_block('pretix/widget')) {
            wp_enqueue_style(
                'pretix-widget-style',
                $this->get_url('assets/css/style.css'),
                [],
                filemtime($this->get_path('assets/css/style.css')),
                'all'
            );
        }
    }

    /**
     * Load WordPress backend assets for plugin pages.
     *
     * @since 1.0.00
     */
    public function enqueue_backend_assets() {
		$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

        if (str_contains($page, 'pretix_widget')) {
            if ($page === 'pretix_widget_settings') {
                $settings = wp_enqueue_code_editor(array('type' => 'text/html'));

                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery(function() { wp.codeEditor.initialize("pretix_widget_custom_css", %s ); });',
                        wp_json_encode($settings)
                    )
                );
            }
            wp_enqueue_style(
                'pretix-widget-backend-style',
                $this->get_url('assets/css/backend.css'),
                [],
                filemtime($this->get_path('assets/css/backend.css')),
                'all'
            );
            wp_enqueue_script(
                'pretix-widget-backend-script',
                $this->get_url('assets/js/backend.js'),
                [],
                filemtime($this->get_path('assets/js/backend.js')),
                true
            );
        }
    }

}
