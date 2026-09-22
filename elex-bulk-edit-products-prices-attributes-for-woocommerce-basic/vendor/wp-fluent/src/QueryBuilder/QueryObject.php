<?php

namespace WpFluent\QueryBuilder;

class QueryObject {

	protected $sql;

	protected $db;

	protected $bindings = array();

	public function __construct( $sql, array $bindings ) {
		$this->sql = (string) $sql;

		$this->bindings = $bindings;

		global $wpdb;

		$this->db = $wpdb;
	}

	public function getSql() {
		return $this->sql;
	}

	public function getBindings() {
		return $this->bindings;
	}

	/**
	 * Get the raw/bound sql
	 *
	 * @return string
	 */
	public function getRawSql() {
		return $this->interpolateQuery( $this->sql, $this->bindings );
	}

	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from
	 * $params are are in the same order as specified in $query
	 *
	 * Reference: http://stackoverflow.com/a/1376838/656489
	 *
	 * @param string $query  The sql query with parameter placeholders
	 * @param array  $params The array of substitution parameters
	 *
	 * @return string The interpolated query
	 */
	protected function interpolateQuery( $query, $params ) {
		$keys         = array();
		$placeHolders = array();

		foreach ( $params as $key => $value ) {
			if ( is_string( $key ) ) {
				$keys[] = '/:' . $key . '/';
			} else {
				$keys[] = '/[?]/';
			}

			$placeHolders[] = $this->getPlaceHolder( $value );
		}

		$query = preg_replace( $keys, $placeHolders, $query, 1, $count );

		return $params ? $this->db->prepare( $query, $params ) : $query;
	}

	private function getPlaceHolder( $value ) {
		$placeHolder = '%s';

		if ( is_int( $value ) ) {
			$placeHolder = '%d';
		} elseif ( is_float( $value ) ) {
			$placeHolder = '%f';
		}

		return $placeHolder;
	}
}
