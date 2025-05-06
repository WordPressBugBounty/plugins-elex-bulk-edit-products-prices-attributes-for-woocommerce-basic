<?php
namespace WpFluent;

use WpFluent\QueryBuilder\QueryBuilderHandler;

/**
 * This class gives the ability to access non-static methods statically
 *
 * Class AliasFacade
 *
 * @package WpFluent
 */
class AliasFacade {


	protected static $queryBuilderInstance;

	public static function __callStatic( $method, $args ) {
		if ( ! static::$queryBuilderInstance ) {
			static::$queryBuilderInstance = new QueryBuilderHandler();
		}

		// Call the non-static method from the class instance
		return call_user_func_array( array( static::$queryBuilderInstance, $method ), $args );
	}

	public static function setQueryBuilderInstance( $queryBuilderInstance ) {
		static::$queryBuilderInstance = $queryBuilderInstance;
	}
}
