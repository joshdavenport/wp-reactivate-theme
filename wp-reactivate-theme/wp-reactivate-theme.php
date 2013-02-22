<?php
/*
Plugin Name: Reactivate Theme
Plugin URI: http://www.joshdavenport.co.uk/blog/wp-reactivate-theme/
Description: This plugin provides a utility to reactivate the currently active WordPress theme for development purposes.
Version: 1.0
Author: Josh Davenport
Author URI: http://www.joshdavenport.co.uk/
License: GPL v3

WordPress Reactivate Theme Plugin
Copyright (C) 2013, Josh Davenport - josh@joshdavenport.co.uk

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class WP_ReactivateTheme {

    var $current_theme;

    const page_slug = 'reactivate-theme';

    function __construct() {
        // add menu item
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        // enqueue assets
        $this->admin_assets();

        // stop anything further from happening unless we're on our tools_page
        if ( !isset( $_GET['page'] ) || self::page_slug != $_GET['page'] )
            return;

        // get variables together that will be needed later on
        $this->current_theme_data = $this->get_current_theme_data();

        // reactivate the theme, if the reactivation hasn't already happened
        if(!$this->is_reactivated()) {
            $this->reactivate_current_theme();
        }
    }

    function admin_menu() {
        if (function_exists('add_management_page')) {
            add_management_page('Reactivate Theme', 'Reactivate Theme', 'switch_themes', self::page_slug, array($this, 'reactivate_page'));
        }
    }

    function admin_assets() {
        $styles_src = plugins_url('css/styles.css', __FILE__);
        wp_enqueue_style( 'reactivatetheme-admin-styles', $styles_src );
    }

    function reactivate_page() {
        ?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"><br></div>
        <h2>Reactivate Theme</h2>
        <div id="rt-container">
            <div id="rt-main">
                <?php if($this->get_current_template() && $this->get_current_stylesheet()): ?>
                <p class="hidden"><?php echo $this->get_current_template() ?></p>
                <p class="hidden"><?php echo $this->get_current_stylesheet() ?></p>
                <?php if($this->is_reactivated()): ?>
                    <p><?php _e('Success! Theme reactivated.', 'reactivate_theme') ?></p>
                    <?php endif ?>
                <?php else: ?>
                <p><?php _e('There was a problem :(', 'reactivate_theme') ?></p>
                <p><?php _e("I couldn't seem to find the current theme you've got activated. Here's what I did find:", 'reactivate_theme') ?></p>
                <p><?php _e('Template:', 'reactivate_theme') ?> <?php var_dump($this->get_current_template()) ?></p>
                <p><?php _e('Template:', 'reactivate_theme') ?> <?php var_dump($this->get_current_stylesheet()) ?></p>
                <?php endif;?>
            </div>
            <div id="rt-sidebar">
                <div class="author">
                    <img src="http://www.gravatar.com/avatar/c55bd9279032b4dfd1057746c55ba129?s=128&amp;d" width="64" height="64">
                    <div class="desc">
                        <h3>Created by</h3>
                        <h2>Josh Davenport</h2>
                        <p>
                            <a href="http://profiles.wordpress.org/josh-davenport/">Profile</a>
                            &nbsp;&nbsp;
                            <a href="http://www.joshdavenport.co.uk/">Website</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }

    function get_current_theme_data() {
        $theme = wp_get_theme(get_stylesheet());

        if (!$theme->exists() || !$theme->is_allowed()) {
            return false;
        }

        return array(
            'template' => $theme->get_template(),
            'stylesheet' => $theme->get_stylesheet(),
        );
    }

    function get_current_template() {
        return $this->current_theme_data['template'];
    }

    function get_current_stylesheet() {
        return $this->current_theme_data['stylesheet'];
    }

    function reactivate_current_theme() {
        // actually activate the current theme
        if(get_bloginfo('version') > '3.5') {
            switch_theme($this->get_current_stylesheet());
        } else {
            switch_theme($this->get_current_template(), $this->get_current_stylesheet());
        }
        // redirect to our options page, with a flag telling the logic that the switch has already happened
        wp_redirect(admin_url('tools.php?page=' . self::page_slug . '&success=true'));
    }

    function is_reactivated() {
        return isset($_GET['success']);
    }

}

function reactivatetheme_init() {
    if (!is_admin()) {
        return;
    }

    global $reactivate_theme;
    $reactivate_theme = new WP_ReactivateTheme();
}

add_action('init', 'reactivatetheme_init');
