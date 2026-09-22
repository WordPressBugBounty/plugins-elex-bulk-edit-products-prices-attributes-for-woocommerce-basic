<?php namespace WpFluent\QueryBuilder;

class Raw {

	protected $value;

	protected $bindings;

	public function __construct( $value, $bindings = array() ) {
		$this->value    = (string) $value;
		$this->bindings = (array) $bindings;
	}

	public function getBindings() {
		return $this->bindings;
	}

	public function __toString() {
		return (string) $this->value;
	}
}
