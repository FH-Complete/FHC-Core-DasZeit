<?php

class DasZeitMitarbeiter_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbTable = 'public.tbl_person';
		$this->pk = 'person_id';

		//$this->load->model('person/kontakt_model', 'KontaktModel');
	}
	/**
	 * 
	 * @param
	 * @return object success or error
	 */
	public function getMitarbeiter($mitarbeiter_uid)
	{
		$qry = "SELECT
					mitarbeiter_uid, personalnummer, vorname, nachname,
					(
						SELECT
							STRING_AGG(kontakt, ', ' ORDER BY insertamum DESC)
						FROM
							public.tbl_kontakt
						WHERE
							kontakttyp = 'email'
							AND zustellung
							AND person_id = pers.person_id
					) AS email,
					akt_funk.oe_kurzbz
				FROM
					public.tbl_mitarbeiter ma
					JOIN public.tbl_benutzer ben ON mitarbeiter_uid = ben.uid
					JOIN public.tbl_person pers USING (person_id)
					LEFT JOIN public.tbl_benutzerfunktion akt_funk ON
						ma.mitarbeiter_uid = akt_funk.uid AND akt_funk.funktion_kurzbz = 'fachzuordnung'
						AND (akt_funk.datum_von IS NULL OR akt_funk.datum_von <= now())
						AND (akt_funk.datum_bis IS NULL OR akt_funk.datum_bis >= now())
				WHERE
					mitarbeiter_uid = ?";

		$params = [$mitarbeiter_uid];

		return $this->execQuery($qry, $params);
	}
}