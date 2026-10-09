<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Example API
 */
class DasZeitTest extends JOB_Controller
{
	/**
	 * Controller initialization
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Example method
	 */
	public function runDasZeitExample()
	{
		// Loads model
		$this->load->model('extensions/FHC-Core-DasZeit/Entities_model', 'EntitiesModel');

		// test calls - get employees
		$entitiesRes = $this->EntitiesModel->getEntities();
		$entitiesRess = $this->EntitiesModel->getEntities();
	}
}
