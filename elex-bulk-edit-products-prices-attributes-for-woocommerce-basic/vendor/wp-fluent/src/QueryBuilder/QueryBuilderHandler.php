<?php namespace WpFluent\QueryBuilder;

use Closure;
use WpFluent\Connection;
use WpFluent\Exception;

class QueryBuilderHandler {


	protected $container;

	protected $connection;

	protected $statements = array();

	protected $db;

	protected $dbStatement = null;

	protected $tablePrefix = null;

	protected $adapterInstance;
	protected $adapter;
	protected $adapterConfig;

	protected $fetchParameters = array( 5 ); // \PDO::FETCH_OBJ is the value we are replacing because $wpdb issues

	public function __construct( Connection $connection = null ) {
		if ( is_null( $connection ) ) {
			$connection = Connection::getStoredConnection();
			if ( ! $connection ) {
				throw new Exception( 'No database connection found.', 1 );
			}
		}

		$this->connection    = $connection;
		$this->container     = $this->connection->getContainer();
		$this->db            = $this->connection->getDbInstance();
		$this->adapter       = $this->connection->getAdapter();
		$this->adapterConfig = $this->connection->getAdapterConfig();

		if ( isset( $this->adapterConfig['prefix'] ) ) {
			$this->tablePrefix = $this->adapterConfig['prefix'];
		}

		// Query builder adapter instance
		$this->adapterInstance = $this->container->build(
			'\\WpFluent\\QueryBuilder\\Adapters\\' . ucfirst( $this->adapter ),
			array( $this->connection )
		);
	}

	/**
	 * Set the fetch mode
	 *
	 * @param $mode
	 * @return $this
	 */
	public function setFetchMode( $mode ) {
		$this->fetchParameters = func_get_args();

		return $this;
	}

	/**
	 * Fetch query results as object of specified type
	 *
	 * @param $className
	 * @param array $constructorArgs
	 * @return QueryBuilderHandler
	 */
	public function asObject( $className, $constructorArgs = array() ) {
		die( 'need to implement this' );
	}

	public function newQuery( Connection $connection = null ) {
		if ( is_null( $connection ) ) {
			$connection = $this->connection;
		}

		return new static( $connection );
	}

	public function query( $sql, $bindings = array() ) {
		$this->dbStatement = $this->container->build(
			'\\WpFluent\\QueryBuilder\\QueryObject',
			array( $sql, $bindings )
		)->getRawSql();

		return $this;
	}

	public function statement( $rawSql ) {
		$start = microtime( true );

		$this->db->query( $rawSql );

		return microtime( true ) - $start;
	}

	/**
	 * Get all rows
	 *
	 * @return array|object|null
	 * @throws \WpFluent\Exception
	 */
	public function get() {
		$eventResult = $this->fireEvents( 'before-select' );

		if ( ! is_null( $eventResult ) ) {
			return $eventResult;
		};

		if ( is_null( $this->dbStatement ) ) {
			$queryObject = $this->getQuery( 'select' );

			$this->dbStatement = $queryObject->getRawSql();
		}

		$start             = microtime( true );
		$result            = $this->db->get_results( $this->dbStatement );
		$executionTime     = microtime( true ) - $start;
		$this->dbStatement = null;
		$this->fireEvents( 'after-select', $result, $executionTime );

		return $result;
	}

	/**
	 * Get chunked rows
	 *
	 * @return array|object|null
	 * @throws \WpFluent\Exception
	 */
	public function chunk( $limit, Closure $callback, $chunkPage = 1 ) {

		$this->limit( $limit );
		$this->offset( $limit * ( $chunkPage - 1 ) );

		$result = $this->get();

		if ( count( $result ) ) {
			$callback( $result );
			return $this->chunk( $limit, $callback, ++$chunkPage );
		}

		return $chunkPage;
	}

	/**
	 * Get first row
	 *
	 * @return \stdClass|null
	 */
	public function first() {
		$this->limit( 1 );
		$result = $this->get();

		return empty( $result ) ? null : $result[0];
	}

