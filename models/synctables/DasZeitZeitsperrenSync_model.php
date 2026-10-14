<?php

class DasZeitZeitsperrenSync_model extends DB_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'extension.tbl_daszeit_zeitsperren';
		$this->pk = array('zeitsperre_id');
		$this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
	}

	/**
	 * 
	 * @param
	 * @return object success or error
	 */
	public function deleteZeitSperre($zeitsperre_id)
	{
				// Start DB transaction
		$this->db->trans_start(false);
		
		$result = $this->delete(array('zeitsperre_id' => $zeitsperre_id));
		if (isSuccess($result))
		{
			$result = $this->ZeitsperreModel->delete(array('zeitsperre_id' => $zeitsperre_id));
		}
		
		// Transaction complete!
		$this->db->trans_complete();
		
		// Check if everything went ok during the transaction
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$result = error($result->msg, EXIT_ERROR);
		}
		else
		{
			$this->db->trans_commit();
			$result = success('Specialization successfully removed');
		}

		return $result;
	}
}
