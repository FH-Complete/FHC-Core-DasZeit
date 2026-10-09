<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This library contains the logic used to perform data synchronization between FHC and dasZeit
 */
class SyncZeitsperrenLib
{
	// Project types
	const DAS_ZEIT_MITARBEITER_ID = 'entityId';
	const DAS_ZEIT_SPERRETYP = 'absenceReason';
	const DAS_ZEIT_VON_DATUM = 'from';
	const DAS_ZEIT_BIS_DATUM = 'to';
	const DAS_ZEIT_STAMP_INDICATOR = 'isStamp';

	const DAS_ZEIT_DATE_FORMAT = 'Y-m-d\TH:i:s';
	const INSERT_VON = 'dasZeitSync';

	private $_ci; // Code igniter instance

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// Loads the LogLib with the needed parameters to log correctly from this library
		$this->_ci->load->library(
			'LogLib',
			array(
				'classIndex' => 3,
				'functionIndex' => 3,
				'lineIndex' => 2,
				'dbLogType' => 'job', // required
				'dbExecuteUser' => 'Cronjob system',
				'requestId' => 'JOB',
				'requestDataFormatter' => function($data) {
					return json_encode($data);
				}
			),
			'LogLibDasZeit'
		);

		// Load models
		$this->_ci->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->_ci->load->model('extensions/FHC-Core-DasZeit/Zeiterfassung_model', 'DasZeitZeiterfassungModel');
		$this->_ci->load->model('extensions/FHC-Core-DasZeit/synctables/DasZeitEntitiesSync_model', 'DasZeitEntitiesSyncModel');
		$this->_ci->load->model('extensions/FHC-Core-DasZeit/synctables/DasZeitAbsenceReasonSync_model', 'DasZeitAbsenceReasonSyncModel');
		$this->_ci->load->model('extensions/FHC-Core-DasZeit/synctables/DasZeitZeitsperrenSync_model', 'DasZeitZeitsperrenSyncModel');
	}

	// --------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Synchronize Zeitsperren between dasZeit and fhcomplete
	 * @param $von date, from which Zeitsperren should be synced
	 * @param $bis date, to which Zeitsperren should be synced
	 */
	public function syncZeitsperren($von, $bis)
	{
		if (!is_valid_date($von) || (isset($bis) && !is_valid_date($bis))) return error("Invalid date");

		$returnArr = ['errors' => [], 'noSaved' => 0, 'noDeleted' => 0];

		$dasZeitSperren = $this->_ci->DasZeitZeiterfassungModel->getZeiterfassung($von, $bis);

		if (isError($dasZeitSperren)) return $dasZeitSperren;
		if (!hasData($dasZeitSperren)) return success();

		// Get already synced Zeitsperren from fhc
		$syncedFhcSperren = [];
		$this->_ci->DasZeitZeitsperrenSyncModel->addSelect('zeitsperre_id, vondatum, bisdatum, zeitsperretyp_kurzbz, mitarbeiter_uid');
		$this->_ci->DasZeitZeitsperrenSyncModel->addJoin('campus.tbl_zeitsperre', 'zeitsperre_id');
		$this->_ci->DasZeitZeitsperrenSyncModel->db->where('vondatum >=', $von);
		if (isset($bis)) $this->_ci->DasZeitZeitsperrenSyncModel->db->where('bisdatum <=', $bis);
		$syncedFhcSperrenRes = $this->_ci->DasZeitZeitsperrenSyncModel->load();

		if (isError($syncedFhcSperrenRes)) return $syncedFhcSperrenRes;
		if (hasData($syncedFhcSperrenRes)) $syncedFhcSperren = getData($syncedFhcSperrenRes);

		$dasZeitSperren = getData($dasZeitSperren);

		foreach ($dasZeitSperren as $dasZeitSperre)
		{
			// check validity of Sperre
			if (!$this->_checkSperre($dasZeitSperre)) continue;

			// map Sperre to fhc format
			$fhcSperre = $this->_mapSperre($dasZeitSperre);

			if (isError($fhcSperre))
			{
				$returnArr['errors'][] = getError($fhcSperre);
				continue;
			}

			if (!hasData($fhcSperre)) continue;

			$fhcSperre = getData($fhcSperre);

			// finally, save Sperre in db
			$result = $this->_saveSperre($fhcSperre);

			if (isError($result))
			{
				$returnArr['errors'][] = getError($result);
			}
			else
			{
				$returnArr['noSaved']++;
			}

			// filter already synced sperren - if they are not in dasZeit anymore, they should be deleted!
			$syncedFhcSperren = array_filter($syncedFhcSperren, function($obj) use ($fhcSperre){
				return $obj->zeitsperretyp_kurzbz != $fhcSperre['zeitsperretyp_kurzbz']
					|| $obj->mitarbeiter_uid != $fhcSperre['mitarbeiter_uid']
					|| DateTime::createFromFormat('Y-m-d', $obj->vondatum) > DateTime::createFromFormat('Y-m-d', $fhcSperre['bisdatum'])
					|| DateTime::createFromFormat('Y-m-d', $obj->bisdatum) < DateTime::createFromFormat('Y-m-d', $fhcSperre['vondatum']);
			});
		}

		// delete Sperren, which are already deleted in dasZeit
		foreach ($syncedFhcSperren as $syncedSperre)
		{
			$result = $this->_ci->DasZeitZeitsperrenSyncModel->deleteZeitSperre($syncedSperre->zeitsperre_id);

			if (isError($result))
			{
				$returnArr['errors'][] = getError($result);
			}
			else
			{
				$returnArr['noDeleted']++;
			}
		}

		return success($returnArr);
	}

	/**
	 * Map Zeitsperre from dasZeit to fhcomplete Zeitsperre.
	 * @param $dasZeitSperre
	 * @return object success or error
	 */
	private function _mapSperre($dasZeitSperre)
	{
		$mappings = [
			'mitarbeiter_uid' => self::DAS_ZEIT_MITARBEITER_ID,
			'zeitsperretyp_kurzbz' => self::DAS_ZEIT_SPERRETYP,
			'bezeichnung' => 'info',
			'vondatum' => self::DAS_ZEIT_VON_DATUM,
			'bisdatum' => self::DAS_ZEIT_BIS_DATUM
		];

		$fhcZeitsperre = [];

		foreach ($mappings as $key => $value)
		{
			// check existence
			if (!property_exists($dasZeitSperre, $value)) return error("Field missing in dasZeit: $value");
			$fhcZeitsperre[$key] = $dasZeitSperre->{$value};
		}

		// map mitarbeiter id
		$result = $this->_ci->DasZeitEntitiesSyncModel->loadWhere(['daszeit_entity_id' => $dasZeitSperre->{self::DAS_ZEIT_MITARBEITER_ID}]);

		if (isError($result)) return $result;
		if (!hasData($result)) return error("Employee entity id not mapped: ".$dasZeitSperre->{self::DAS_ZEIT_MITARBEITER_ID});

		$fhcZeitsperre['mitarbeiter_uid'] = getData($result)[0]->mitarbeiter_uid;

		// map absence reason
		$result = $this->_ci->DasZeitAbsenceReasonSyncModel->loadWhere(['daszeit_absence_reason' => $dasZeitSperre->{self::DAS_ZEIT_SPERRETYP}]);

		if (isError($result)) return $result;
		if (!hasData($result)) return error("Absence reason id not mapped: ".$dasZeitSperre->{self::DAS_ZEIT_SPERRETYP});

		$fhcZeitsperre['zeitsperretyp_kurzbz'] = getData($result)[0]->zeitsperretyp_kurzbz;

		// change date format
		$d = DateTime::createFromFormat(self::DAS_ZEIT_DATE_FORMAT, $fhcZeitsperre['vondatum']);
		$fhcZeitsperre['vondatum'] = $d->format('Y-m-d');

		$d = DateTime::createFromFormat(self::DAS_ZEIT_DATE_FORMAT, $fhcZeitsperre['bisdatum']);
		$fhcZeitsperre['bisdatum'] = $d->format('Y-m-d');

		return success($fhcZeitsperre);
	}

	/**
	 * Save the Sperre in db
	 * @param $fhcZeitsperre
	 * @return object success or error
	 */
	private function _saveSperre($fhcZeitsperre)
	{
		// Check if Sperre already exists
		$this->_ci->ZeitsperreModel->addSelect('zeitsperre_id');
		$this->_ci->ZeitsperreModel->db->where('mitarbeiter_uid', $fhcZeitsperre['mitarbeiter_uid']);
		$this->_ci->ZeitsperreModel->db->where('zeitsperretyp_kurzbz', $fhcZeitsperre['zeitsperretyp_kurzbz']);
		$this->_ci->ZeitsperreModel->db->where('vondatum <=', $fhcZeitsperre['bisdatum']);
		$this->_ci->ZeitsperreModel->db->where('bisdatum >=', $fhcZeitsperre['vondatum']);
		$result = $this->_ci->ZeitsperreModel->load();

		if (isError($result)) return $result;

		$saveResult = null;

		if (hasData($result))
		{
			$zeitsperre = getData($result);
			if (count($zeitsperre) > 1)
			{
				return error(
					"More than one Zeitsperre found for type "
					.$fhcZeitsperre['zeitsperretyp_kurzbz'].", employee: ".$fhcZeitsperre['mitarbeiter_uid'].", date: ".$fhcZeitsperre['vondatum']." - ".$fhcZeitsperre['bisdatum']
				);
			}

			// Zeitsperre exists -> update
			$zeitsperre_id = $zeitsperre[0]->zeitsperre_id;
			$fhcZeitsperre['updatevon'] = self::INSERT_VON;
			$fhcZeitsperre['updateamum'] = "NOW()";
			$saveResult = $this->_ci->ZeitsperreModel->update(['zeitsperre_id' => $zeitsperre_id], $fhcZeitsperre);
		}
		else
		{
			// Zeitsperre does not exist yet -> insert
			$fhcZeitsperre['insertvon'] = self::INSERT_VON;
			$fhcZeitsperre['insertamum'] = "NOW()";
			$saveResult = $this->_ci->ZeitsperreModel->insert($fhcZeitsperre);

			// save in sync table if successful
			if (isSuccess($saveResult) && hasData($saveResult))
			{
				$syncTblRes = $this->_ci->DasZeitZeitsperrenSyncModel->insert(
					['zeitsperre_id' => getData($saveResult), 'insertvon' => self::INSERT_VON]
				);
				if (isError($syncTblRes)) return $syncTblRes;
			}
		}

		return $saveResult;
	}

	/**
	 * Checks for a Sperre, if it should be saved.
	 * @param $sperre
	 * @return boolean
	 */
	private function _checkSperre($sperre)
	{
		$checkedFields = [self::DAS_ZEIT_VON_DATUM, self::DAS_ZEIT_BIS_DATUM, self::DAS_ZEIT_STAMP_INDICATOR, self::DAS_ZEIT_SPERRETYP];

		foreach ($checkedFields as $field)
		{
			if (!isset($sperre->{$field})) return false;
		}
		
		$fromDate = DateTime::createFromFormat(self::DAS_ZEIT_DATE_FORMAT, $sperre->{self::DAS_ZEIT_VON_DATUM});
		$toDate = DateTime::createFromFormat(self::DAS_ZEIT_DATE_FORMAT, $sperre->{self::DAS_ZEIT_BIS_DATUM});

		// should not be a stamp, should have a sperretyp, should be whole-day-sperre (0 hours and minutes)
		return
			$sperre->{self::DAS_ZEIT_STAMP_INDICATOR} === false && $sperre->{self::DAS_ZEIT_SPERRETYP} !== null
			&& $fromDate->format('H:i:s') === "00:00:00" && $toDate->format('H:i:s') === "00:00:00";
	}
}
