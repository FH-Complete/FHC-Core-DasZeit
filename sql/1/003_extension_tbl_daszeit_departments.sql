CREATE TABLE IF NOT EXISTS extension.tbl_daszeit_departments (
	oe_kurzbz character varying(8) NOT NULL,
	daszeit_department_id character varying(128) NOT NULL
);

COMMENT ON TABLE extension.tbl_daszeit_departments IS 'Departments synchronization table with DasZeit system';
COMMENT ON COLUMN extension.tbl_daszeit_departments.oe_kurzbz IS 'Department from FH Complete';
COMMENT ON COLUMN extension.tbl_daszeit_departments.daszeit_department_id IS 'Department from DasZeit';

DO $$
BEGIN
	ALTER TABLE extension.tbl_daszeit_departments ADD CONSTRAINT tbl_daszeit_departments_pkey PRIMARY KEY (oe_kurzbz, daszeit_department_id);
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

DO $$
BEGIN
	ALTER TABLE ONLY extension.tbl_daszeit_departments ADD CONSTRAINT tbl_daszeit_departments_oe_kurzbz_fkey FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
	EXCEPTION WHEN OTHERS THEN NULL;
END $$;

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE extension.tbl_daszeit_departments TO vilesci;
GRANT SELECT ON TABLE extension.tbl_daszeit_departments TO web;
