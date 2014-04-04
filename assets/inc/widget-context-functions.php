<?php

/************************************************************************/
/* MODIFY SIDEBAR WIDGETS
/************************************************************************/

if ( is_admin() ) {
  add_action( 'sidebar_admin_setup', 'widget_context_expand_control' );
  add_filter( 'widget_update_callback', 'widget_context_ajax_update_callback', 10, 3); 
} else {
  add_filter( 'widget_display_callback', 'widget_context_filter_sidebars_widgets', 11,3);
  add_filter( 'sidebars_widgets', 'widget_context_maybe_unset_widget', 10 );
}

$home_page_slug = get_option('page_on_front') ? get_post( get_option('page_on_front') )->post_name : '';

function widget_context_maybe_unset_widget( $sidebars_widgets ) {

  global $ks_options, $home_page_slug;

  foreach( $sidebars_widgets as $widget_area => $widget_list ) {

    if ( $widget_area == 'wp_inactive_widgets' || empty( $widget_list ) ) 
      continue;
        
    foreach( $widget_list as $pos => $widget_id ) {

      $visible = false;

      if ( isset( $ks_options['widget_contexts'][$widget_id] ) ) {

        foreach ( $ks_options['widget_contexts'][$widget_id] as $value ) {

          $redirect_url_base = isset( $_SERVER['REDIRECT_URL'] ) ? $_SERVER['REDIRECT_URL'] : '';
          $redirect_url      = isset( $_SERVER['REDIRECT_URL'] ) && !empty( $_SERVER['REDIRECT_URL'] ) ? explode( '/', $_SERVER['REDIRECT_URL'] ) : '';
          $redirect_url      = is_array( $redirect_url ) ? $redirect_url[ count( $redirect_url ) - 2 ] : '';
          $redirect_uri_base = isset( $_SERVER['REDIRECT_URI'] ) ? $_SERVER['REDIRECT_URI'] : '';
          $redirect_uri      = isset( $_SERVER['REDIRECT_URI'] ) && !empty( $_SERVER['REDIRECT_URI'] ) ? explode( '/', $_SERVER['REDIRECT_URI'] ) : '';
          $redirect_uri      = is_array( $redirect_uri ) ? $redirect_uri[ count( $redirect_uri ) - 2 ] : '';

          _log('------- HOME PAGE SLUG --------');
          _log( $home_page_slug );
          _log('------- WIDGET NAME --------');
          _log( $value );
          _log('------- REDIRECT URL BASE --------');
          _log( $redirect_url_base );
          _log('------- REDIRECT URI BASE --------');
          _log( $redirect_uri_base );
          _log('------- REDIRECT URL --------');
          _log( $redirect_url );
          _log('------- REDIRECT URI --------');
          _log( $redirect_uri );
          _log('------- WIDGET CONTEXT VALUE --------');
          _log( $value );
          
          if ( $redirect_url == $value || $redirect_uri == $value || ( $redirect_uri_base == '' && $value == $home_page_slug ) || ( $redirect_url_base == '' && $value == $home_page_slug ) ) {

            $visible = true;

          } else if ( is_single() && $value == 'is_single' ) {

            $visible = true;

          } else if ( is_category() && $value == 'is_category' ) {

            $visible = true;

          } else if ( is_archive() && $value == 'is_archive' ) {

            $visible = true;

          } else if ( is_tax() && $value == 'is_taxonomy' ) {

            $visible = true;

          }

        }

        if ( !$visible ) {

          unset( $sidebars_widgets[$widget_area][$pos] );

        }

      }

    }

  }

  return $sidebars_widgets;

}

function widget_context_filter_sidebars_widgets( $instance, $widget, $args ) {

  global $post, $widget_context_options;

  $widget_context_value = isset( $widget_context_options['widget_contexts'][$widget->id] ) ? $widget_context_options['widget_contexts'][$widget->id] : '';

  if ( is_array( $widget_context_value ) && in_array( $post->post_name, $widget_context_value ) ) {
    return $instance;
  }

}

