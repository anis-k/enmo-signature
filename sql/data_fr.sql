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

-- MASS TEST
-- DO $$
-- BEGIN
-- FOR r in 1..500 LOOP
-- INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) VALUES (r, '2018/A/' || r, 'Mon Courrier ' || r, 'SIGN', 1, 'Urgent', 'Oliver Queen', 'QE', 1, 'Barry Allen', CURRENT_TIMESTAMP);
-- INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) VALUES (r, 'DOC', 'tests/', '1.pdf', '67e1cde83cf6710cb4f3644db6bb0ce67bf5cc23178f641065921e92b5c654a1d7e6595ebd0bbc211750a9e16c2fbf7dac81707021700c79c6eee7e14ed488e9');

-- INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) VALUES (r, r, '2018/PJ/' || r, 'PJ 1', CURRENT_TIMESTAMP);

-- INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) VALUES (r, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
-- END LOOP;
-- END;
-- $$;

-- ALTER SEQUENCE main_documents_id_seq RESTART WITH 501;
-- ALTER SEQUENCE attachments_id_seq RESTART WITH 501;

-- DEMO WITH MAARCH COURRIER
--
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) 
VALUES (1, 'PJS/2019A/1234', 'Réponse à votre demande', 'SIGN', 1, 'Urgent', 'Barbara Bain', 'PE', 1, 'Bernard PASCONTENT', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (1, 'DOC', 'tests/', 'ar_derogation.pdf', '9b0d6e8bf6e868d3a72c91660eab2c5d4b77c482672f6ae71b404d496e78d5856ba28f6f9a9ddb957ae16eece39ae7b3d04aa259fc58506d38edd18a6fbf578c');
INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) 
VALUES (1, 1, 'PJS/2019D/1234', 'Réponse dérogation carte scolaire', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (1, 'ATTACH', 'tests/', 'demande_derogation.pdf', '8a41c12d4b1885f6929a91f2f2e71fa11d5b76019337b705a9593576bf61dbc2ca4f6fe2b46596a22eeeb7244f2fb9b71594c4ef291b5092df8743747a0d738d');
--
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) 
VALUES (2, 'SVA/2019A/12122', 'Accusé de Réception de votre demande intervenant dans le cadre d’une décision implicite d’acceptation', 'SIGN', 1, 'Urgent', 'Charlotte Charles', 'PT', 1, 'Bernard PASCONTENT', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (2, 'DOC', 'tests/', 'ar_sva.pdf', 'e723ec86ec468a30d981f626193ee7d96e4bd45dbf97daa1e03a6720ad9af1299fc60bc30e9e81886652d3af1d42876d37c27e6158493c23bd240abbada0c40c');
INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) 
VALUES (2, 2, '2018/PJ/', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (2, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
--
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) 
VALUES (3, 'SVR/2019A/12122', 'Accusé de Réception de votre demande intervenant dans le cadre d’une décision implicite de rejet', 'SIGN', 1, 'Urgent', 'Robert Reneau', 'DGS', 1, 'Bernard PASCONTENT', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (3, 'DOC', 'tests/', 'ar_svr.pdf', '6e6bb35e903c8a62dcf8e3983071398103da5b068c57e2fafd7670e84bb0cd33057d0126394675657e0547c0c602a8233c115c24c1ddfedd4bf8ee3acb5bf47c');
INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) 
VALUES (3, 3, '2018/PJ/', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (3, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
--
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) 
VALUES (4, 'PJS/2019D/1234', 'Invitation pour échanges journée des sports', 'SIGN', 1, 'Urgent', 'Barbara Bain', 'PJS', 1, 'Bernard PASCONTENT', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (4, 'DOC', 'tests/', 'invitation.pdf', 'd3d0f4d8cc3b4f9b5c2d0bd998737def4f769881a41d063f81d2e10166576e137ef294a81be563be04be44ce66c89897a51a4bca0755d0e3cae149501cf0d7aa');
INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) 
VALUES (4, 4, '2018/PJ/', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (4, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
--
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) 
VALUES (5, 'PE/2019A/1234', 'Votre demande de place en crèche', 'SIGN', 1, 'Urgent', 'Sabrina Saporta', 'PE', 1, 'Bernard PASCONTENT', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (5, 'DOC', 'tests/', 'rep_creche.pdf', '4961004985ceeb0fddde9c9e982512da251e5d82ea072b955570f3539e14f88d22f4d3d2ab0e6979443766ec70a72d8d24298ed4a1e1cb9d76e5a19113504d3f');
INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) 
VALUES (5, 5, '2018/PJ/', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (5, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
--
INSERT INTO main_documents (id, reference, subject, mode, status, priority, sender, sender_entity, processing_user, recipient, creation_date) 
VALUES (6, 'PE/2019A/1235', 'Mécontentement délais de réponses place crèche', 'SIGN', 1, 'Urgent', 'Sabrina Saporta', 'PE', 1, 'Bernard PASCONTENT', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (6, 'DOC', 'tests/', 'rep_standard.pdf', 'c64b6cc725d947168b2236951452d4e64c9a9bb5ba41b9749b5b9990d4facfef83404d160a8b5db14c046ccae8c3fa21359c4b7a628fc55b526376578f1752da');
INSERT INTO attachments (id, main_document_id, reference, subject, creation_date) 
VALUES (6, 6, '2018/PJ/', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (6, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');


ALTER SEQUENCE main_documents_id_seq RESTART WITH 7;
ALTER SEQUENCE attachments_id_seq RESTART WITH 7;

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
INSERT INTO signatures VALUES (10, 1, 'tests/', '10.png', '49163968e27e8232aa9d9fad817d78a233cb112dc49d0158644444d19f6c3b718731a442b401295bc02ea5af5f1dc93bed18b4709929ed3b882d87b8019ad8ff');
