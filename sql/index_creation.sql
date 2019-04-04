-- main_documents
CREATE INDEX main_documents_id_idx ON main_documents (id);
CREATE INDEX main_documents_processing_user_idx ON main_documents (processing_user);

-- attachments
CREATE INDEX attachments_id_idx ON attachments (id);
CREATE INDEX attachments_main_document_id_idx ON attachments (main_document_id);

-- history
CREATE INDEX history_object_type_idx ON history (object_type);
CREATE INDEX history_object_id_idx ON history (object_id);
CREATE INDEX history_user_id_idx ON history (user_id);

-- users
CREATE INDEX users_id_idx ON users (id);
CREATE INDEX users_login_idx ON users (login);
