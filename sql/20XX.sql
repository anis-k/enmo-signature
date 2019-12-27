-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.09 to 20.XX                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE main_documents DROP COLUMN IF EXISTS notes;
ALTER TABLE main_documents ADD COLUMN notes jsonb;
