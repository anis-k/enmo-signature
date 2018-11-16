------------
--USERS
------------
TRUNCATE TABLE users;
ALTER SEQUENCE users_id_seq RESTART WITH 1;
INSERT INTO users (email, password, firstname, lastname, mode) VALUES ('jjane@maarch.com', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Jenny', 'JANE', 'standard');
INSERT INTO users (email, password, firstname, lastname, mode) VALUES ('ccornillac@maarch.com', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Clovis', 'CORNILLAC', 'rest');

------------
--DOCSERVERS
------------
TRUNCATE TABLE docservers;
ALTER SEQUENCE docservers_id_seq RESTART WITH 1;
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path)
VALUES ('DOC', 'Documents principaux', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/documents/');
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path)
VALUES ('ATTACH', 'Documents joints', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/attachments/');
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path)
VALUES ('HANDWRITTEN', 'Documents annotés ou signés', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/handwritten/');
INSERT INTO docservers (type, label, is_readonly, size_limit_number, actual_size_number, path)
VALUES ('SIGNATURE', 'Signatures utilisateurs', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/signatures/');

------------
--STATUS
------------
TRUNCATE TABLE status;
ALTER SEQUENCE status_id_seq RESTART WITH 1;
INSERT INTO status (reference, label) VALUES ('ANNOT', 'Annotation');
INSERT INTO status (reference, label) VALUES ('SIGN', 'Parapheur');
INSERT INTO status (reference, label) VALUES ('VAL', 'Validé');
INSERT INTO status (reference, label) VALUES ('REF', 'Refusé');
INSERT INTO status (reference, label) VALUES ('SIGNED', 'Signé');
INSERT INTO status (reference, label) VALUES ('REFSIGNED', 'Signature refusée');

------------
--ACTION
------------
TRUNCATE TABLE action;
ALTER SEQUENCE action_id_seq RESTART WITH 1;
INSERT INTO action (id, label, color, logo, event, previous_status_id, next_status_id) VALUES (1, 'Refuser signature', '#e74c3c', 'fas fa-backspace', 'openDialog', 2, 6);
INSERT INTO action (id, label, color, logo, event, previous_status_id, next_status_id) VALUES (2, 'Refuser', '#e74c3c', 'fas fa-backspace', 'openDialog', 1, 4);
INSERT INTO action (id, label, color, logo, event, previous_status_id, next_status_id) VALUES (3, 'Parapher', '#000', '', 'openDrawer', 2, null);
INSERT INTO action (id, label, color, logo, event, previous_status_id, next_status_id) VALUES (4, 'Annoter', '#2ecc71', '', 'openDrawer', 1, null);
INSERT INTO action (id, label, color, logo, event, previous_status_id, next_status_id) VALUES (5, 'Valider', '#2ecc71', 'fas fa-check-circle', 'confirmDialog', 1, 3);
INSERT INTO action (id, label, color, logo, event, previous_status_id, next_status_id) VALUES (6, 'Valider signature', '#2ecc71', 'fas fa-check-circle', 'confirmDialog', 2, 5);

-----
-- Password management
-----
TRUNCATE TABLE password_rules;
INSERT INTO password_rules (label, "value", enabled) VALUES ('minLength', 6, true);
INSERT INTO password_rules (label, "value") VALUES ('complexityUpper', 0);
INSERT INTO password_rules (label, "value") VALUES ('complexityNumber', 0);
INSERT INTO password_rules (label, "value") VALUES ('complexitySpecial', 0);
INSERT INTO password_rules (label, "value") VALUES ('lockAttempts', 3);
INSERT INTO password_rules (label, "value") VALUES ('lockTime', 5);
INSERT INTO password_rules (label, "value") VALUES ('historyLastUse', 2);
INSERT INTO password_rules (label, "value") VALUES ('renewal', 90);

/* Tests */
TRUNCATE TABLE main_documents;
TRUNCATE TABLE adr_main_documents;
TRUNCATE TABLE attachments;
TRUNCATE TABLE adr_attachments;
DO $$
BEGIN
FOR r in 1..500 LOOP
INSERT INTO main_documents (id, reference, subject, status, priority, sender, sender_entity, processing_user, recipient, creation_date) VALUES (r, '2018/A/' || r, 'Mon Courrier ' || r, 2, 'Urgent', 'Oliver Queen', 'QE', 1, 'Barry Allen', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) VALUES (r, 'DOC', 'tests/', 'test.pdf', '22948029580928509276290285908');

INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) VALUES (r, r, '2018/PJ/' || r, 'PJ 1', CURRENT_TIMESTAMP);

INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) VALUES (r, 'ATTACH', 'tests/', 'test_pj_1.pdf', '22948029580928509276290285908');
END LOOP;
END;
$$;