	public function findAll( $fieldName, $value ) {
		$this->where( $fieldName, '=', $value );

		return $this->get();
	}

	public function find( $value, $fieldName = 'id' ) {
		$this->where( $fieldName, '=', $value );

		return $this->first();
	}

	public function count() {
		// Get the current statements
		$originalStatements = $this->statements;

		unset( $this->statements['orderBys'] );
		unset( $this->statements['limit'] );
		unset( $this->statements['offset'] );

		$count            = $this->aggregate( 'count' );
		$this->statements = $originalStatements;

		return $count;
	}

	protected function aggregate( $type ) {
		// Get the current selects
		$mainSelects = isset( $this->statements['selects'] ) ? $this->statements['selects'] : null;
		// Replace select with a scalar value like `count`
		$this->statements['selects'] = array( $this->raw( $type . '(*) as field' ) );
		$row                         = $this->get();

		// Set the select as it was
		if ( $mainSelects ) {
			$this->statements['selects'] = $mainSelects;
		} else {
			unset( $this->statements['selects'] );
		}
		$count = count( $row );
		if ( $count > 1 ) {
			return $count;
		} else {
			$item = (array) $row[0];

			return (int) $item['field'];
		}
	}

	public function getQuery( $type = 'select', $dataToBePassed = array() ) {
		$allowedTypes = array( 'select', 'insert', 'insertignore', 'replace', 'delete', 'update', 'criteriaonly' );

		if ( ! in_array( strtolower( $type ), $allowedTypes, true ) ) {
			throw new Exception( $type . ' is not a known type.', 2 );
		}

		$queryArr = $this->adapterInstance->$type( $this->statements, $dataToBePassed );

		return  $this->container->build(
			'\\WpFluent\\QueryBuilder\\QueryObject',
			array( $queryArr['sql'], $queryArr['bindings'] )
		);
	}

	public function subQuery( QueryBuilderHandler $queryBuilder, $alias = null ) {
		$sql = '(' . $queryBuilder->getQuery()->getRawSql() . ')';

		if ( $alias ) {
			$sql = $sql . ' as ' . $alias;
		}

		return $queryBuilder->raw( $sql );
	}

	private function doInsert( $data, $type ) {
		$eventResult = $this->fireEvents( 'before-insert' );

		if ( ! is_null( $eventResult ) ) {
			return $eventResult;
		}

		// If first value is not an array
		// Its not a batch insert
		if ( ! is_array( current( $data ) ) ) {
			$start = microtime( true );

			$queryObject = $this->getQuery( $type, $data );

			$executionTime = $this->statement( $queryObject->getRawSql() );

			$return = $this->db->insert_id;
		} else {
			// Its a batch insert
			$executionTime = 0;
			$return        = array();
			foreach ( $data as $subData ) {
				$start = microtime( true );

				$queryObject = $this->getQuery( $type, $subData );

				$executionTime = $this->statement( $queryObject->getRawSql() );

				$return[] = $this->db->insert_id;
			}
		}

		$this->fireEvents( 'after-insert', $return, $executionTime );

		return $return;
	}

	public function insert( $data ) {
		return $this->doInsert( $data, 'insert' );
	}

	public function insertIgnore( $data ) {
		return $this->doInsert( $data, 'insertignore' );
	}

	public function replace( $data ) {
		return $this->doInsert( $data, 'replace' );
	}

	public function update( $data ) {
		$eventResult = $this->fireEvents( 'before-update' );

		if ( ! is_null( $eventResult ) ) {
			return $eventResult;
		}

		$queryObject = $this->getQuery( 'update', $data );

		$executionTime = $this->statement( $queryObject->getRawSql() );

		$this->fireEvents( 'after-update', $queryObject, $executionTime );
	}

	public function updateOrInsert( $data ) {
		if ( $this->first() ) {
			return $this->update( $data );
		} else {
			return $this->insert( $data );
		}
	}

