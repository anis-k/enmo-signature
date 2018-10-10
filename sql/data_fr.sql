
-- Create USERS
DELETE FROM users WHERE user_id <> 'superadmin';
DELETE FROM users WHERE user_id = 'rrenaud';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (1, 'rrenaud', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Robert', 'RENAUD', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'ssissoko';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (3, 'ssissoko', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Sylvain', 'SISSOKO', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'jjane';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (6, 'jjane', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Jenny', 'JANE', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'eerina';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (7, 'eerina', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Edith', 'ERINA', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'ppetit';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (10, 'ppetit', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Patricia', 'PETIT', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'sstar';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (14, 'sstar', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Suzanne', 'STAR', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'mmanfred';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (17, 'mmanfred', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Martin', 'MANFRED', 'info@maarch.org', 'Y', 'OK', 'standard');
DELETE FROM users WHERE user_id = 'ddaull';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES (18, 'ddaull', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Denis', 'DAULL', 'info@maarch.org', 'Y', 'OK', 'standard');
select setval('users_id_seq', (select max(id)+1 from users), false);

-- Create BASKETS
TRUNCATE TABLE baskets;
DELETE FROM baskets WHERE basket_id = 'MyBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, enabled) VALUES ('MyBasket', 'Courriers à traiter', 'Bannette des courriers à traiter', 'status in (''ANNOT'', ''SIGN'')', 'Y');

-- Donnees manuelles
------------
--DOCSERVERS
------------
TRUNCATE TABLE docserver_types;
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('DOC', 'Documents numériques', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('CONVERT', 'Conversions de formats', 'Y', 'SHA256');

TRUNCATE TABLE docservers;
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FASTHD_MAN', 'DOC', 'Dépôt documentaire de numérisation manuelle', 'N', 50000000000, 1290730, '/opt/maarchparapheur/docservers/manual/', '2011-01-13 14:47:49.197164', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FASTHD_ATTACH', 'DOC', 'Dépôt des pièces jointes', 'N', 50000000000, 1, '/opt/maarchparapheur/docservers/manual_attachments/', '2011-01-13 14:47:49.197164', 'attachments_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('CONVERT_MLB', 'CONVERT', 'Dépôt des formats des documents numérisés', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/convert_mlb/', '2015-03-16 14:47:49.197164', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('CONVERT_ATTACH', 'CONVERT', 'Dépôt des formats des pièces jointes', 'N', 50000000000, 0, '/opt/maarchparapheur/docservers/convert_attachments/', '2015-03-16 14:47:49.197164', 'attachments_coll');
------------
--SUPERADMIN USER
------------
DELETE FROM users WHERE user_id='superadmin';
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, enabled, status, loginmode) VALUES ('superadmin', '$2y$10$Vq244c5s2zmldjblmMXEN./Q2qZrqtGVgrbz/l1WfsUJbLco4E.e.', 'Super', 'ADMIN', '0147245159', 'info@maarch.org', 'Y', 'OK', 'standard');
--MAARCH2GEC USER
DELETE FROM users WHERE user_id = 'cchaplin';
INSERT INTO users (user_id, password, firstname, lastname, mail, enabled, status, loginmode) VALUES ('cchaplin', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Charlie', 'CHAPLIN', 'info@maarch.org', 'Y', 'OK', 'restMode');

------------
--STATUS-
------------
TRUNCATE TABLE status;
INSERT INTO status (id, label_status) VALUES ('ANNOT', 'Annotation');
INSERT INTO status (id, label_status) VALUES ('SIGN', 'Parapheur');
INSERT INTO status (id, label_status) VALUES ('VAL', 'Validé');
INSERT INTO status (id, label_status) VALUES ('REF', 'Refusé');
