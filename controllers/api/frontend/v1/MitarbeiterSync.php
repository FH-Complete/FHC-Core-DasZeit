<?php

/**
 * Manages Employee import.
 */
class MitarbeiterSync extends FHCAPI_Controller
{
	private $_uid;
	private $_fields;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getMitarbeiterByUid' => array('admin:r','mitarbeiter/stammdaten:r'),
				'getDasZeitMitarbeiterByUid' => array('admin:r','mitarbeiter/stammdaten:r'),
				'getAllEntitities' => array('admin:r','mitarbeiter/stammdaten:r')
			)
		);

		// Loads models
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('extensions/FHC-Core-DasZeit/Entities_model', 'EntitiesModel');
		$this->load->model('extensions/FHC-Core-DasZeit/DasZeitMitarbeiter_model', 'DasZeitMitarbeiterModel');
		$this->load->model('extensions/FHC-Core-DasZeit/synctables/DasZeitEntitiesSync_model', 'DasZeitEntitiesSyncModel');

		// Load language phrases
		$this->loadPhrases([
			'dasZeit'
		]);

		// set uid of logged in user
		$this->_setAuthUID();

		$this->_fields =  [
			'mitarbeiter_uid' => ['dasZeitField' => 'id', 'displayName' => $this->p->t('dasZeit', 'id')],
			'personalnummer' => ['dasZeitField' => 'personnelNumber', 'displayName' => $this->p->t('dasZeit', 'personalnummer')],
			'vorname' => ['dasZeitField' => 'firstName', 'displayName' => $this->p->t('dasZeit', 'vorname')],
			'nachname' => ['dasZeitField' => 'lastName', 'displayName' => $this->p->t('dasZeit', 'nachname')],
			'email' => ['dasZeitField' => 'email', 'displayName' =>  $this->p->t('dasZeit', 'email')],
			'oe_kurzbz' => ['dasZeitField' => 'departmentId', 'displayName' => $this->p->t('dasZeit', 'abteilung')]
		];
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets Mitarbeiter
	 */
	public function getMitarbeiterByUid()
	{
		$mitarbeiter_uid = $this->input->get('mitarbeiter_uid');
		if (isEmptyString($mitarbeiter_uid)) $this->terminateWithError('Invalid Uid');

		$mitarbeiter = array();

		$mitarbeiterRes = $this->DasZeitMitarbeiterModel->getMitarbeiter($mitarbeiter_uid);

		if (isError($mitarbeiterRes)) $this->terminateWithError(getError($mitarbeiterRes));

		if (hasData($mitarbeiterRes)) $mitarbeiter = getData($mitarbeiterRes)[0];

		$result = [];

		foreach ($this->_fields as $fhcName => $infos)
		{
			if (isset($mitarbeiter->{$fhcName}) && isset($infos['displayName'])) $result[$infos['displayName']] = $mitarbeiter->{$fhcName};
		}

		$this->terminateWithSuccess($result);
	}

	/**
	 * Gets DasZeit Mitarbeiter
	 */
	public function getDasZeitMitarbeiterByUid()
	{
		$mitarbeiter_uid = $this->input->get('mitarbeiter_uid');
		if (isEmptyString($mitarbeiter_uid)) $this->terminateWithError('Invalid Uid');

		$mitarbeiter = array();

		$result = $this->DasZeitEntitiesSyncModel->loadWhere(['mitarbeiter_uid' => $mitarbeiter_uid]);

		if (!hasData($result)) return $this->terminateWithSuccess($this->p->t('dasZeit', 'mitarbeiterNichtUebertragen'));
		$dasZeitId = getData($result)[0]->daszeit_entity_id;

		// get employees
		$entitiesRes = $this->EntitiesModel->getEntities();

		if (isError($entitiesRes)) $this->terminateWithError(getError($entitiesRes));

		if (hasData($entitiesRes)) $mitarbeiter = getData($entitiesRes);

		$result = [];

		foreach ($mitarbeiter as $ma)
		{
			if ($ma->id == $dasZeitId)
			{
				foreach ($this->_fields as $fhcName => $infos)
				{
					if (isset($infos['displayName']) && isset($infos['dasZeitField'])) $result[$infos['displayName']] = $ma->{$infos['dasZeitField']};
				}
				return $this->terminateWithSuccess($result);
			}
		}
		
		$this->terminateWithSuccess("Employee not found");
	}

	public function getAllEntities()
	{
		// Loads model
		$this->load->model('extensions/FHC-Core-DasZeit/Entities_model', 'EntitiesModel');

		// get employees
		$entitiesRes = $this->EntitiesModel->getEntities();

		if (isError($entitiesRes)) $this->terminateWithError(getError($entitiesRes));

		if (hasData($entitiesRes)) $mitarbeiter = getData($entitiesRes);

		$this->terminateWithSuccess(
			array(
				'entities' => $entities
			)
		);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}
