/* Warning : This script erase all data in the application Maarch. It keeps in database parameters */

TRUNCATE TABLE history;
ALTER SEQUENCE history_id_seq restart WITH 1;

TRUNCATE TABLE main_documents;
ALTER SEQUENCE main_documents_id_seq restart WITH 1;

TRUNCATE TABLE attachments;
ALTER SEQUENCE attachments_id_seq restart WITH 1;

TRUNCATE TABLE users_signatures;
ALTER SEQUENCE users_signatures_id_seq restart WITH 1;
