<?php
namespace WpFluent;

use Viocon\Container;

class Connection {


	protected $container;

	protected $adapter;

	protected $adapterConfig;

	protected $dbInstance;

	protected $wpdb;

	protected static $storedConnection;

	protected $eventHandler;

	public function __construct( $wpdb, array $config = array(), $alias = null, Container $container = null ) {
		$container = $container ? $container : new Container();

		$this->container = $container;

		$this->wpdb = $wpdb;

		$this->setAdapter()->setAdapterConfig( $config )->connect();

		// Create event dependency
		$this->eventHandler = $this->container->build( '\\WpFluent\\EventHandler' );

		if ( $alias ) {
			$this->createAlias( $alias );
		}
	}

	/**
	 * Create an easily accessible query builder alias
	 *
	 * @param $alias
	 */
	public function createAlias( $alias ) {
		class_alias( 'WpFluent\\AliasFacade', $alias );

		$builder = $this->container->build( '\\WpFluent\\QueryBuilder\\QueryBuilderHandler', array( $this ) );

		AliasFacade::setQueryBuilderInstance( $builder );
	}

	/**
	 * Returns an instance of Query Builder
	 */
	public function getQueryBuilder() {
		return $this->container->build( '\\WpFluent\\QueryBuilder\\QueryBuilderHandler', array( $this ) );
	}


	/**
	 * Create the connection adapter
	 */
	protected function connect() {
		$this->setDbInstance( $this->wpdb );

		// Preserve the first database connection with a static property
		if ( ! static::$storedConnection ) {
			static::$storedConnection = $this;
		}
	}

	public function setDbInstance( $db ) {
		$this->dbInstance = $db;

		return $this;
	}

	public function getDbInstance() {
		return $this->dbInstance;
	}

	public function setAdapter( $adapter = 'mysql' ) {
		$this->adapter = $adapter;

		return $this;
	}

	public function getAdapter() {
		return $this->adapter;
	}

	public function setAdapterConfig( array $adapterConfig ) {
		$this->adapterConfig = $adapterConfig;

		return $this;
	}

	public function getAdapterConfig() {
		return $this->adapterConfig;
	}

	public function getContainer() {
		return $this->container;
	}

	public function getEventHandler() {
		return $this->eventHandler;
	}

	public static function getStoredConnection() {
		return static::$storedConnection;
	}
}
