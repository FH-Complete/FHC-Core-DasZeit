CREATE TABLE IF NOT EXISTS extension.tbl_daszeit_zeitsperren (
	zeitsperre_id serial4 NOT NULL,
	insertamum TIMESTAMP DEFAULT NOW(),
	insertvon character varying(32)
);

COMMENT ON TABLE extension.tbl_daszeit_zeitsperren IS 'Zeitsperre synchronization table with DasZeit system';
COMMENT ON COLUMN extension.tbl_daszeit_zeitsperren.zeitsperre_id IS 'Zeitsperre id from FH Complete';

DO $$
BEGIN
	ALTER TABLE extension.tbl_daszeit_zeitsperren ADD CONSTRAINT tbl_daszeit_zeitsperren_pkey PRIMARY KEY (zeitsperre_id);
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

DO $$
BEGIN
	ALTER TABLE ONLY extension.tbl_daszeit_zeitsperren ADD CONSTRAINT tbl_daszeit_zeitsperren_zeitsperre_id_fkey FOREIGN KEY (zeitsperre_id) REFERENCES campus.tbl_zeitsperre(zeitsperre_id) ON UPDATE CASCADE ON DELETE RESTRICT;
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE extension.tbl_daszeit_zeitsperren TO vilesci;
GRANT SELECT ON TABLE extension.tbl_daszeit_zeitsperren TO web;