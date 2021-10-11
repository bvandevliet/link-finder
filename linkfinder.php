<?php

/**
 * Plugin Name:       Link Finder
 * Version:           2021.10.12
 * Requires at least: 4.6
 * Requires PHP:      7.2
 * Description:       Find and repair broken links throughout your website.
 * Author:            Bob Vandevliet
 * Author URI:        https://www.bvandevliet.nl/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl.html
 * Text Domain:       linkfinder
 * Domain Path:       /languages
 *
 * @ignore local storage to skip re-validation of ignored links on form submit
 * @ignore add link text table column
 * @ignore add option to prevent search engines from following a link
 * @ignore add option to display a broken link differently (e.g. line-through)
 * @ignore add option to unlink, remove the link but keep the link text
 * @ignore does the plugin also detects missing images ??
 *
 * @ignore keywords: detect/find and repair/fix missing images and broken or redirected links
 */

defined( 'ABSPATH' ) || exit;


/**
 * Include plugin resources.
 *
 * @since 2020.06.11
 */
require dirname( __FILE__ ) . '/inc/functions.php';
require dirname( __FILE__ ) . '/inc/class-linkfinder-manage-links.php';
require dirname( __FILE__ ) . '/admin/settings.php';


/**
 * Force the languages to load.
 *
 * @since 2020.06.11
 *
 * @ignore STILL DOESN'T WORK !!
 */
add_action(
  'init',
  function ()
  {
    load_plugin_textdomain( 'linkfinder', false, dirname( __FILE__ ) . '/languages' );
  }
);


/**
 * Add the plugin menu's and pages (admin_menu).
 *
 * @since 2020.06.11
 *
 * @link https://developer.wordpress.org/plugins/administration-menus/
 */
add_action(
  'admin_menu',
  function ()
  {
    $hookname = add_management_page(
      __( 'Link Finder', 'linkfinder' ), // $page_title
      __( 'Link Finder', 'linkfinder' ), // $menu_title
      'edit_pages', // $capability
      'linkfinder', // $menu_slug
      function ()
      {
        /**
         * Print submit messages.
         */
        settings_errors( 'linkfinder' );

        ?>
        <div class="wrap linkfinder-page">
          <h1>Link Finder</h1>
          <form action="<?php echo htmlspecialchars( $_SERVER['REQUEST_URI'] ); ?>" method="post">
            <?php
            /**
             * Output security fields for the registered setting.
             */
            settings_fields( 'linkfinder' ); // $option_group
            /**
             * Output setting sections and their fields.
             */
            do_settings_sections( 'linkfinder' ); // $page

            /**
             * Filter the submit button.
             *
             * @since 2020.06.11
             */
            echo apply_filters( 'linkfinder_submit_button', get_submit_button( __( 'Save changes', 'linkfinder' ) ) );
            ?>
          </form>
        </div>
        <?php
      } // $function
    );

    /**
     * After submit callback.
     *
     * @since 2020.06.11
     */
    add_action(
      'load-' . $hookname,
      function ()
      {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
          return;
        }

        /**
         * Check if request is valid and permitted.
         */
        if (
          empty( $_POST['_wpnonce'] ) ||
          empty( $_POST['action'] )
          || wp_verify_nonce(
            $_POST['_wpnonce'], // $nonce
            'linkfinder-options' // $action, refer to https://developer.wordpress.org/reference/functions/settings_fields/
          ) === false
        ) {
          add_settings_error(
            'linkfinder',
            'linkfinder_invalidpost',
            __( 'Something went wrong, please try again!', 'linkfinder' )
          );
          return;
        }

        linkfinder_after_page_submit_cb();
      }
    );
  }
);


/**
 * Enqueue admin styles and scripts.
 *
 * @since 2020.06.11
 */
add_action(
  'admin_enqueue_scripts',
  function ()
  {
    if (
      isset( $_GET['page'] ) &&
      $_GET['page'] === 'linkfinder'
    ) {
      wp_enqueue_style(
        'linkfinder_styles', // $handle
        plugin_dir_url( __FILE__ ) . 'assets/linkfinder-styles.css', // $src
        array(), // $deps
        '2021.10.12' // $ver
      );
      wp_enqueue_script(
        'linkfinder_scripts', // $handle
        plugin_dir_url( __FILE__ ) . 'assets/linkfinder-scripts.js', // $src
        array( 'jquery' ), // $deps
        '2021.10.12', // $ver
        false // $in_footer
      );
    }
  }
);
