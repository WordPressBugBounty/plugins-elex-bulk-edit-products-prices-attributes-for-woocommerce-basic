<?php namespace Viocon;

/**
 * This class gives the ability to access non-static methods statically
 *
 * Class AliasFacade
 *
 * @package Viocon
 */
class AliasFacade {

	protected static $vioconInstance;

	public static function __callStatic( $method, $args ) {
		if ( ! static::$vioconInstance ) {
			static::$vioconInstance = new Container();
		}

		return call_user_func_array( array( static::$vioconInstance, $method ), $args );
	}

	public static function setVioconInstance( Container $instance ) {
		static::$vioconInstance = $instance;
	}

	public static function getVioconInstance() {
		return static::$vioconInstance;
	}
}
