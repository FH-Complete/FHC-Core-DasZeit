<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Job to synchronize abscence data from 
 */
class ManageZeitsperren extends JOB_Controller
{
	/**
	 * Controller initialization
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads SyncBanksLib
		$this->load->library('extensions/FHC-Core-DasZeit/SyncZeitsperrenLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Save Zeitsperren data into the sync table
	 */
	public function syncZeitsperren($von, $bis = null)
	{
		$this->logInfo('Start Zeitsperren synchronization with dasZeit');

		// Synchronize active banks
		$syncResult = $this->synczeitsperrenlib->syncZeitsperren($von, $bis);

		// Log result
		if (isError($syncResult))
		{
			$this->logError(getCode($syncResult).': '.getError($syncResult));
		}
		else // otherwise
		{
			if (hasData($syncResult))
			{
				$result = getData($syncResult);
				if (isset($result['errors']) && !isEmptyArray($result['errors']))
				{
					$this->logError(implode(', ', $result['errors']));
				}
				if (isset($result['noSaved'])) $this->logInfo($result['noSaved'] . ' Zeitsperren gesynct');
			}
		}

		$this->logInfo('End Zeitsperren synchronization with dasZeit');
	}
}

