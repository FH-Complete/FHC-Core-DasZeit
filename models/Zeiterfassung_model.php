<?php

require_once APPPATH.'models/extensions/FHC-Core-DasZeit/ZeitClientModel.php';

/**
 * Implements the Zeit webservice calls for zeiterfassung)
 */
class Zeiterfassung_model extends ZeitClientModel
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_apiSetName = 'reports/zeiterfassung';
	}

	// --------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets data
	 */
	public function getZeiterfassung($from, $to)
	{
		$params = array();
		if (isset($from)) $params['from'] = $from;
		if (isset($to)) $params['to'] = $to;
		return $this->_call(
			$this->_apiSetName,
			ZeitClientLib::HTTP_GET_METHOD,
			$params
		);
	}
}
