<?php

/**
* 
*/
class cloudpress {
	
	function __construct(){
		$this->hooks();
		$this->theme_support();
		$this->register_nav_menues();
	}

	function hooks(){

		add_action('init', array($this, 'head_cleanup'), 10, 1);
	    add_filter('the_generator', array($this, '__return_empty_string'), 10, 1); // remove WP version from RSS
	    add_filter('wp_head', array($this, 'remove_wp_widget_recent_comments_style'), 1);
	    add_action('wp_head', array('remove_recent_comments_style'), 1);
	    add_filter('gallery_style', array($this, 'joints_gallery_style'), 10);
	    add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 999);
	    add_action('widgets_init', array($this, 'register_sidebars'), 10);
	    add_filter('get_search_form', array($this, 'search_form'), 10);
	    add_filter('the_content', array($this, 'filter_ptags_on_images'), 10);

	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function theme_support(){

		set_post_thumbnail_size(125, 125, true);

		add_theme_support('post-thumbnails');
		add_theme_support('automatic-feed-links');
		add_theme_support('post-formats',
			array(
				'aside',           
				'gallery',         
				'link',            
				'image',           
				'quote',           
				'status',          
				'video',           
				'audio',           
				'chat'             
				)
			);
		add_theme_support('menus');
		add_theme_support('html5', 
			array( 
				'comment-list', 
				'comment-form', 
				'search-form', 
				) 
			);
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function register_nav_menues(){
		register_nav_menus(
			array(
				'top-nav' => __( 'The Top Menu' ),   // main nav in header
				'main-nav' => __( 'The Main Menu' ),   // main nav in header
				'footer-links' => __( 'Footer Links' ) // secondary nav in footer
				)
			);

	}

	/**
	 * Removes clutter in the <head> tag
	 *
	 * @return void
	 **/
	function head_cleanup() {
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);
		remove_action('wp_head', 'start_post_rel_link', 10, 0);
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
		remove_action('wp_head', 'wp_generator');
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function remove_wp_widget_recent_comments_style(){
		if(has_filter('wp_head', 'wp_widget_recent_comments_style')){
			remove_filter('wp_head', 'wp_widget_recent_comments_style');
		}
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function remove_recent_comments_style(){
		global $wp_widget_factory;
		if(isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
			remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
		}
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function comments_style(){
		return preg_replace("<style type='text/css'>(.*?)</style>s", '', $css);
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function enqueue_assets(){
		global $wp_styles; // call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way
		if (!is_admin()) {
			$theme_version = wp_get_theme()->Version;

			// removes WP version of jQuery
			wp_deregister_script('jquery');

			// loads jQuery 2.1.0
			wp_enqueue_script( 'jquery', get_template_directory_uri() . '/bower_components/foundation/js/vendor/jquery.js', array(), '2.1.0', false );

			 // modernizr (without media query polyfill)
			wp_enqueue_script( 'modernizr', get_template_directory_uri() . '/bower_components/foundation/js/vendor/modernizr.js', array(), '2.5.3', false );

			 // adding Foundation scripts file in the footer
			wp_enqueue_script( 'foundation-js', get_template_directory_uri() . '/bower_components/foundation/js/foundation.min.js', array( 'jquery' ), $theme_version, true );

			 // register main stylesheet
			wp_enqueue_style( 'joints-stylesheet', get_template_directory_uri() . '/library/css/style.css', array(), $theme_version, 'all' );

			 // register foundation icons
			wp_enqueue_style( 'foundation-icons', get_template_directory_uri() . '/library/css/icons/foundation-icons.css', array(), $theme_version, 'all' );

			 // comment reply script for threaded comments
			if ( is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
				wp_enqueue_script( 'comment-reply' );
			}

			 //adding scripts file in the footer
			wp_enqueue_script( 'joints-js', get_template_directory_uri() . '/library/js/scripts.js', array( 'jquery' ), $theme_version, true );

		}
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function register_sidebars(){

		register_sidebar(array(
			'id' => 'sidebar1',
			'name' => __('Sidebar 1', 'jointstheme'),
			'description' => __('The first (primary) sidebar.', 'jointstheme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="widgettitle">',
			'after_title' => '</h4>',
			));

		register_sidebar(array(
			'id' => 'offcanvas',
			'name' => __('Offcanvas', 'jointstheme'),
			'description' => __('The offcanvas sidebar.', 'jointstheme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="widgettitle">',
			'after_title' => '</h4>',
			));
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function search_form(){

		$form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
		<label class="screen-reader-text" for="s">' . __('Search for:', 'jointstheme') . '</label>
		<input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="'.esc_attr__('Search the Site...','jointstheme').'" />
		<input type="submit" id="searchsubmit" class="button" value="'. esc_attr__('Search') .'" />
		</form>';

		return $form;

	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function filter_ptags_on_images($content){
		return preg_replace('~<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>~iU', '$1$2$3', $content);
	}

}


/* Instance function */
function cloudpress(){
	if(!$GLOBALS['__cloudpress'])
		$GLOBALS['__cloudpress'] = new cloudpress();
	return $GLOBALS['__cloudpress'];
}

cloudpress();


?>