	public function onDuplicateKeyUpdate( $data ) {
		$this->addStatement( 'onduplicate', $data );

		return $this;
	}

	public function delete() {
		$eventResult = $this->fireEvents( 'before-delete' );

		if ( ! is_null( $eventResult ) ) {
			return $eventResult;
		}

		$queryObject = $this->getQuery( 'delete' );

		$executionTime = $this->statement( $queryObject->getRawSql() );

		$this->fireEvents( 'after-delete', $queryObject, $executionTime );
	}

	public function table( $tables ) {

		$originalArgs = func_get_args();
	
		if ( ! is_array( $tables ) ) {
			$tables = $originalArgs;
		}
		$tablesCopy = $tables;
	
		// Proceed with further operations using $tablesCopy
		$instance = new static( $this->connection );
		$tablesCopy = $this->addTablePrefix( $tablesCopy, false );
		$instance->addStatement( 'tables', $tablesCopy );
	
		return $instance;
	}
	public function from( $tables ) {
		// Create a new variable to hold the tables
		$resolvedTables = is_array( $tables ) ? $tables : func_get_args();
	
		// Process the tables
		$resolvedTables = $this->addTablePrefix( $resolvedTables, false );
		$this->addStatement( 'tables', $resolvedTables );
	
		return $this;
	}

	public function select($fields) {
		// Create a new variable to hold the fields
		$resolvedFields = is_array($fields) ? $fields : func_get_args();
	
		// Process the fields
		$resolvedFields = $this->addTablePrefix($resolvedFields);
		$this->addStatement('selects', $resolvedFields);
	
		return $this;
	}

	public function selectDistinct( $fields ) {
		$this->select( $fields );
		$this->addStatement( 'distinct', true );

		return $this;
	}

	public function groupBy( $field ) {
		$field = $this->addTablePrefix( $field );
		$this->addStatement( 'groupBys', $field );

		return $this;
	}

	public function orderBy( $fields, $defaultDirection = 'ASC' ) {
		if ( ! is_array( $fields ) ) {
			$fields = array( $fields );
		}

		foreach ( $fields as $key => $value ) {
			$field = $key;
			$type  = $value;

			if ( is_int( $key ) ) {
				$field = $value;
				$type  = $defaultDirection;
			}

			if ( ! $field instanceof Raw ) {
				$field = $this->addTablePrefix( $field );
			}

			$this->statements['orderBys'][] = compact( 'field', 'type' );
		}

		return $this;
	}

	public function limit( $limit ) {
		$this->statements['limit'] = $limit;

		return $this;
	}

	public function offset( $offset ) {
		$this->statements['offset'] = $offset;

		return $this;
	}

	public function having( $key, $operator = null, $value = null, $joiner = 'AND' ) {
		$key                           = $this->addTablePrefix( $key );
		$this->statements['havings'][] = compact( 'key', 'operator', 'value', 'joiner' );

		return $this;
	}

	public function orHaving( $key, $operator, $value ) {
		return $this->having( $key, $operator, $value, 'OR' );
	}

