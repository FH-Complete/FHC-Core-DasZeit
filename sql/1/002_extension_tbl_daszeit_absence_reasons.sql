CREATE TABLE IF NOT EXISTS extension.tbl_daszeit_absence_reasons (
	zeitsperretyp_kurzbz character varying(8) NOT NULL,
	daszeit_absence_reason character varying(128) NOT NULL
);

COMMENT ON TABLE extension.tbl_daszeit_absence_reasons IS 'Absence reasons synchronization table with DasZeit system';
COMMENT ON COLUMN extension.tbl_daszeit_absence_reasons.zeitsperretyp_kurzbz IS 'Zeitsperre typ from FH Complete';
COMMENT ON COLUMN extension.tbl_daszeit_absence_reasons.daszeit_absence_reason IS 'Absence reason from DasZeit';

DO $$
BEGIN
	ALTER TABLE extension.tbl_daszeit_absence_reasons ADD CONSTRAINT tbl_daszeit_absence_reasons_pkey PRIMARY KEY (zeitsperretyp_kurzbz, daszeit_absence_reason);
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

DO $$
BEGIN
	ALTER TABLE ONLY extension.tbl_daszeit_absence_reasons ADD CONSTRAINT tbl_daszeit_absence_reasons_zeitsperretyp_kurzbz_fkey FOREIGN KEY (zeitsperretyp_kurzbz) REFERENCES campus.tbl_zeitsperre(zeitsperretyp_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE extension.tbl_daszeit_absence_reasons TO vilesci;
GRANT SELECT ON TABLE extension.tbl_daszeit_absence_reasons TO web;

INSERT INTO
	extension.tbl_daszeit_absence_reasons (zeitsperretyp_kurzbz, daszeit_absence_reason)
VALUES
	('Krank', 'Krankenstand'), ('Schulung', 'Aus- und Weiterbildung'), ('Arzt', 'Arztbesuch')
ON
	CONFLICT (zeitsperretyp_kurzbz, daszeit_absence_reason) DO NOTHING;