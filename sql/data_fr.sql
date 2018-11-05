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
VALUES ('DOC', 'Documents principaux', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/manual/', CURRENT_TIMESTAMP);

------------
--STATUS
------------
TRUNCATE TABLE status;
INSERT INTO status (reference, label) VALUES ('ANNOT', 'Annotation');
INSERT INTO status (reference, label) VALUES ('SIGN', 'Parapheur');
INSERT INTO status (reference, label) VALUES ('VAL', 'Validé');
INSERT INTO status (reference, label) VALUES ('REF', 'Refusé');

/* Tests */
INSERT INTO main_documents (reference, subject, doc_date, status, priority, sender, sender_entity, processing_user, recipient, creation_date) VALUES ('2018/A/1', 'Mon Courrier', CURRENT_TIMESTAMP, 2, 'Urgent', 'Oliver Queen', 'QE', 1, 'Barry Allen', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename) VALUES (1, 'DOC', 'tests/', 'test.pdf');
