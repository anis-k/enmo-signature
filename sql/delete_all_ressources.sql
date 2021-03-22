/* Warning : This script erase all data in the application Maarch. It keeps in database parameters */

TRUNCATE TABLE history;
ALTER SEQUENCE history_id_seq restart WITH 1;

TRUNCATE TABLE main_documents;
ALTER SEQUENCE main_documents_id_seq restart WITH 1;

TRUNCATE TABLE adr_main_documents;
ALTER SEQUENCE adr_main_documents_id_seq restart WITH 1;

TRUNCATE TABLE attachments;
ALTER SEQUENCE attachments_id_seq restart WITH 1;

TRUNCATE TABLE adr_attachments;
ALTER SEQUENCE adr_attachments_id_seq restart WITH 1;

TRUNCATE TABLE emails;
ALTER SEQUENCE emails_id_seq restart WITH 1;

TRUNCATE TABLE password_history;
ALTER SEQUENCE password_history_id_seq restart WITH 1;

TRUNCATE TABLE workflows;
ALTER SEQUENCE workflows_id_seq restart WITH 1;
