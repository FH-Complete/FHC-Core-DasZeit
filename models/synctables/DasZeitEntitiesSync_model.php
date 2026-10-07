<?php

class DasZeitEntitiesSync_model extends DB_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'extension.tbl_daszeit_entities';
		$this->pk = array('mitarbeiter_uid', 'daszeit_entity_id');
	}
}
