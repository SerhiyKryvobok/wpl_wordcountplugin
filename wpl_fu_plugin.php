<?php

/*
    Plugin Name: WPL First Plugin 
    Description: First steps in plugin development
    Version: 1.0
    Author: SK
    Author URI: https://github.com/SerhiyKryvobok
    License:      GNU General Public License v3.0
    License URI:  https://www.gnu.org/licenses/gpl-3.0.html
    Text Domain: wplwcp_domain
    Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

class WPLWordCountAndTimePlugin {
    function __construct() {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
    }

    function languages() {
        load_plugin_textdomain('wplwcp_domain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content) {
        if (is_main_query() && is_single() && (get_option('wplwcp_wordcount', '1') || get_option('wplwcp_charcount', '1') || get_option('wplwcp_readtime', '1'))) {
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content) {
        $html = '<h4>' . esc_html(get_option('wplwcp_headline', 'Post Statistics')) . '</h4><p>';
        if (get_option('wplwcp_wordcount', '1') || get_option('wplwcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('wplwcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wplwcp_domain') . ' ' . $wordCount . ' ' . esc_html__('words', 'wplwcp_domain') . '.<br>';
        }

        if (get_option('wplwcp_charcount', '1')) {
            $html .= esc_html__('This post has', 'wplwcp_domain') . ' ' . strlen(strip_tags($content)) . ' ' . esc_html__('characters', 'wplwcp_domain') . '.<br>';
        }

        if (get_option('wplwcp_readtime', '1')) {
            $html .= esc_html__('This post will take about', 'wplwcp_domain') . ' ' . round($wordCount/225) . ' ' . esc_html__('minute(s) to read', 'wplwcp_domain') . '.<br>';
        }

        $htmp .= '</p>';

        if (get_option('wplwcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function settings() {
        add_settings_section( 'wplwcp_section_1', null, null, 'wpl-word-count-settings-page');

        add_settings_field('wplwcp_location', __('Display Location', 'wplwcp_domain'), array($this, 'locationHTML'), 'wpl-word-count-settings-page', 'wplwcp_section_1');
        register_setting('wpl_wordcountplugin', 'wplwcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        add_settings_field('wplwcp_headline', __('Headline Text', 'wplwcp_domain'), array($this, 'headlineHTML'), 'wpl-word-count-settings-page', 'wplwcp_section_1');
        register_setting('wpl_wordcountplugin', 'wplwcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post statistics'));
    
        add_settings_field('wplwcp_wordcount', __('Word Count', 'wplwcp_domain'), array($this, 'chckboxHTML'), 'wpl-word-count-settings-page', 'wplwcp_section_1', array('theName' => 'wplwcp_wordcount'));
        register_setting('wpl_wordcountplugin', 'wplwcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wplwcp_charcount', __('Character Count', 'wplwcp_domain'), array($this, 'chckboxHTML'), 'wpl-word-count-settings-page', 'wplwcp_section_1', array('theName' => 'wplwcp_charcount'));
        register_setting('wpl_wordcountplugin', 'wplwcp_charcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
        
        add_settings_field('wplwcp_readtime', __('Read Time', 'wplwcp_domain'), array($this, 'chckboxHTML'), 'wpl-word-count-settings-page', 'wplwcp_section_1', array('theName' => 'wplwcp_readtime'));
        register_setting('wpl_wordcountplugin', 'wplwcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    function chckboxHTML($args) { ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option( $args['theName'] ), '1') ?>>
    <?php }

    function headlineHTML() { ?>
        <input type="text" name="wplwcp_headline" value="<?php echo esc_attr(get_option('wplwcp_headline')) ?>">
    <?php }

    function sanitizeLocation($input) {
        if ($input != '0' && $input != '1') {
            add_settings_error('wplwcp_location', 'wplwcp_location_error', 'Display location must be either Beginning or End!');
            return get_option('wplwcp_location');
        }
        return $input;
    }

    function locationHTML() { ?>
        <select name="wplwcp_location">
            <option value="0" <?php selected(get_option('wplwcp_location'), '0') ?>><?php echo __('Beginning of post', 'wplwcp_domain') ?></option>
            <option value="1" <?php selected(get_option('wplwcp_location'), '1') ?>><?php echo __('End of post', 'wplwcp_domain') ?></option>
        </select>
    <?php }

    function adminPage() {
        add_options_page('Word Count Settings', __('Word Count', 'wplwcp_domain'), 'manage_options', 'wpl-word-count-settings-page', array($this, 'settingsHTML'));
    }
    
    function settingsHTML() { ?>
        <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('wpl_wordcountplugin');
                    do_settings_sections('wpl-word-count-settings-page');
                    submit_button();
                ?>
            </form>
        </div>
    <?php }
}

$wplWordCountAndTimePlugin = new WPLWordCountAndTimePlugin();