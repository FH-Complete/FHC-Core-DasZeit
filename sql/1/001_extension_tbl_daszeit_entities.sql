CREATE TABLE IF NOT EXISTS extension.tbl_daszeit_entities (
	mitarbeiter_uid character varying(32) NOT NULL,
	daszeit_entity_id integer NOT NULL
);

COMMENT ON TABLE extension.tbl_daszeit_entities IS 'Employee synchronization table with DasZeit system';
COMMENT ON COLUMN extension.tbl_daszeit_entities.mitarbeiter_uid IS 'Employee id from FH Complete';
COMMENT ON COLUMN extension.tbl_daszeit_entities.daszeit_entity_id IS 'Entity id from DasZeit';

DO $$
BEGIN
	ALTER TABLE extension.tbl_daszeit_entities ADD CONSTRAINT tbl_daszeit_entities_pkey PRIMARY KEY (mitarbeiter_uid, daszeit_entity_id);
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

DO $$
BEGIN
	ALTER TABLE ONLY extension.tbl_daszeit_entities ADD CONSTRAINT tbl_daszeit_entities_mitarbeiter_uid_fkey FOREIGN KEY (mitarbeiter_uid) REFERENCES public.tbl_mitarbeiter(mitarbeiter_uid) ON UPDATE CASCADE ON DELETE RESTRICT;
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE extension.tbl_daszeit_entities TO vilesci;
GRANT SELECT ON TABLE extension.tbl_daszeit_entities TO web;
