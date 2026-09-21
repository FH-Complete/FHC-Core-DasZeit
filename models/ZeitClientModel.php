<?php

/**
 * Implements the dasZeit webservice calls
 */
abstract class ZeitClientModel extends CI_Model
{
	protected $_apiSetName; // to store the name of the api set name

	public $hasBadRequestError; // wether bad request error is returned
	public $hasNotFoundError; // wether not found request error is returned

	/**
	 *
	 */
	public function __construct()
	{
		// Loads the ZeitClientLib library
		$this->load->library('extensions/FHC-Core-DasZeit/ZeitClientLib');
	}

	// --------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Generic BIS webservice call
	 */
	protected function _call($wsFunction, $httpMethod, $uriParametersArray = array(), $callParametersArray = array())
	{
		// Checks if the property _apiSetName is valid
		if ($this->_apiSetName == null || trim($this->_apiSetName) == '')
		{
			$this->zeitclientlib->resetToDefault();

			return error('API set name not valid');
		}

		// Call the BIS webservice with the given parameters
		$wsResult = $this->zeitclientlib->call($this->_apiSetName, $wsFunction, $httpMethod, $uriParametersArray, $callParametersArray);

		// If an error occurred return it
		if ($this->zeitclientlib->isError())
		{
			$this->hasBadRequestError = $this->zeitclientlib->hasBadRequestError();
			$this->hasNotFoundError = $this->zeitclientlib->hasNotFoundError();
			$wsResult = error($this->zeitclientlib->getError(), $this->zeitclientlib->getErrorCode());
		}
		else // otherwise return a success
		{
			$wsResult = success($wsResult);
		}

		// Reset the zeitclientlib parameters
		$this->zeitclientlib->resetToDefault();

		// Return a success object that contains the web service result
		return $wsResult;
	}
}
