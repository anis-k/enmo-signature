-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 20.03 to 20.10                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE main_documents DROP COLUMN IF EXISTS status;
ALTER TABLE main_documents ADD COLUMN status varchar(10);

UPDATE main_documents SET status = 'READY';
