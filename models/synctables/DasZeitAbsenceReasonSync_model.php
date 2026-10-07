<?php

class DasZeitAbsenceReasonSync_model extends DB_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'extension.tbl_daszeit_absence_reasons';
		$this->pk = array('zeitsperretyp_kurzbz', 'daszeit_absence_reason');
	}
}
