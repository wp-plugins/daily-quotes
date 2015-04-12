<?php
/**
 * Plugin Name: Daily Quotes
 * Description: A widget that displays daily inspirational quotes
 * Version: 1.0.0
 * Author: Bobcares 
 * Author URI: http://bobcares.com/
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Add actions to widgets_init to load the widget.
 */
add_action("widgets_init", "daily_quotes_load_widgets");
add_action("wp_enqueue_scripts", "daily_quotes_enqueue_scripts");


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
 * function to register the widget "daily_quotes"
 * @author Bobcares 
 */
function daily_quotes_load_widgets() {
    register_widget("Daily_Quotes");
}

/**
 * function to enqueue the styles and scripts used in the widget "daily_quotes"
 * @author Bobcares
 */
function daily_quotes_enqueue_scripts() {
    wp_enqueue_style("styles", plugin_dir_url(__FILE__) . "styles.css");
    wp_enqueue_script("scripts", plugin_dir_url(__FILE__) . "scripts.js", array("jquery"), "1.0.0", true);
}

/**
 * Daily_Quotes: Class which contains the functions for the display of the Daily Quote widget
 *
 * @author  Bobcares
 */
class Daily_Quotes extends WP_Widget {

    protected $plugin_slug;

    /**
     * Constructor
     */
    function Daily_Quotes() {
        include( plugin_dir_path(__FILE__) . 'class-quotery-quote.php' );
        $this->plugin_slug = Quotery_Quote::get_instance()->get_plugin_slug();
        $idBase = "daily_quotes_id";
        $name = "Daily Quotes";
        $description = "A widget that displays the daily inspirational quotes.";

        /* Widget settings. */
        $widgetOptions = array(
            "classname" => "daily_quotes", // CSS classname of the widget container
            "description" => $description // widget description which appears in admin area (Available widgets)
        );

        /* Widget control settings. */
        $controlOptions = array(
            "width" => 300, // width of the fully expanded control form in admin area (Sidebar)
            "id_base" => $idBase // ID of the widget container. This is used for multi-widgets . Id of each instance will be like {$id_base}-{$unique_number}
        );

        /* Create the widget. */
        $this->WP_Widget($idBase, $name, $widgetOptions, $controlOptions);
    }

    /**
     * Function to display the daily_quotes widget on the screen.
     *
     * @param 	Array 	$args	array of arguments
     * @param	Object	$instance	widget instance
     * @author  Bobcares
     */
    function widget($args, $instance) {
        global $post;

        /* Our variables from the widget settings. */
        $title = apply_filters("widget_title", $instance["title"]);


        echo $args["before_widget"];
        Quotery_Quote::get_instance()->quote_html($instance);
        ?>
        <?php
        echo $args["after_widget"];
    }

    /**
     * function to update the widget settings in admin area.
     * @param	Object	$newInstance	New instance of the widget
     * @param	Object	$oldInstance	Old instance of the widget
     * @return	Object	updated instance of the widget
     * @author  Bobcares
     */
    function update($newInstance, $oldInstance) {
        $instance = $oldInstance;

        /* Strip tags for title and name to remove HTML. */
        $instance["title"] = strip_tags($newInstance["title"]);

        $instance["author"] = strip_tags($newInstance["author"]);
        
        $instance['topics'] = Quotery_Quote::get_instance()->filter_in_array($new_instance['topics'], Quotery_Quote::get_instance()->get_topics_options());

        /* Strip tags for message and name to remove HTML. */
        $instance["topics"] = strip_tags($newInstance["topics"]);

        /* Strip tags for message and name to remove HTML. */
        $instance["border"] = strip_tags($newInstance["border"]);
        
	writeLog("Updated the daily quote with the new admin settings", basename(__LINE__), basename(__FILE__));
        
        return $instance;
        
        
    }

    /**
     * Displays the widget settings on the widget panel in admin area.
     * @param	Object	Widget instance
     * @author  Bobcares
     */
    function form($instance) {

        $instance = wp_parse_args(
                (array) $instance, Quotery_Quote::get_instance()->get_quote_default_settings()
        );

        /* Default widget settings. */
        $defaults = array("title" => "");
        $defaults = array("topics" => "");
        $defaults = array("border" => "");
        $defaults = array("author" => "");
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>

        <!-- Widget Title: Text Box -->
        <p>
            <label for="<?php echo $this->get_field_id("title"); ?>">
                <?php _e("Title", "daily_quotes") . " : "; ?>
            </label>
            <input id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" value="<?php echo $instance["title"]; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('topics'); ?>"><?php _e('Topic:', $this->plugin_slug) ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('topics'); ?>" name="<?php echo $this->get_field_name('topics'); ?>">
                <?php foreach (Quotery_Quote::get_instance()->get_topics_options() as $value => $name): ?>
                    <option value="<?php echo $value ?>"<?php echo $value == $instance['topics'] ? ' selected="selected"' : '' ?>><?php echo $name ?></option>
                <?php endforeach ?>
            </select>
        </p>

        <!-- Widget Title: Text Box -->
        <p>
            <label for="<?php echo $this->get_field_id("border"); ?>">
                <?php _e("Border Color", "daily_quotes") . " : "; ?>
            </label>
            <input id="<?php echo $this->get_field_id("border"); ?>" name="<?php echo $this->get_field_name("border"); ?>" value="<?php echo $instance["border"]; ?>" />
        </p>

        <?php
    }

}
?>
