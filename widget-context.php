<?php

/*
Plugin Name: Widget Context
Plugin URI: https://github.com/joshuaadrian/widget-context
Description: Assign sidebar widgets to specific pages or page types.
Author: Joshua Adrian
Version: 0.1.0
Author URI: https://github.com/joshuaadrian/
*/

/************************************************************************/
/* ERROR LOGGING
/************************************************************************/

/**
 *  Simple logging function that outputs to debug.log if enabled
 *  _log('Testing the error message logging');
 *	_log(array('it' => 'works'));
 */

if ( !function_exists('_log') ) {
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}

/************************************************************************/
/* DEFINE PLUGIN ID AND NICK
/************************************************************************/

// DEFINE PLUGIN BASE
define( 'WIDGET_CONTEXT_PATH', plugin_dir_path(__FILE__) );
// DEFINE PLUGIN URL
define( 'WIDGET_CONTEXT_URL_PATH', plugins_url() . '/widget-context' );
// DEFINE PLUGIN ID
define( 'WIDGET_CONTEXT_PLUGINOPTIONS_ID', 'widget-context' );
// DEFINE PLUGIN NICK
define( 'WIDGET_CONTEXT_PLUGINOPTIONS_NICK', 'Widget Context' );
// DEFINE PLUGIN NICK
register_activation_hook( __FILE__, 'widget_context_add_defaults' );
// DEFINE PLUGIN NICK
register_uninstall_hook( __FILE__, 'widget_context_delete_plugin_options' );
// ADD LINK TO ADMIN
add_action( 'admin_init', 'widget_context_init' );
// ADD LINK TO ADMIN
add_action( 'admin_menu', 'widget_context_add_options_page' );
// ADD LINK TO ADMIN
add_filter( 'plugin_action_links', 'widget_context_plugin_action_links', 10, 2 );
// GET OPTION
$widget_context_options = get_option( 'widget_context_options' );

