-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.09 to 20.03                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE main_documents DROP COLUMN IF EXISTS notes;
ALTER TABLE main_documents ADD COLUMN notes jsonb;

ALTER TABLE main_documents DROP COLUMN IF EXISTS link_id;
ALTER TABLE main_documents ADD COLUMN link_id text;

ALTER TABLE signatures DROP COLUMN IF EXISTS substituted;
ALTER TABLE signatures ADD COLUMN substituted boolean DEFAULT FALSE NOT NULL;
