<?php

/**
 * Created by PhpStorm.
 * User: drm2
 * Date: 12/16/2014
 * Time: 12:01 PM
 */
class Wms_JSON_Dining {
	public $widget_id, $widget_name, $debug; // from callee
	private $csv_body, $last_modified, $data;
	//private $csv_file_name = 'http://dev.wms.dev/wp-content\plugins\wms-dining-menu-widget\lib\daily-menu.csv';
	private $csv_file_name = 'http://dining.williams.edu/files/daily-menu.csv';
	private $csv_ttl = DAY_IN_SECONDS;
	// Map of OIT service unit ids to NetNutrition location ids
	private $service_unit_id_map = array(
		27  => array( 'S3', 'Driscoll Dining Hall' ),
		29  => array( 'S5', 'Mission Dining Hall' ),
		30  => array( '', 'CDE' ),
		32  => array( '', 'Faculty House' ),
		34  => array( '', 'Bakeshop' ),
		36  => array( '', 'CENTRAL OFFICE' ),
		38  => array( 'S14', 'Eco-Cafe' ),
		208 => array( 'S211', 'Paresky Whitmans Market' ),
		209 => array( 'S23', 'Paresky Grab N Go' ),
		210 => array( 'S25', 'Paresky 82 Grill' ),
		214 => array( 'S24', 'Paresky Snack Bar' ),
		264 => array( 'S221', 'Paresky Late Nite' ),
		277 => array( '', 'Mission Salad' ),
		278 => array( '', 'Mission Cereal' ),
		279 => array( '', 'Mission Beverage' ),
		280 => array( '', 'Bread Bar' ),
		281 => array( '', 'Mission Pasta' ),
		283 => array( '', 'Mission Bread' ),
		285 => array( '', 'Mission Gluten Free' ),
		287 => array( '', 'Mission Deli' ),
		288 => array( '', 'Mission Salad Dressing' ),
		289 => array( '', 'Mission Condiments' ),
		290 => array( '', 'Mission Pasta Sauce' ),
		291 => array( '', 'Taco Bar' ),
		295 => array( '', 'Driscoll Bread' ),
		296 => array( '', 'Driscoll cereal bar' ),
		297 => array( '', 'Driscoll Salad' ),
		298 => array( '', 'Driscoll Pasta' ),
		299 => array( '', 'Driscoll condiment' ),
		300 => array( '', 'Driscoll Bev' ),
		301 => array( '', 'Driscoll deli' ),
		305 => array( '', 'Late Night Deli' ),
		306 => array( '', 'Late Night Beverage' ),
		307 => array( '', 'Late Night Salad' ),
		308 => array( '', 'Late Night Cereal' ),
		309 => array( '', 'Late Night Condiments' ),
		310 => array( '', 'Late Night Gluten Free' ),
		311 => array( '', 'Whitman Cereal' ),
		312 => array( '', 'Whitman Salad' ),
		313 => array( '', 'Whitman Deli' ),
		314 => array( '', 'Whitman Pasta' ),
		315 => array( '', 'Whitman Beverage' ),
		316 => array( '', 'Whitman Bread' ),
		317 => array( '', 'Whitman Condiments' ),
		318 => array( '', 'Whitman Gluten Free' ),
		323 => array( '', 'Paresky Lee After Dark S' ),
	);

	function __construct( $widget_id, $widget_name, $debug ) {
		$this->widget_id = $widget_id;
		$this->widget_name = $widget_name;
		$this->debug = $debug;
		$this->init();
	}

	/**
	 * Initialization function to hook into the WordPress init action
	 *
	 * Instantiates the class on a global variable and sets the class, actions
	 * etc. up for use.
	 *
	 * @param $widget_id
	 * @param $widget_name
	 * @param $debug
	 */
	static function instance( $widget_id, $widget_name, $debug ) {
		global $Wms_JSON_Dining;

		// Only instantiate the Class if it hasn't been already
		if ( ! isset( $Wms_JSON_Dining ) ) {
			$Wms_JSON_Dining = new Wms_JSON_Dining( $widget_id, $widget_name, $debug );
		}
	}

	/**
	 * Convert the csv to JSON
	 */
	public function init() {
		$this->add_hooks();
		// Is this an API request
		$is_api = stristr($_SERVER['REQUEST_URI'], '/wp-json/dining/') > -1;
		// Get csv file last modified time as transient.
		$this->last_modified = $this->get_transient( '_modified' );
		// If the transient doesn't exist or is older than 24 hours.
		if ( !$is_api || $this->debug || false === ( $this->last_modified ) || ( time() - $this->last_modified > $this->csv_ttl ) ) {
			$this->get_csv();
			$this->populate_data();
			$this->set_transients();
		}
	}

