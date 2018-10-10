-- res_letterbox
CREATE INDEX type_label_idx ON res_letterbox (type_id);
CREATE INDEX typist_idx ON res_letterbox (typist);
CREATE INDEX doc_date_idx ON res_letterbox (doc_date);
CREATE INDEX status_idx ON res_letterbox (status);
CREATE INDEX destination_idx ON res_letterbox (destination);
CREATE INDEX dest_user_idx ON res_letterbox (dest_user);
CREATE INDEX res_letterbox_docserver_id_idx ON res_letterbox (docserver_id);
CREATE INDEX res_letterbox_filename_idx ON res_letterbox (filename);

-- res_attachments
CREATE INDEX res_id_idx ON res_attachments (res_id);
CREATE INDEX res_id_master_idx ON res_attachments (res_id_master);

-- history
CREATE INDEX table_name_idx ON history (table_name);
CREATE INDEX record_id_idx ON history (record_id);
CREATE INDEX event_type_idx ON history (event_type);
CREATE INDEX user_id_idx ON history (user_id);

-- users
CREATE INDEX lastname_users_idx ON users (lastname);
