/* Warning : This script erase all data in the application Maarch. It keeps in database parameters */

TRUNCATE TABLE history;
ALTER SEQUENCE history_id_seq restart WITH 1;

TRUNCATE TABLE res_letterbox;
ALTER SEQUENCE res_id_mlb_seq restart WITH 1;

TRUNCATE TABLE res_attachments;
ALTER SEQUENCE res_attachment_res_id_seq restart WITH 1;

TRUNCATE TABLE user_signatures;
ALTER SEQUENCE user_signatures_id_seq restart WITH 1;
