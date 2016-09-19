<?php
/**
 * @package   WMS_Dining_Menu
 * @author    Rus Miller / Williams Web Team <webteam@williams.edu>
 * @license   GPL-2.0+
 * @link      http://communications.williams.edu/web-development/
 * @copyright 2014 Williams College
 * @uses      json-api plugin (controllers/dining.php)
 *
 * @wordpress-plugin
 * Plugin Name:       Williams Dining Menu Widget
 * Description:       Displays dining menu information by dining hall. Additionally provides a RESTful API.
 * Version:           0.0.1
 * Author:            Williams Web Team <webteam@williams.edu>
 * Author URI:        http://communications.williams.edu/web-development/
 * Text Domain:       wms-dining-menu-widget
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// TODO: change 'WMS_Dining_Menu' to the name of your plugin
class WMS_Dining_Menu extends WP_Widget {

    /**
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    0.0.1
     *
     * @var      string
     */
    private     $debug          = true;
    protected   $widget_slug    = 'wms-dining-menu-widget';
    protected   $widget_name    = 'Williams Dining Menu';
    protected   $widget_id      = 'wms_dining_menu_widget';
    protected   $plugin_url     = '';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {
        $this->plugin_url = plugins_url( '', __FILE__ );
		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_widget_slug(),
			__( $this->widget_name, $this->get_widget_slug() ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Displays dining menu information by dining hall. Additionally provides a RESTful API.', $this->get_widget_slug() )
			)
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	} // end constructor

    /**
     * Return the widget slug.
     *
     * @since    0.0.1
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

    /**
     * Return the widget ID.
     *
     * @since    0.0.1
     *
     * @return    Plugin ID variable.
     */
    public function get_widget_id() {
        return $this->widget_id;
    }

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {
		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];
		
		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		// TODO: Here is where you manipulate your widget's values based on their input fields
		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'views/wmsdining-widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget
	
	
	public function flush_widget_cache() {
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// TODO: Here is where you update your widget's old values with the new, incoming values

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// TODO: Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		// TODO: Store the values of the widget in their own variable

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'views/admin.php' );

	} // end form
	
	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {

		// TODO be sure to change 'wms-dining-menu-widget' to the name of *your* plugin
		load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

	} // end widget_textdomain

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// TODO define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here
	} // end deactivate

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array('jquery') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

        wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {
		// Registar Angular.js
		wp_enqueue_script( 'angular', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js', array(), null, true );
//		wp_enqueue_script( 'angular-animate', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular-animate.min.js', array(), null, true );
		wp_enqueue_script( 'angular-resource', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular-resource.min.js', array(), null, true );
		wp_enqueue_script( 'angular-route', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular-route.min.js', array(), null, true );
		wp_enqueue_script( 'underscore', 'http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.7.0/underscore-min.js', array(), null, true );
        wp_enqueue_script( $this->get_widget_slug().'-app', plugins_url( 'js/app/wmsdining-app.min.js', __FILE__ ), array('angular', 'angular-resource', 'angular-route', 'underscore'), null, true );
        wp_enqueue_script( $this->get_widget_slug().'-controllers', plugins_url( 'js/app/controllers/wmsdining-controllers.min.js', __FILE__ ), array(), null, true );
        wp_enqueue_script( $this->get_widget_slug().'-services', plugins_url( 'js/app/services/wmsdining-services.min.js', __FILE__ ), array(), null, true );



		// we need to create a JavaScript variable to store our API endpoint...
		//wp_localize_script( $this->get_widget_slug().'-app', 'WMS_DINING_MENU_OBJECT', array( 'id' => $this->id) );

	} // end register_widget_scripts
} // end class

// TODO: Remember to change 'WMS_Dining_Menu' to match the class name definition
add_action( 'widgets_init', create_function( '', 'register_widget("WMS_Dining_Menu");' ) );

if(!class_exists('customException')){
    class customException extends Exception {
        public function errorMessage()
        {
            //error message
            $errorMsg = '<p><strong>Error on line '.$this->getLine().' in '.$this->getFile().':</strong> <code>'.$this->getMessage().'</code></p>';
            return $errorMsg;
        }
    }
}