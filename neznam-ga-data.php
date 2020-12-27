<?php

/**
 * NeZnam GA Data
 *
 * @package     NeZnam\GaData
 * @author      Marko Banusic
 * @copyright   2020 mbanusic
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: NeZnam GA Data
 * Plugin URI:  https://nezn.am
 * Description: Pull data from GA to WordPress
 * Version:     1.0.0
 * Author:      Marko Banusic
 * Author URI:  https://nezn.am
 * Text Domain: neznam
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

function neznam_ga_data_plugin() {

    require_once dirname(__FILE__) . '/vendor/autoload.php';

    NeZnam\GaData\Init::instance();
}
neznam_ga_data_plugin();
