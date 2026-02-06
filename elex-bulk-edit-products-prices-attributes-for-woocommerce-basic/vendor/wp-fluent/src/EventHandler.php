<?php namespace WpFluent;

use WpFluent\QueryBuilder\QueryBuilderHandler;
use WpFluent\QueryBuilder\Raw;

class EventHandler {

	protected $events = array();

	protected $firedEvents = array();

	public function getEvents() {
		return $this->events;
	}

	public function getEvent( $event, $table = ':any' ) {
		if ( $table instanceof Raw ) {
			return null;
		}
		return isset( $this->events[ $table ][ $event ] ) ? $this->events[ $table ][ $event ] : null;
	}

	public function registerEvent( $event, $table, \Closure $action ) {
		$table = $table ? $table : ':any';

		$this->events[ $table ][ $event ] = $action;
	}

	public function removeEvent( $event, $table = ':any' ) {
		unset( $this->events[ $table ][ $event ] );
	}

	public function fireEvents($queryBuilder, $event) {
		$statements = $queryBuilder->getStatements();
		$tables     = isset($statements['tables']) ? $statements['tables'] : array();
	
		// Events added with :any will be fired in case of any table,
		// we are adding :any as a fake table at the beginning.
		array_unshift($tables, ':any');
	
		// Capture original arguments
		$originalArgs = func_get_args();
	
		// Fire all events
		foreach ($tables as $table) {
			// Fire before events for :any table
			$action = $this->getEvent($event, $table);
			if ($action) {
				// Make an event id, with event type and table
				$eventId = $event . $table;
	
				// Fire event
				$handlerParams = $originalArgs;
				unset($handlerParams[1]); // we do not need $event
				// Add to fired list
				$this->firedEvents[] = $eventId;
				$result              = call_user_func_array($action, $handlerParams);
				if (!is_null($result)) {
					return $result;
				}
			}
		}
	}
	
}
