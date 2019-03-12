------------
--USERS
------------
TRUNCATE TABLE users;
ALTER SEQUENCE users_id_seq RESTART WITH 1;
INSERT INTO users (email, password, firstname, lastname, mode) VALUES ('jjane@maarch.com', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Jenny', 'JANE', 'standard');
INSERT INTO users (email, password, firstname, lastname, mode) VALUES ('ccornillac@maarch.com', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Clovis', 'CORNILLAC', 'rest');

------------
--GROUPS
------------
TRUNCATE TABLE groups;
ALTER SEQUENCE groups_id_seq RESTART WITH 1;
INSERT INTO groups (label) VALUES ('Administrateur');
INSERT INTO groups (label) VALUES ('Utilisateur Rest');

TRUNCATE TABLE groups_privileges;
ALTER SEQUENCE groups_privileges_id_seq RESTART WITH 1;
INSERT INTO groups_privileges (group_id, privilege) VALUES (1, 'manage_rest_users');
INSERT INTO groups_privileges (group_id, privilege) VALUES (2, 'manage_users');
INSERT INTO groups_privileges (group_id, privilege) VALUES (2, 'manage_documents');

TRUNCATE TABLE users_groups;
ALTER SEQUENCE users_groups_id_seq RESTART WITH 1;
INSERT INTO users_groups (group_id, user_id) VALUES (1, 1);
INSERT INTO users_groups (group_id, user_id) VALUES (2, 2);

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
ALTER SEQUENCE status_id_seq RESTART WITH 4;
INSERT INTO status (id, reference, label) VALUES (1, 'NEW', 'En cours');
INSERT INTO status (id, reference, label) VALUES (2 ,'VAL', 'Validé');
INSERT INTO status (id, reference, label) VALUES (3, 'REF', 'Refusé');

------------
--ACTIONS
------------
TRUNCATE TABLE actions;
ALTER SEQUENCE actions_id_seq RESTART WITH 1;
INSERT INTO actions (id, label, color, logo, event, mode, status_id, next_status_id) VALUES (1, 'Refuser', '#e74c3c', 'fas fa-backspace', 'refuseDocument', 'SIGN', 1, 3);
INSERT INTO actions (id, label, color, logo, event, mode, status_id, next_status_id) VALUES (2, 'Refuser', '#e74c3c', 'fas fa-backspace', 'refuseDocument', 'NOTE', 1, 3);
INSERT INTO actions (id, label, color, logo, event, mode, status_id, next_status_id) VALUES (3, 'Signatures', '#135F7F', '', 'openDrawer', 'SIGN', 1, null);
INSERT INTO actions (id, label, color, logo, event, mode, status_id, next_status_id) VALUES (4, 'Signatures', '#135F7F', '', 'openDrawer', 'NOTE', 1, null);
INSERT INTO actions (id, label, color, logo, event, mode, status_id, next_status_id) VALUES (5, 'Valider', '#2ecc71', 'fas fa-check-circle', 'validateDocument', 'SIGN', 1, 2);
INSERT INTO actions (id, label, color, logo, event, mode, status_id, next_status_id) VALUES (6, 'Valider', '#2ecc71', 'fas fa-check-circle', 'validateDocument', 'NOTE', 1, 2);

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
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) VALUES (r, '2018/A/' || r, 'Mon Courrier ' || r, 'SIGN', 1, 'Urgent', 'Oliver Queen', 'QE', 1, 'Barry Allen', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) VALUES (r, 'DOC', 'tests/', 'test.pdf', '5842a595983924f745e38c13195914d20420e85f2598e4fb3e11a491f89f545641e4995ab2fc54df0fec79be7b4fdb84e38322cd624061d754874e2c8178dcac');

INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) VALUES (r, r, '2018/PJ/' || r, 'PJ 1', CURRENT_TIMESTAMP);

INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) VALUES (r, 'ATTACH', 'tests/', 'test_pj_1.pdf', '5842a595983924f745e38c13195914d20420e85f2598e4fb3e11a491f89f545641e4995ab2fc54df0fec79be7b4fdb84e38322cd624061d754874e2c8178dcac');
END LOOP;
END;
$$;

ALTER SEQUENCE main_documents_id_seq RESTART WITH 501;
ALTER SEQUENCE attachments_id_seq RESTART WITH 501;

-----
-- Signatures
-----
TRUNCATE TABLE signatures;
INSERT INTO signatures VALUES (1, 1, 'tests/', '1.png', '8429de8819279f416f1427593798e7b8d22f92ae74b7635d74b450365aee90f7919540109e4b78179c19436dc673b47a7a4f133150c6a19ee5567c3a4b5cdf52');
INSERT INTO signatures VALUES (2, 1, 'tests/', '2.png', '1add86db218fe4a0eb2385c826cfb7c51163b3a7862803c918ad122b30ea75021598540170f8ea9a97d97e7ce34df17530ec9d239cd7cfdf516e55b75f694364');
INSERT INTO signatures VALUES (3, 1, 'tests/', '3.png', '01ae8ee839a4e65cdc2c56ca6c3cf419c4c4e3a48656107e3adfacdfb105f407aef2d4f01e159871cac4af2c971f25acbc6909da2c21ccde18e05d15761bd588');
INSERT INTO signatures VALUES (4, 1, 'tests/', '4.png', '0a17f4f63490851c07c757c22b19846f17b4f36476f30900417ee56261dcfac319a350f33505cf45abd3f51cf0d94fffb0858708696fd616b7dc2f38bb1ef217');
INSERT INTO signatures VALUES (5, 1, 'tests/', '5.png', '26206efefbe4f3bcf674f72588d88c9f779e6f41945d92f45e3a4267abc69c5500e5769a4dc72a28c6cc508fced24e73131471392a7e22f290f2ce896aee7fce');
INSERT INTO signatures VALUES (6, 1, 'tests/', '6.png', '05cddd650ce17dcd3e14869f7a6d8dbc3d5a079a66f61bb0cf5532d17c53f1a552d207284072f75eb7ab13466efe9a16b3150115663c4c53d4c19c9566948208');
INSERT INTO signatures VALUES (7, 1, 'tests/', '7.png', '9b10027df5a5c3730e7e8ae98117e5d597fff2e2777e175a7e8a8653fc8309ee74ab3221782fd2f551adef7ae18224107c5b4df7f0792b846106683bd258d6d6');
INSERT INTO signatures VALUES (8, 1, 'tests/', '8.png', '63fa29872286baf7ee9a295e6fce6c78e2a176ee5952b374dd5a448dac9cef42e393be7216584b3f3fefcaed8ffe60faa05210d200a6c02f5fe5f01480930db2');
INSERT INTO signatures VALUES (9, 1, 'tests/', '9.png', '06f847c16e32e4e2b761d30281e009d7621731606526e3b86aa0bd2672dbfa932bd55dbfc116b818c507c55f14d7940b87e1bab78bbbacea78178c330a89a289');
