<?php

require_once APPPATH.'models/extensions/FHC-Core-DasZeit/ZeitClientModel.php';

/**
 * Implements the Zeit webservice calls for entities (employees)
 */
class Entities_model extends ZeitClientModel
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_apiSetName = 'entities';
	}

	// --------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets data of all entities (employees)
	 */
	public function getEntities()
	{
		return $this->_call(
			$this->_apiSetName,
			ZeitClientLib::HTTP_GET_METHOD
		);
	}
}
