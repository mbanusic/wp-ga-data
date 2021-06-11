<?php

namespace NeZnam\GaData;

class Init {

    const PLUGIN_VERSION = '1.0.0';

    const PLUGIN_NAME = 'NeZnam GA Data Plugin';

    const PLUGIN_SLUG = 'neznam-ga-data-plugin';

    const PLUGIN_PREFIX = 'neznam-ga-plugin';

    private static $plugin_path;

    private static $plugin_url;

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', Init::PLUGIN_SLUG ), '2.0' );
    }

    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', Init::PLUGIN_SLUG ), '2.0' );
    }

    public function __construct() {
        self::$plugin_path = plugin_dir_path( dirname( __FILE__ ) );
        self::$plugin_url  = plugin_dir_url( dirname( __FILE__ ) );
        $this->init_hooks();
        do_action( Init::PLUGIN_SLUG . '_loaded' );
    }

    public function init_hooks() {
        Admin::instance();
        Elastic::instance();
        Statistic::instance();
        if (defined( 'WP_CLI' ) && WP_CLI) {
            Cli::instance();
        }
        ActionsFilters::init_actions_filters();
    }

    public static function get_plugin_path() {
        return isset( self::$plugin_path ) ? self::$plugin_path : plugin_dir_path( dirname( __FILE__ ) );
    }

    public static function get_plugin_url() {
        return isset( self::$plugin_url ) ? self::$plugin_url : plugin_dir_url( dirname( __FILE__ ) );
    }
}
