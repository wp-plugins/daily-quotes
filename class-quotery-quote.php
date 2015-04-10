<?php
/**
 * Plugin Name - daily-quotes
 * @author     Bobcares <geethu.n@poornam.com>
 * @link      http://www.bobcares.com/
 * @copyright Copyright 2014, poornam.com
 */


/*
 * fucntion to display contents in the webpage
* @param null
* @return display contents in a webpage
*/


if (!function_exists('writeLog')) {

	/**
	 * Function to add the plugin log to wordpress log file, added by BDT
	 * @param object $log
	 */
	function writeLog($log, $line = "",$file = "")  {

		if (WP_DEBUG === true) {

			$pluginLog = $log ." on line [" . $line . "] of [" . $file . "]\n";

			if ( is_array( $pluginLog ) || is_object( $pluginLog ) ) {
				print_r( $pluginLog, true );
			} else {
				error_log( $pluginLog );
			}

		}
	}

}


/**
 * Plugin class. This class is used to work with the
 * public-facing side of the WordPress site.
 *
 * @author  Bobcares <geethu.n@poornam.com>
 */
class Quotery_Quote {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @var     string
	 */
	const VERSION 						= '1.0.4';

	/**
	 * Cache prefix
	 */
	const CACHE_GROUP 					= 'quotery_qod_plugin_';

	/**
	 * Unique identifier for your plugin.
	 *
	 * @var      string
	 */
	protected $plugin_slug 				= 'quotery-quote-of-the-day';

	protected $quotes_url 				= 'http://www.quotery.com/api/qod/';
	protected $quotes_categories_url 	= 'http://www.quotery.com/api/qod/get/categories/';
        
	// protected $quotes_url 				= 'http://localhost/wp/quotery/api/qod/';
	// protected $quotes_categories_url 	= 'http://localhost/wp/quotery/api/qod/get/categories/';


	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action('init', array($this, 'add_shortcodes'));
	}

	/**
	 * Return the plugin slug.
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		writeLog(" Returning existing instance of the Quotery_Quote class for quote generation ", basename(__LINE__), basename(__FILE__));
		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

               /* Function checks whether the multi site is activated */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

                    /* Function checks whether the mnetwork widw variable is activated */
			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

                                /* Compare each blog ids with the blog id */
				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

                /* Function checks whether the multi site is activated */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

                        /* Function checks whether the mnetwork widw variable is activated */
			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

                                /* Compare each blog ids with the blog id */
				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 */
	private static function single_activate() {

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 */
	private static function single_deactivate() {

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/styles.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

		// http://api.theysaidso.com/qod.json
	/**
	 * Get a quote of the day
	 *
	 */
	public function get_quote($options = array(), $force_load = false)
	{

		$category = $options['topics'] ? $options['topics'] : 'all';

		$cache_name = 'quotery_qod_plugin_' . $category;

		$expire 	= 60 * 30; // cache quote for 30 minutes

		$url = $this->quotes_url . $category;

		if ($force_load) {
			$quote = false;
		} else {
			$quote = get_transient($cache_name);
		}
		$quote = false;

		if (false === $quote) {
			$remote = wp_remote_get($url);
			$quote = json_decode($remote['body']);
			if ($quote && $quote->type == 'success') {
				set_transient($cache_name, $quote, $expire);
			} else {
				$quote = false;
			}
		}

		return $quote;
	}

	public function get_categories($force_load = false)
	{
		$cache_name = 'quotery_qod_plugin_categories';
		$expire 	= 60 * 30; // cache quote for 30 minutes

		if ($force_load) {
			$categories = false;
		} else {
			$categories = get_transient($cache_name);
		}
		if (false === $categories) {

			$remote = wp_remote_get($this->quotes_categories_url);
			$categories = json_decode($remote['body']);

			if ($categories && $categories->type == 'success') {
				$categories = (array) $categories->contents->categories;
				set_transient($cache_name, $categories, $expire);
			} else {
				$categories = false;
			}
		}

		return $categories;
	}

	public function add_shortcodes()
	{
		add_shortcode('quotery_qod', array($this, 'shortcode_quotery_qod'));
	}

	public function shortcode_quotery_qod($atts, $content = "")
	{
		$instance = shortcode_atts($this->get_quote_default_settings(), $atts);

		ob_start();
		$this->quote_html($instance);
		$html = ob_get_clean();

		return $html;
	}

	public function quote_html($instance)
	{
		$title = apply_filters('widget_title', $instance['title'] );

		$quote_data = $this->get_quote(array(
			'topics' 	=> $instance['topics'],
			// 'quantity'	=> $instance['quantity'],
		));

		if ($quote_data) {
			$quote = $quote_data->contents->quote->quote;
			$author = $quote_data->contents->quote->author;
			$share_url = $quote_data->contents->quote->share_url;
		} else {
			$quote = __('Cannot fetch quote from source', $this->plugin_slug);
			$instance['social'] = $instance['author'] = false;
		}

		include( plugin_dir_path( __FILE__ ) . 'widget-public.php' );
	}

	public function get_quote_default_settings()
	{
		return array(
			'title' 	=> __('Quote of the Day"', $this->plugin_slug),
			'topics'	=> 'all',
			// 'quantity'	=> 1,
			'color'		=> 'light',
			'author'	=> 'yes',
			'social'	=> 'yes',
		);
	}

	public function get_color_options()
	{
		return array(
			'light' 	=> __('Light', $this->plugin_slug),
			'orange' 	=> __('Orange', $this->plugin_slug),
			'dark' 		=> __('Dark', $this->plugin_slug),
		);
	}

	public function get_quantity_options()
	{
		return array(
			1 => __('1', $this->plugin_slug),
			2 => __('2', $this->plugin_slug),
			3 => __('3', $this->plugin_slug),
		);
	}

	public function filter_in_array($key, $array)
	{
		$keys = array_keys($array);

		return in_array($key, $keys) ? $key : $keys[0];
	}

	public function get_topics_options()
	{
		$categories = $this->get_categories();

		if (!$categories) {
			$categories = array(
				'all' 		=> __('All', $this->plugin_slug),
			);
		}

		return $categories;
	}

	public function clear_cache()
	{
		global $wpdb;
		return $wpdb->query($wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '%' . self::CACHE_GROUP . '%'
		));
	}
}