if ( is_admin() ) {

	// LOAD PLUGINS FUNCTION TO RETRIEVE PLUGIN DATA
	if ( !function_exists( 'get_plugins' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	// RETRIEVE PLUGIN DATA
  $widget_context_data = get_plugin_data( WIDGET_CONTEXT_PATH . plugin_basename( dirname( __FILE__ ) ) . '.php', false, false );

  // LOAD MARKDOWN CLASS
	if ( !function_exists('markdown') ) {
		require_once WIDGET_CONTEXT_PATH . 'inc/libs/php-markdown/markdown.php';
	}

}

/************************************************************************/
/* Delete options table entries ONLY when plugin deactivated AND deleted
/************************************************************************/

function widget_context_delete_plugin_options() {
	delete_option('widget_context_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'posk_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------
//delete_option( 'widget_context_options' ); widget_context_add_defaults();
// Define default option settings
function widget_context_add_defaults() {

  if ( !$widget_context_options || !is_array( $widget_context_options ) ) {

		delete_option( 'widget_context_options' );

		$widget_context_defaults = array(
			'include_pages'      => '1',
			'include_posts'      => '0',
			'include_page_types' => '0',
			'widget_contexts'    => array()
		);

		update_option( 'widget_context_options', $widget_context_defaults );

	}

}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'posk_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function widget_context_init() {
	register_setting( 'widget_context_plugin_options', 'widget_context_options', 'widget_context_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'posk_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function widget_context_add_options_page() {
	add_options_page('Widget Context', WIDGET_CONTEXT_PLUGINOPTIONS_NICK, 'manage_options', WIDGET_CONTEXT_PLUGINOPTIONS_ID, 'widget_context_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------
// Render the Plugin options form
function widget_context_render_form() { 

	global $widget_context_options, $widget_context_data;
	
	?>

	<div id="widget-context-options" class="wrap">  

		<?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings_options'; ?>
        
		<h2 class="nav-tab-wrapper">  
		  	<a href="?page=widget-context&tab=settings_options" class="nav-tab <?php echo $active_tab == 'settings_options' ? 'nav-tab-active' : ''; ?>">Settings</a>
		  	<a href="?page=widget-context&tab=wiki_options" class="nav-tab <?php echo $active_tab == 'wiki_options' ? 'nav-tab-active' : ''; ?>">Wiki</a>  
		</h2>

		<?php if ( $active_tab == 'settings_options' ) : ?>

    <div class="widget-context-options-section">

	    <form action="options.php" method="post" id="<?php echo WIDGET_CONTEXT_PLUGINOPTIONS_ID; ?>-options-form" name="<?php echo WIDGET_CONTEXT_PLUGINOPTIONS_ID; ?>-options-form">

	    	<?php settings_fields('widget_context_plugin_options'); ?>

    		<h1>Settings</h1>

    		<table class="form-table">

		    	<tr>

						<th>

			    		<label for="include_pages">Include Pages</label>

			    	</th>

			    	<td>

			    		<input type="checkbox" value="1" id="include_pages" name="widget_context_options[include_pages]" <?php checked( $widget_context_options['include_pages'], 1 ); ?> />

						</td>

					</tr>

					<tr>

						<th>

			    		<label for="include_posts">Include Posts</label>

			    	</th>

			    	<td>

			    		<input type="checkbox" value="1" id="include_posts" name="widget_context_options[include_posts]" <?php checked( $widget_context_options['include_posts'], 1 ); ?> />

						</td>

					</tr>

					<tr>

						<th>

			    		<label for="include_page_types">Include Page Types</label>

			    	</th>

			    	<td>

			    		<input type="checkbox" value="1" id="include_page_types" name="widget_context_options[include_page_types]" <?php checked( $widget_context_options['include_page_types'], 1 ); ?> />

						</td>

					</tr>

				</table>

    		<div class="widget-context-form-action">
          <p><input name="Submit" type="submit" value="<?php esc_attr_e('Update Settings'); ?>" class="button-primary" /></p>
        </div>

			</form>

		</div>

    <?php endif; ?>

		<?php if ( $active_tab == 'wiki_options' ) : ?>

		<div class="widget-context-options-section">

	  	<div class="widget-context-copy">

	   <?php

    		$text = file_get_contents( WIDGET_CONTEXT_PATH . 'README.md' );

    		if ( $text ) {
					$html = Markdown($text);
					echo $html;
				} else {
					echo '<h1>Issue retrieving plugin information</h1>';
				}

			?>

			</div>		

		</div>

		<?php endif; ?>

		<div class="credits">
			<p><?php echo $widget_context_data['Name']; ?> Plugin | Version <?php echo $widget_context_data['Version']; ?> | <a href="<?php echo $widget_context_data['PluginURI']; ?>">Plugin Website</a> | Author <a href="<?php echo $widget_context_data['AuthorURI']; ?>"><?php echo $widget_context_data['Author']; ?></a> | <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/" style="position:relative; top:3px; margin-left:3px"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png" /></a><a href="http://joshuaadrian.com" target="_blank" class="alignright"><img src="<?php echo plugins_url( 'images/ja-logo.png' , __FILE__ ); ?>" alt="Joshua Adrian" /></a></p>
		</div>

	</div>

<?php

}

/************************************************************************/
/* Sanitize and validate input. Accepts an array, return a sanitized array.
/************************************************************************/

function widget_context_validate_options( $input ) {

	if ( isset( $input['include_pages'] ) ) {
		$input['include_pages'] = wp_filter_nohtml_kses( $input['include_pages'] );
	}

	if ( isset( $input['include_posts'] ) ) {
		$input['include_posts'] = wp_filter_nohtml_kses( $input['include_posts'] );
	}

	if ( isset( $input['include_page_types'] ) ) {
		$input['include_page_types'] = wp_filter_nohtml_kses( $input['include_page_types'] );
	}
	
	return $input;

}

/************************************************************************/
/* Display a Settings link on the main Plugins page
/************************************************************************/

function widget_context_plugin_action_links( $links, $file ) {

	$tmp_id = WIDGET_CONTEXT_PLUGINOPTIONS_ID . '/widget-context.php';

	if ( $file == $tmp_id ) {

		$widget_context_links = '<a href="'.get_admin_url().'options-general.php?page=' . WIDGET_CONTEXT_PLUGINOPTIONS_ID . '">' . __('Settings') . '</a>';
		array_unshift( $links, $widget_context_links );

	}

	return $links;

}

/************************************************************************/
/* IMPORT CSS AND JAVASCRIPT STYLES
/************************************************************************/

function widget_context_enqueue() {
  wp_register_style( 'widget_context_css', plugins_url('/assets/css/widget-context.css', __FILE__), false, '1.0.0' );
  wp_enqueue_style( 'widget_context_css' );
  wp_enqueue_script( 'widget_context_script', plugins_url('/assets/js/widget-context.min.js', __FILE__), array('jquery') );
}

add_action('admin_enqueue_scripts', 'widget_context_enqueue');

/************************************************************************/
/* INCLUDES
/************************************************************************/

require WIDGET_CONTEXT_PATH . 'assets/inc/widget-context-functions.php';

?>