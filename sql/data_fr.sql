------------
--USERS
------------
TRUNCATE TABLE users;
INSERT INTO users (login, password, firstname, lastname, mail, enabled, status, loginmode) VALUES ('jjane', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Jenny', 'JANE', 'info@maarch.org', 'Y', 'OK', 'standard');

------------
--DOCSERVERS
------------
TRUNCATE TABLE docservers;
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path, creation_date)
VALUES ('DOC', 'Documents principaux', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/documents/', CURRENT_TIMESTAMP);
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path, creation_date)
VALUES ('ATTACH', 'Documents joints', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/attachments/', CURRENT_TIMESTAMP);
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path, creation_date)
VALUES ('SIGNATURE', 'Signatures utilisateurs', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/signatures/', CURRENT_TIMESTAMP);

------------
--STATUS
------------
TRUNCATE TABLE status;
INSERT INTO status (reference, label) VALUES ('ANNOT', 'Annotation');
INSERT INTO status (reference, label) VALUES ('SIGN', 'Parapheur');
INSERT INTO status (reference, label) VALUES ('VAL', 'Validé');
INSERT INTO status (reference, label) VALUES ('REF', 'Refusé');

/* Tests */
TRUNCATE TABLE main_documents;
TRUNCATE TABLE adr_main_documents;
TRUNCATE TABLE attachments;
TRUNCATE TABLE adr_attachments;
DO $$
BEGIN
FOR r in 1..500 LOOP
INSERT INTO main_documents (id, reference, subject, doc_date, status, priority, sender, sender_entity, processing_user, recipient, creation_date) VALUES (r, '2018/A/' || r, 'Mon Courrier ' || r, CURRENT_TIMESTAMP, 2, 'Urgent', 'Oliver Queen', 'QE', 1, 'Barry Allen', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename) VALUES (r, 'DOC', 'tests/', 'test.pdf');

INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) VALUES (r, r, '2018/PJ/' || r, 'PJ 1', CURRENT_TIMESTAMP);

INSERT INTO adr_attachments (attachment_id, type, path, filename) VALUES (r, 'ATTACH', 'tests/', 'test_pj_1.pdf');
END LOOP;
END;
$$;