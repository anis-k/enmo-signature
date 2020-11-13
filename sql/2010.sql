-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 20.03 to 20.10                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE main_documents DROP COLUMN IF EXISTS status;
ALTER TABLE main_documents ADD COLUMN status varchar(10);

ALTER TABLE main_documents DROP COLUMN IF EXISTS typist;
ALTER TABLE main_documents ADD COLUMN typist INTEGER;

DELETE FROM groups_privileges WHERE privilege = 'indexation';
INSERT INTO groups_privileges (group_id, privilege)
SELECT group_id, 'indexation' FROM groups_privileges WHERE privilege = 'manage_documents';

UPDATE main_documents SET status = 'READY';

DROP TABLE IF EXISTS workflow_templates;
CREATE TABLE workflow_templates
(
    id SERIAL NOT NULL,
    title text NOT NULL,
    owner INTEGER NOT NULL,
    CONSTRAINT workflow_templates_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS workflow_templates_items;
CREATE TABLE workflow_templates_items
(
    id SERIAL NOT NULL,
    workflow_template_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    mode CHARACTER VARYING(64) NOT NULL,
    signature_mode CHARACTER VARYING(64) NOT NULL,
    "order" INTEGER NOT NULL,
    CONSTRAINT workflow_templates_items_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DO $$ BEGIN
    IF (SELECT count(column_name) from information_schema.columns where table_name = 'workflows' and column_name = 'signature_mode') = 0 THEN
        ALTER TABLE workflows ADD COLUMN signature_mode CHARACTER VARYING(64);
        UPDATE workflows SET signature_mode = 'stamp';
        ALTER TABLE workflows ALTER COLUMN signature_mode set not null;
    END IF;
END$$;

ALTER TABLE users DROP COLUMN IF EXISTS signature_modes;
ALTER TABLE users ADD COLUMN signature_modes jsonb DEFAULT '["stamp"]';
