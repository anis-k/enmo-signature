-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.09 to 20.XX                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE main_documents DROP COLUMN IF EXISTS notes;
ALTER TABLE main_documents ADD COLUMN notes jsonb;

ALTER TABLE main_documents DROP COLUMN IF EXISTS link_id;
ALTER TABLE main_documents ADD COLUMN link_id text;
