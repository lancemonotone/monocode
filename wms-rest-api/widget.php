<?php
/**
 * @package Plugin_Template
 * @uses Advanced Custom Fields plugin
 */

add_action('widgets_init', create_function('', 'return register_widget("Widget_Template");'));

class Widget_Template extends WP_Widget {    
    var $_parent    = 'Plugin Template';
    var $label      = 'Plugin Template';
    var $widgetname = 'Widget_Template';
    var $namespace  = 'widget_template';
	var $classname  = 'widget-template';
	var $version    = "1.0.0";
	    
	function __construct(){
		$description      = '';
        $label            =  WMS_WIDGET_PREFIX . $this->widgetname;
        $widget_ops  = array('classname' => $this->classname. ' cf', 'description' => __($description) );
        $control_ops = array('width' => WMS_WIDGET_WIDTH, 'height' => WMS_WIDGET_HEIGHT);
		
		parent::__construct( 
			$this->namespace, 
			_($label),
			$widget_ops,
			$control_ops
		);
		global $Plugin_Template;
		$this->_parent = $Plugin_Template;
		$this->add_hooks();
	}
	
	/**
     * Add in various hooks
     * 
     * Place all add_action, add_filter, add_shortcode hook-ins here
     */
	function add_hooks(){
		// Register front-end js and styles for this plugin
		//add_action( 'wp_enqueue_scripts', array( &$this, 'wp_register_scripts' ), 1 );
		//add_action( 'wp_enqueue_scripts', array( &$this, 'wp_register_styles' ), 1 );

        // Register admin js and styles for this plugin
        add_action( 'admin_head', array( &$this, 'wp_register_scripts' ), 1 );
        add_action( 'admin_head', array( &$this, 'wp_register_styles' ), 1 );

        // Add Shortcode for widget
		add_shortcode('the_widget', array(&$this, 'the_shortcode'));
	}
	
	/**
     * Register scripts used by this plugin for enqueuing elsewhere
     * 
     * @uses wp_register_script()
     */
    function wp_register_scripts() {
        $name = $this->classname.'-widget';
        if(!is_admin()){
	        wp_enqueue_script( $name, WMS_REST_API_URLPATH . '/js/widget.js', array( 'jquery' ), $this->version, true );
        }else{
            global $pagenow;
            if ($pagenow == "widgets.php") {
                wp_enqueue_script( $name.'-admin', WMS_REST_API_URLPATH . '/js/admin.js', array( 'jquery' ), $this->version, true );
                wp_localize_script( $name.'-admin', $this->namespace . '_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));  
            }
        }
    }
    
    /**
     * Register styles used by this plugin for enqueuing elsewhere
     * 
     * @uses wp_register_style()
     */
    function wp_register_styles() {
        // Admin Stylesheet
        $name = $this->classname.'-widget';
        if(!is_admin()){
	        wp_enqueue_style( $name, WMS_REST_API_URLPATH . '/css/widget.css', array(), $this->version, 'screen' );
	    }else{
        	wp_enqueue_style( $name.'-admin', WMS_REST_API_URLPATH . '/css/admin.css', array(), $this->version, 'screen' );
        }
    }
	
    
    /**
     * Widget Display
     *
     * @param Array $args Settings
     * @param Array $instance Widget Instance
     */
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		echo $before_widget;

		// TODO:	Here is where you manipulate your widget's values based on their input fields
		include( plugin_dir_path( __FILE__ ) . '/lib/functions.php' );
		include( plugin_dir_path( __FILE__ ) . '/views/view-widget.php' );

		echo $after_widget;

	}
	
	/**
	 * Widgets page form submission logic
	 *
	 * @param Array $new_instance
	 * @param Array $old_instance
	 * @return unknown
	 */
	function update( $new_instance, $old_instance ) {
	    
	    foreach( $new_instance as $key => $val ) {
	        $data[$key] = $this->Parent->_sanitize( $val );
	    }

	    return $data;
	}

	/**
	 * Widgets page form controls
	 *
	 * @param Array $instance
	 */
	function form( $instance ) {
	
    	//Set up some default widget settings.
    	$defaults = $this->defaults;
    	
    	$instance = wp_parse_args( (array) $instance, $defaults ); 
    	
    	require('views/view-admin.php');
	}
    
	/**
	 * Widget shortcode
	 *
	 * @param Array $atts
	 * @return String Widget HTML
	 */
	function the_shortcode($atts) {
	    static $widget_i = 0;
	    global $wp_widget_factory;
	    
	    $defaults = shortcode_atts($this->defaults, $atts);
	    
	    $instance = wp_parse_args( (array) $instance, $defaults ); 
	    
	    if (!is_a($wp_widget_factory->widgets[$this->widgetname], $this->widgetname)){
	        $wp_class = 'WP_Widget_'.ucwords(strtolower($class));
	        
	        if (!is_a($wp_widget_factory->widgets[$wp_class], 'WP_Widget')){
	            return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct"),'<strong>'.$class.'</strong>').'</p>';
	        } else {
	            $class = $wp_class;
	        }
	    }
	    
	    ob_start();
	    
	    the_widget($this->widgetname, $instance, array(
	    	'widget_id'     => $this->classname.'-'.$widget_i,
	        'before_widget' => '<div id="'.$this->namespace.'-'.$widget_i++.'" class="widget '.$this->classname.' cf">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="title">',
			'after_title'   => '</h2>'
	    ));
	    
	    return ob_get_clean();
	    
	}
	
}

?>
