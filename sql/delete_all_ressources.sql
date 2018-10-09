/* Warning : This script erase all data in the application Maarch. It keeps in database parameters */
TRUNCATE TABLE cases;
ALTER SEQUENCE case_id_seq restart WITH 1;

TRUNCATE TABLE cases_res;

/*TRUNCATE TABLE contacts_v2;
ALTER SEQUENCE contact_v2_id_seq restart WITH 1;

TRUNCATE TABLE contact_addresses;
ALTER SEQUENCE contact_addresses_id_seq restart WITH 1;

TRUNCATE TABLE contact_types;
ALTER SEQUENCE contact_types_id_seq restart WITH 1;

TRUNCATE TABLE contact_purposes;
ALTER SEQUENCE contact_purposes_id_seq restart WITH 1;*/

TRUNCATE TABLE contacts_res;

TRUNCATE TABLE listinstance;
ALTER SEQUENCE listinstance_id_seq restart WITH 1;

TRUNCATE TABLE listinstance_history;
ALTER SEQUENCE listinstance_history_id_seq restart WITH 1;

TRUNCATE TABLE listinstance_history_details;
ALTER SEQUENCE listinstance_history_details_id_seq restart WITH 1;

TRUNCATE TABLE history;
ALTER SEQUENCE history_id_seq restart WITH 1;

TRUNCATE TABLE history_batch;
ALTER SEQUENCE history_batch_id_seq restart WITH 1;

TRUNCATE TABLE notes;
ALTER SEQUENCE notes_seq restart WITH 1;

TRUNCATE TABLE note_entities;

TRUNCATE TABLE mlb_coll_ext;

TRUNCATE TABLE res_letterbox;
ALTER SEQUENCE res_id_mlb_seq restart WITH 1;

TRUNCATE TABLE res_attachments;
ALTER SEQUENCE res_attachment_res_id_seq restart WITH 1;

TRUNCATE TABLE res_version_attachments;
TRUNCATE TABLE res_linked;
TRUNCATE TABLE res_mark_as_read;

TRUNCATE TABLE saved_queries;
TRUNCATE TABLE lc_stack;

TRUNCATE TABLE tags;
ALTER SEQUENCE tag_id_seq restart WITH 1;

TRUNCATE TABLE tags_entities;

TRUNCATE TABLE tag_res;

TRUNCATE TABLE sendmail;

TRUNCATE TABLE notif_event_stack;
ALTER SEQUENCE notif_event_stack_seq restart WITH 1;

TRUNCATE TABLE notif_email_stack;
ALTER SEQUENCE notif_email_stack_seq restart WITH 1;

TRUNCATE TABLE user_signatures;
ALTER SEQUENCE user_signatures_id_seq restart WITH 1;

/* reset chrono */
UPDATE parameters SET param_value_int = '1' WHERE id = 'chrono_outgoing_' || extract(YEAR FROM current_date);
UPDATE parameters SET param_value_int = '1' WHERE id = 'chrono_incoming_' || extract(YEAR FROM current_date);
UPDATE parameters SET param_value_int = '1' WHERE id = 'chrono_internal_' || extract(YEAR FROM current_date);