function widget_context_expand_control() {

  global $wp_registered_widgets, $wp_registered_widget_controls, $widget_context_options;

  foreach ( $wp_registered_widgets as $id => $widget ) {

    if ( !$wp_registered_widget_controls[$id] ) {
      wp_register_widget_control( $id,$widget['name'], 'widget_context_empty_control' );
    }

		$wp_registered_widget_controls[$id]['callback_wl_redirect'] = $wp_registered_widget_controls[$id]['callback'];
		$wp_registered_widget_controls[$id]['callback']             = 'widget_context_extra_control';
    array_push( $wp_registered_widget_controls[$id]['params'], $id );   

  }

}

// added to widget functionality in 'widget_context_expand_control' (above)
function widget_context_empty_control() {}

// added to widget functionality in 'widget_context_expand_control' (above)
function widget_context_extra_control() {   

  global $wp_registered_widget_controls, $widget_context_options;

	$output   = '';
	$params   = func_get_args();
	$id       = array_pop( $params );
	$callback = $wp_registered_widget_controls[$id]['callback_wl_redirect'];

	$page_types = array(
		'is_single'   => 'Posts',
		'is_category' => 'Categories',
		'is_archive'  => 'Archives',
		'is_taxonomy' => 'Taxonomies'
	);

  if ( is_callable( $callback ) ) {
    call_user_func_array( $callback, $params );
  }

  $value = !empty( $widget_context_options['widget_contexts'][$id] ) ? $widget_context_options['widget_contexts'][$id] : 'Nothing';

  // dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
  $number = $params[0]['number'];

  if ( $number == -1 ) {
    $number = "%i%"; 
    $value  = "";
  }

  $id_disp = $id;

  if ( isset( $number ) ) {
    $id_disp = $wp_registered_widget_controls[$id]['id_base'] . '-' . $number;
  }

  $pages_args = array(
    'posts_per_page' => -1,
    'post_type'      => array('page'),
    'orderby'        => 'title',
    'order'          => 'ASC'
  );

  $pages = get_posts( $pages_args );

  if ( $pages ) {

    foreach ( $pages as $page ) {

      $checked = isset( $widget_context_options['widget_contexts'][$id_disp] ) && in_array( $page->post_name, $widget_context_options['widget_contexts'][$id_disp] ) ? 'checked' : '';
      $output .= "<div class='widget-context-input'><input type='checkbox' value='$page->post_name' name='" . $id_disp . "-context[]' id='" .$id_disp . "-context' $checked /> <label for='" .$id_disp . "-context'>$page->post_title</label></div>";

    }

  }

  $output .= "<h4>Show On Certain Types Of Pages</h4>";

	foreach ( $page_types as $key => $page_type ) {

    $checked = isset( $widget_context_options['widget_contexts'][$id_disp] ) && in_array( $key, $widget_context_options['widget_contexts'][$id_disp] ) ? 'checked' : '';
    $output .= "<div class='widget-context-input'><input type='checkbox' value='$key' name='" . $id_disp . "-context[]' id='" .$id_disp . "-context' $checked /> <label for='" .$id_disp . "-context'>$page_type</label></div>";

  }

  echo '<div class="widget-context"><div class="widget-context-inside"><p>Show Widget On Specified Page(s) Only<a href="#" class="expand-widget-context pull-right" data-show="widget-context-inputs">Show</a></p><div class="widget-context-inputs group">' . $output . '</div></div></div>';

}

function widget_context_ajax_update_callback( $instance, $new_instance, $this_widget ) {

  global $widget_context_options;

  $widget_id = $_POST['id_base'] . '-' . $_POST['widget_number'];

  if ( isset( $_POST[ $widget_id . '-context'] ) ) {
      $widget_context_options['widget_contexts'][$widget_id] = is_array( $_POST[$widget_id.'-context'] ) ? $_POST[$widget_id.'-context'] : $_POST[$widget_id.'-context'];
      update_option('widget_context_options', $widget_context_options);
  }

  return $instance;

}