	public function add_hooks() {
		// Add JSON API classes here
		//        add_action( 'wp_json_server_before_serve', array( &$this, 'init_api' ) );
		add_filter( 'rest_endpoints', array( &$this, 'register_routes' ) );
	}

	/**
	 * Register the taxonomy-related routes
	 *
	 * @param array $routes Existing routes
	 *
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$dining_routes = array(
			'/dining'                                        => array(
				array(
					'callback' => array( $this, 'do_nothing' ),
					'methods'  => WP_REST_Server::READABLE
				)
			),
			'/dining/menus'                                  => array(
				array(
					'callback' => array( $this, 'get_menus' ),
					'methods'  => WP_REST_Server::READABLE,
					'args'     => array(
						'context' => array(
							'required' => false,
						),
					)
				),
			),
			'/dining/service_units'                          => array(
				array(
					'callback' => array( $this, 'get_service_units' ),
					'methods'  => WP_REST_Server::READABLE,
					'args'     => array(
						'context' => array(
							'required' => false,
						),
					)
				),
			),
			'/dining/service_units/(?P<unitid>\w+)' => array(
				array(
					'callback' => array( $this, 'get_menus_by_service_unit' ),
					'methods'  => WP_REST_Server::READABLE,
					'args'     => array(
						'context' => array(
							'required' => false,
						),
					)
				),
			),
		);

		return array_merge( $routes, $dining_routes );
	}

	public function do_nothing() {

	}

	/**
	 * Get all menus
	 * @return array
	 */
	public function get_menus() {
		return $this->get_transient( '_data' );
	}

	/**
	 * Get a hash of unitid & name
	 * @return array
	 */
	public function get_service_units() {
		$data = $this->get_menus();
		$unique = array();
		foreach ( $data as $item ) {
			if ( ! array_key_exists( $item['unitid'], $unique ) ) {
				$unique[ $item['unitid'] ] = array(
					'unitid' => $item['unitid'],
					'service_unit'    => $item['service_unit'],
					'net_nutrition' => $item['net_nutrition']
				);
			}
		}

		return $unique;
	}

	/**
	 * Get all menus by specific unitid
	 *
	 * @param $request WP_JSON_Request
	 *
	 * @return array
	 */
	public function get_menus_by_service_unit( $request ) {
		$data = $this->get_menus();

		$arr = array_filter( $data, function ( $item ) use ( $request ) {
			return $request->get_param( 'unitid' ) == $item['unitid'];
		} );

		return array_values( $arr );
	}

	public function get_transient( $which ) {
		return get_transient( $this->widget_id . $which );
	}

	public function set_transients() {
		set_transient( $this->widget_id . '_data', $this->data, $this->csv_ttl );
		set_transient( $this->widget_id . '_modified', $this->last_modified, $this->csv_ttl );
	}

	/**
	 * Process the CSV
	 * 1. Check if stored transient exists
	 * 2. If transient is older than 24 hrs or doesn't exist...
	 * 3. Get the remote CSV
	 * 4. Store it as transient
	 */
	public function get_csv() {
		// Get the remote CSV menu.
		$csv_file = wp_remote_get( $this->csv_file_name );
		if ( is_a( $csv_file, 'WP_Error' ) ) {
			error_log( $this->widget_name . ' - Could not get remote CSV file: ' . $this->csv_file_name );
		} else {
			$this->csv_body = $csv_file['body'];
			$this->last_modified = strtotime( $csv_file['headers']['last-modified'] );
		}
	}

	public function populate_data() {
		// Map csv to array
		if ( ! $rows = array_map( "str_getcsv", explode( "\n", $this->csv_body ) ) ) {
			error_log( $this->widget_name . ' - Could not retrieve menu CSV data.' );
		}
		// Generate IDs for each service_unit
		$header = array_shift( $rows );
		$net_nutrition = count( $header );
		$header[ $net_nutrition ] = 'net_nutrition';
		$unitid = array_search( 'unitid', $header );
		foreach ( $rows as $row ) {
			if ( ! $row[0] ) {
				continue;
			}
			$row[$net_nutrition] = $this->service_unit_id_map[ $row[ $unitid ] ][0];
			$this->data[] = array_combine( $header, $row );
		}
	}
}

/**
 * Initialize REST API classes here
 */
Wms_JSON_Dining::instance( $this->id, $this->friendly_name, $this->debug );