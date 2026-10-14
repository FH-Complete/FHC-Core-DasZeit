INSERT INTO system.tbl_app (app) VALUES
('daszeit')
ON CONFLICT (app) DO NOTHING;