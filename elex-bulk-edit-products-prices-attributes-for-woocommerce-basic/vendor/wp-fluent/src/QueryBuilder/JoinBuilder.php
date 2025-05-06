<?php namespace WpFluent\QueryBuilder;

class JoinBuilder extends QueryBuilderHandler {

	/**
	 * Join on callback
	 *
	 * @param $key
	 * @param $operator
	 * @param $value
	 *
	 * @return $this
	 */
	public function on( $key, $operator, $value ) {
		return $this->joinHandler( $key, $operator, $value, 'AND' );
	}

	/**
	 * Join on or callback
	 *
	 * @param $key
	 * @param $operator
	 * @param $value
	 *
	 * @return $this
	 */
	public function orOn( $key, $operator, $value ) {
		return $this->joinHandler( $key, $operator, $value, 'OR' );
	}

	/**
	 * Join Hanlder
	 *
	 * @param        $key
	 * @param null   $operator
	 * @param null   $value
	 * @param string $joiner
	 *
	 * @return $this
	 */
	protected function joinHandler( $key, $operator = null, $value = null, $joiner = 'AND' ) {
		$key                            = $this->addTablePrefix( $key );
		$value                          = $this->addTablePrefix( $value );
		$this->statements['criteria'][] = compact( 'key', 'operator', 'value', 'joiner' );

		return $this;
	}
}