	public function where( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() === 2 ) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->whereHandler( $key, $operator, $value );
	}

	public function orWhere( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() === 2 ) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->whereHandler( $key, $operator, $value, 'OR' );
	}

	public function whereNot( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() == 2 ) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->whereHandler( $key, $operator, $value, 'AND NOT' );
	}

	public function orWhereNot( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() == 2 ) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->whereHandler( $key, $operator, $value, 'OR NOT' );
	}

	public function whereIn( $key, $values ) {
		return $this->whereHandler( $key, 'IN', $values, 'AND' );
	}

	public function whereNotIn( $key, $values ) {
		return $this->whereHandler( $key, 'NOT IN', $values, 'AND' );
	}

	public function orWhereIn( $key, $values ) {
		return $this->whereHandler( $key, 'IN', $values, 'OR' );
	}

	public function orWhereNotIn( $key, $values ) {
		return $this->whereHandler( $key, 'NOT IN', $values, 'OR' );
	}

	public function whereBetween( $key, $valueFrom, $valueTo ) {
		return $this->whereHandler( $key, 'BETWEEN', array( $valueFrom, $valueTo ), 'AND' );
	}

	public function orWhereBetween( $key, $valueFrom, $valueTo ) {
		return $this->whereHandler( $key, 'BETWEEN', array( $valueFrom, $valueTo ), 'OR' );
	}

	public function whereNull( $key ) {
		return $this->whereNullHandler( $key );
	}

	public function whereNotNull( $key ) {
		return $this->whereNullHandler( $key, 'NOT' );
	}

	public function orWhereNull( $key ) {
		return $this->whereNullHandler( $key, '', 'or' );
	}

	public function orWhereNotNull( $key ) {
		return $this->whereNullHandler( $key, 'NOT', 'or' );
	}

	protected function whereNullHandler( $key, $prefix = '', $operator = '' ) {
		$key = $this->adapterInstance->wrapSanitizer( $this->addTablePrefix( $key ) );

		return $this->{$operator . 'Where'}( $this->raw( "{$key} IS {$prefix} NULL" ) );
	}

	public function join( $table, $key, $operator = null, $value = null, $type = 'inner' ) {
		if ( ! $key instanceof \Closure ) {
			$key = function ( $joinBuilder ) use ( $key, $operator, $value ) {
				$joinBuilder->on( $key, $operator, $value );
			};
		}

		// Build a new JoinBuilder class, keep it by reference so any changes made
		// in the closure should reflect here
		$joinBuilder = $this->container->build( '\\WpFluent\\QueryBuilder\\JoinBuilder', array( $this->connection ) );
		$joinBuilder = & $joinBuilder;
		// Call the closure with our new joinBuilder object
		$key( $joinBuilder );
		$table = $this->addTablePrefix( $table, false );
		// Get the criteria only query from the joinBuilder object
		$this->statements['joins'][] = compact( 'type', 'table', 'joinBuilder' );

		return $this;
	}

	/**
	 * Runs a transaction
	 *
	 * @param $callback
	 *
	 * @return $this
	 */
	public function transaction( \Closure $callback ) {
		try {
			// Begin the PDO transaction
			$this->db->query( 'START TRANSACTION' );

			// Get the Transaction class
			$transaction = $this->container->build(
				'\\WpFluent\\QueryBuilder\\Transaction',
				array( $this->connection )
			);

			// Call closure
			$callback( $transaction );

			// If no errors have been thrown or the transaction wasn't completed within
			// the closure, commit the changes
			$this->db->query( 'COMMIT' );

			return $this;
		} catch ( TransactionHaltException $e ) {
			// Commit or rollback behavior has been handled in the closure, so exit
			return $this;
		} catch ( \Exception $e ) {
			// something happened, rollback changes
			$this->db->query( 'ROLLBACK' );

			return $this;
		}
	}

	public function leftJoin( $table, $key, $operator = null, $value = null ) {
		return $this->join( $table, $key, $operator, $value, 'left' );
	}

	public function rightJoin( $table, $key, $operator = null, $value = null ) {
		return $this->join( $table, $key, $operator, $value, 'right' );
	}

	public function innerJoin( $table, $key, $operator = null, $value = null ) {
		return $this->join( $table, $key, $operator, $value, 'inner' );
	}

	public function raw( $value, $bindings = array() ) {
		return $this->container->build( '\\WpFluent\\QueryBuilder\\Raw', array( $value, $bindings ) );
	}

	public function db() {
		return $this->db;
	}

	public function setConnection( Connection $connection ) {
		$this->connection = $connection;

		return $this;
	}

	public function getConnection() {
		return $this->connection;
	}

	protected function whereHandler( $key, $operator = null, $value = null, $joiner = 'AND' ) {
		$key                          = $this->addTablePrefix( $key );
		$this->statements['wheres'][] = compact( 'key', 'operator', 'value', 'joiner' );

		return $this;
	}

	public function addTablePrefix( $values, $tableFieldMix = true ) {
		if ( is_null( $this->tablePrefix ) ) {
			return $values;
		}

		// $value will be an array and we will add prefix to all table names

		// If supplied value is not an array then make it one
		$single = false;

		if ( ! is_array( $values ) ) {
			$values = array( $values );
			// We had single value, so should return a single value
			$single = true;
		}

		$return = array();

		foreach ( $values as $key => $value ) {
			// It's a raw query, just add it to our return array and continue next
			if ( $value instanceof Raw || $value instanceof \Closure ) {
				$return[ $key ] = $value;
				continue;
			}

			// If key is not integer, it is likely a alias mapping,
			// so we need to change prefix target
			$target = &$value;
			if ( ! is_int( $key ) ) {
				$target = &$key;
			}

			if ( ! $tableFieldMix || ( $tableFieldMix && strpos( $target, '.' ) !== false ) ) {
				$target = $this->tablePrefix . $target;
			}

			$return[ $key ] = $value;
		}

		// If we had single value then we should return a single value (end value of the array)
		return $single ? end( $return ) : $return;
	}

	protected function addStatement( $key, $value ) {
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		if ( ! array_key_exists( $key, $this->statements ) ) {
			$this->statements[ $key ] = $value;
		} else {
			$this->statements[ $key ] = array_merge( $this->statements[ $key ], $value );
		}
	}

	public function getEvent( $event, $table = ':any' ) {
		return $this->connection->getEventHandler()->getEvent( $event, $table );
	}

	public function registerEvent( $event, $table, \Closure $action ) {
		$table = $table ? $table : ':any';

		if ( ':any' !== $table ) {
			$table = $this->addTablePrefix( $table, false );
		}

		$this->connection->getEventHandler()->registerEvent( $event, $table, $action );
	}

	public function removeEvent( $event, $table = ':any' ) {
		if ( ':any' !== $table ) {
			$table = $this->addTablePrefix( $table, false );
		}

		$this->connection->getEventHandler()->removeEvent( $event, $table );
	}

	public function fireEvents( $event ) {
		$params = func_get_args();
		array_unshift( $params, $this );

		return call_user_func_array(
			array( $this->connection->getEventHandler(), 'fireEvents' ),
			$params
		);
	}

	public function getStatements() {
		return $this->statements;
	}

	public function paginate( $perPage = null, $columns = array( '*' ) ) {
		$currentPage = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;

		$perPage = $perPage ? $perPage : intval( isset( $_REQUEST['per_page'] ) ? sanitize_text_field( $_REQUEST['per_page'] ) : 15 );

		$skip = $perPage * ( $currentPage - 1 );

		$data = (array) $this->select( $columns )->limit( $perPage )->offset( $skip )->get();

		$dataCount = count( $data );

		$from = $dataCount > 0 ? ( $currentPage - 1 ) * $perPage + 1 : null;

		$to = $dataCount > 0 ? $from + $dataCount - 1 : null;

		$total = $this->count();

		$lastPage = (int) ceil( $total / $perPage );

		return array(
			'current_page' => $currentPage,
			'per_page'     => $perPage,
			'from'         => $from,
			'to'           => $to,
			'last_page'    => $lastPage,
			'total'        => $total,
			'data'         => $data,
		);
	}

	/**
	 * Apply the callback's query changes if the given "value" is true.
	 *
	 * @param mixed $value
	 * @param callable $callback
	 * @param callable $default
	 * @return mixed
	 */
	public function when( $value, $callback, $default = null ) {
		if ( $value ) {
			$callback_response = $callback( $this, $value );
			return $callback_response ? $callback_response : $this;
		} elseif ( $default ) {
			$default_response = $default( $this, $value );
			return $default_response ? $default_response : $this;
		}

		return $this;
	}
}
