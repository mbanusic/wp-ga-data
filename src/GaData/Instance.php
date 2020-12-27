<?php

namespace NeZnam\GaData;

abstract class Instance {

	private static $instances = [];

	/**
	 * Constructor
	 *
	 */
	abstract protected function __construct();
	/**
	 * Register callbacks for actions and filters
	 *
	 */
	abstract protected function register_hook_callbacks();

	public static function instance () {
		$classname = get_called_class();
		if ( ! isset( self::$instances[ $classname ] ) ) {
			self::$instances[ $classname ] = new $classname();
		}
		return self::$instances[ $classname ];
	}
}