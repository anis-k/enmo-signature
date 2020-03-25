/* Tests */
TRUNCATE TABLE main_documents;
TRUNCATE TABLE adr_main_documents;
TRUNCATE TABLE attachments;
TRUNCATE TABLE adr_attachments;
TRUNCATE TABLE workflows;

-- MASS TEST
-- DO $$
-- BEGIN
-- FOR r in 1..500 LOOP
-- INSERT INTO main_documents (id, reference, title, sender, creation_date) VALUES (r, '2018/A/' || r, 'Mon Courrier ' || r, 'Oliver Queen', CURRENT_TIMESTAMP);
-- INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) VALUES (r, 'DOC', 'tests/', '1.pdf', '67e1cde83cf6710cb4f3644db6bb0ce67bf5cc23178f641065921e92b5c654a1d7e6595ebd0bbc211750a9e16c2fbf7dac81707021700c79c6eee7e14ed488e9');

-- INSERT INTO attachments (id, main_document_id, reference, title, creation_date) VALUES (r, r, '2018/PJ/' || r, 'PJ 1', CURRENT_TIMESTAMP);

-- INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) VALUES (r, 'ATTACH', 'tests/', '1.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
-- END LOOP;
-- END;
-- $$;

-- ALTER SEQUENCE main_documents_id_seq RESTART WITH 501;
-- ALTER SEQUENCE attachments_id_seq RESTART WITH 501;

-- DEMO WITH MAARCH COURRIER
-- LIST OF TO VISA/SIGN DOCS
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (1, 'MAARCH/2019D/1', 'Réponse à votre demande', 'Barbara Bain', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (1, 'DOC', 'tests/', 'ar_derogation.pdf', '9b0d6e8bf6e868d3a72c91660eab2c5d4b77c482672f6ae71b404d496e78d5856ba28f6f9a9ddb957ae16eece39ae7b3d04aa259fc58506d38edd18a6fbf578c');
INSERT INTO attachments (id, main_document_id, reference, title, creation_date)
VALUES (1, 1, 'MAARCH/2019A/1', 'Réponse dérogation carte scolaire', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (1, 'ATTACH', 'tests/', 'demande_derogation.pdf', '8a41c12d4b1885f6929a91f2f2e71fa11d5b76019337b705a9593576bf61dbc2ca4f6fe2b46596a22eeeb7244f2fb9b71594c4ef291b5092df8743747a0d738d');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (4, 1, 'visa', 1);
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (4, 1, 'sign', 2);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (2, 'MAARCH/2019D/2', 'Accusé de Réception de votre demande intervenant dans le cadre d’une décision implicite d’acceptation', 'Charlotte Charles', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (2, 'DOC', 'tests/', 'ar_sva.pdf', 'e723ec86ec468a30d981f626193ee7d96e4bd45dbf97daa1e03a6720ad9af1299fc60bc30e9e81886652d3af1d42876d37c27e6158493c23bd240abbada0c40c');
INSERT INTO attachments (id, main_document_id, reference, title, creation_date)
VALUES (2, 2, 'MAARCH/2019A/2', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (2, 'ATTACH', 'tests/', 'sva_route_66.pdf', '7ac968279579b547867964dd1d86b8cbc2bf9c9e6f68229cd42bbdbbcaded0062cb90759d31f08eef08bd8dc21d83912085adca87e27218dfceda5f7b56d6699');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (3, 2, 'visa', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (3, 'MAARCH/2019D/3', 'Accusé de Réception de votre demande intervenant dans le cadre d’une décision implicite de rejet', 'Robert Renaud', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (3, 'DOC', 'tests/', 'ar_svr.pdf', '6e6bb35e903c8a62dcf8e3983071398103da5b068c57e2fafd7670e84bb0cd33057d0126394675657e0547c0c602a8233c115c24c1ddfedd4bf8ee3acb5bf47c');
INSERT INTO attachments (id, main_document_id, reference, title, creation_date)
VALUES (3, 3, 'MAARCH/2019A/3', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (3, 'ATTACH', 'tests/', 'svr_route_chien_bruyant.pdf', '669b144a43f9b9c447e9a00794813ef95cd3b8392d5ec86f3aa8f2fbe65b6a3715fbddde8217165a3b1d7eb3b8e0eea040275a67dcab467bdc1c7783e146f18a');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (3, 3, 'visa', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (4, 'MAARCH/2019D/4', 'Invitation pour échanges journée des sports', 'Barbara Bain', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (4, 'DOC', 'tests/', 'invitation.pdf', 'd3d0f4d8cc3b4f9b5c2d0bd998737def4f769881a41d063f81d2e10166576e137ef294a81be563be04be44ce66c89897a51a4bca0755d0e3cae149501cf0d7aa');
INSERT INTO attachments (id, main_document_id, reference, title, creation_date)
VALUES (4, 4, 'MAARCH/2019D/4', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (4, 'ATTACH', 'tests/', 'test.pdf', '9510c93637f6baafbb9082a802aa6c3d1167f4cf6a02c432131de3a6f98d39cd3cac5f26d7b472e8fc2a50930a4e1ca89e8d9e839c81ef201198678aac3df45a');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (3, 4, 'visa', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (5, 'MAARCH/2019D/5', 'Votre demande de place en crèche', 'Sabrina Saporta', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (5, 'DOC', 'tests/', 'rep_creche.pdf', '4961004985ceeb0fddde9c9e982512da251e5d82ea072b955570f3539e14f88d22f4d3d2ab0e6979443766ec70a72d8d24298ed4a1e1cb9d76e5a19113504d3f');
INSERT INTO attachments (id, main_document_id, reference, title, creation_date)
VALUES (5, 5, 'MAARCH/2019A/4', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (5, 'ATTACH', 'tests/', 'demande_place_creche.pdf', 'ac4190f7b9a07204d15a3c0616b36be43de732a3c7a50d4c72f00eaa2e8bef19c5219b60e5e465ddf6d22a12dfb8157b97d47b96af0cd047296f3ddb7f79ad08');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (4, 5, 'sign', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (6, 'MAARCH/2019D/6', 'Mécontentement délais de réponses place crèche', 'Sabrina Saporta', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (6, 'DOC', 'tests/', 'rep_standard.pdf', 'c64b6cc725d947168b2236951452d4e64c9a9bb5ba41b9749b5b9990d4facfef83404d160a8b5db14c046ccae8c3fa21359c4b7a628fc55b526376578f1752da');
INSERT INTO attachments (id, main_document_id, reference, title, creation_date)
VALUES (6, 6, 'MAARCH/2019A/5', 'PJ 1', CURRENT_TIMESTAMP);
INSERT INTO adr_attachments (attachment_id, type, path, filename, fingerprint) 
VALUES (6, 'ATTACH', 'tests/', 'relance_place_creche.pdf', 'a252b97a682f84ae27ec3a03097d9be94c817904ff90eecccbe75afdf90768c5b1cc75aa8b358a8f1573e0622e4e11c3ec5a6f4d3d2f2beffae939df7bc31e7d');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (4, 6, 'sign', 1);

-- LIST OF ANNOTATE DOCS
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (7, 'MAARCH/2019A/6', 'Pétition pour la survie du square Carré', 'Robert Renaud', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (7, 'DOC', 'tests/', 'petition_square_carre.pdf', '3cfd864ce592a8f82c2098927369327d8dae27981e23f32ac187855369c61e87b6012717811c2654a2743b6892bb3bda1d6ac00ebf2747718f1017c00d6ece5c');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 7, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (8, 'MAARCH/2019A/7', 'Félicitations élections', 'Robert Renaud', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (8, 'DOC', 'tests/', 'felicitations.pdf', 'acddf0e9dca9f0e57dfc2b385865ab7b7fc815c47d242e5656247f3bf7caee3980717413a3c85d1ca7250db6e228b3498004d4ffd42f9e899ef837e50b3e76ac');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 8, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (9, 'MAARCH/2019A/8', 'Demande place creche', 'Sabrina Saporta', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (9, 'DOC', 'tests/', 'formulaire_place_creche.pdf', '8f8ac7c8bdb32c4b5cc1e8e4e96cf1f3cc9a303f893b1cc58c3650b561c50eb90c34ca246264d2751d897804146da8dea4f5a162b8f95eb3ab65b8447b2d0a6b');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 9, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (10, 'MAARCH/2019A/9', 'Demande subvention Jokkolabs', 'Robert Renaud', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (10, 'DOC', 'tests/', 'demande_subvention.pdf', '8e8bf316a63813491f528396628c537a5faabb2c4b3065987527bcacc02819fd265adeb6f9f887e6f604ab0028b2e294b1e1085f1899ca293092139d5036b69a');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 10, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (11, 'MAARCH/2019A/10', 'Facture Maarch', 'Suzanne Star', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (11, 'DOC', 'tests/', 'facture.pdf', '7d30cc8ac072240914e0d1a9346a37b4ea13f7404cc9a39b26e94eb66e57b1ce9a57a3625fbf524839637199cb3e291063e552e54cee22bdb45d9ffbb59ed887');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 11, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (12, 'MAARCH/2019A/11', 'Demande état civil', 'Robert Renaud', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (12, 'DOC', 'tests/', 'etat_civil.pdf', '957e5f08848c0cce768ee4b003e84098c569dd18f854748e080f2c258af55aff3186f9ae8458f2b202577699f2e0499f4ea9e43ccd2dacc78efe4d8e3488e863');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 12, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (13, 'MAARCH/2019A/12', 'Arrêt maladie vide', 'Pierre Pruvost', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (13, 'DOC', 'tests/', 'arret_maladie.pdf', '3f2b7ac317782515bb1864e2b05890f07a74d22e5cb54e04a99d966625eb2a5930e940745057a7f798a332a516e609c9f1440c72f328704c6d5ac3a4e742efae');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 13, 'note', 1);
--
INSERT INTO main_documents (id, reference, title, sender, creation_date)
VALUES (14, 'MAARCH/2019A/13', 'Inscription ecole', 'Sabrina Saporta', CURRENT_TIMESTAMP);
INSERT INTO adr_main_documents (main_document_id, type, path, filename, fingerprint) 
VALUES (14, 'DOC', 'tests/', 'inscription_ecole.pdf', '21af4bb4f8344988b8c85de24dd21444f554f40498147f675d4a138a354df29f093bc381747bcf070a5d019e926ebb01f934b3905d5e7907595bed1314074391');
INSERT INTO workflows (user_id, main_document_id, mode, "order") VALUES (1, 14, 'note', 1);
--

-- Thumbnails --
INSERT INTO adr_main_documents VALUES (15, 1, 'TNL1', 'tests/', '0001_924253347.png', 'a33c1afdb482f1e0bc2c4fdaa1b261a8a8dd061bd343eaffabbe1001fa92f09bdc8ec65a79dfec0f65db202a2641bde5fda0be6ac5f53dd5da5d2c39a06f21b7');
INSERT INTO adr_main_documents VALUES (16, 2, 'TNL1', 'tests/', '0002_914178073.png', 'a192a419135550578cb08dd7abd062ffca61a1ceeff32c453b9d14b1aecd5d2facc3fda7ed7d188647b5fbfbca466042a8862fb7850d6c1ebd95ce341f5d17f7');
INSERT INTO adr_main_documents VALUES (17, 2, 'TNL2', 'tests/', '0003_1372991890.png', '3f4c783fc2df2a9092dc87904886f3ae92e09243b72861622929859361759a37e0a01288083117f4be0890dc8bd6d79a2cae03542bfeac82879c874aedecb6ab');
INSERT INTO adr_main_documents VALUES (18, 3, 'TNL1', 'tests/', '0004_1493739617.png', '920d237256019128f2a2b548f5835403101237238322314565eb56456459ae27e4054a1a8f17bc6a948db6998b40af963c8c8d98b3a2f367a1c017bace2d718e');
INSERT INTO adr_main_documents VALUES (19, 3, 'TNL2', 'tests/', '0005_1251836316.png', '0fc11668d53f23de2c35f96f6bfa9ae3045215b775982fe180367bfd1b8e7990b4463321dfb72ab33140af665975ce7c8477c56bf572a97216aea281367eddbc');
INSERT INTO adr_main_documents VALUES (20, 4, 'TNL1', 'tests/', '0006_740234821.png', '905f226b414a91ad5dbf668104bec0dce053c2bcf386e8c5fc0ed17cc8297cab3d358e85e94acd72a43ac21d972765a4b7b1228c6a9a91d41f5d1aa2f6358714');
INSERT INTO adr_main_documents VALUES (21, 5, 'TNL1', 'tests/', '0007_260864315.png', '11c90dd6d942d33cd5c0b4fb54de3010e69d66b66d3104f535ee049fce3609c720c1b93e935f4be2b76e0d1adf11ddb770929a7feca621d73535386c64c7597c');
INSERT INTO adr_main_documents VALUES (22, 6, 'TNL1', 'tests/', '0008_54230775.png', '48dc7d020c4a058d3c528b9e116c9d12e5241fce3e804a25dee73243b9d9164912947d32f71e4e1bcf1480652a21f3abaf3c568b41c2cfa17c4bcace2e632c81');
INSERT INTO adr_main_documents VALUES (23, 7, 'TNL1', 'tests/', '0009_1079204807.png', '994d47cdf7bcf0a8c37198cdc2d6825646d58b9555aa585af34f9f0f87f1a7d1daf2cf7c6befd8aa665af76d6406bfc78a6fcb96fd925f864121fda0e8652dba');
INSERT INTO adr_main_documents VALUES (24, 8, 'TNL1', 'tests/', '0010_705380960.png', '09b202c528dc9735fcfffb26a94e9f7aae09fcf83b483aad841c62b5f79773d1a446f906e8d652f626c3cd8772b48e89f577ff8042dee6fc994da069f51dd054');
INSERT INTO adr_main_documents VALUES (25, 9, 'TNL1', 'tests/', '0011_1027862846.png', '9bebfe8dad605b5b231a72489b2628962ad15c11b6d342eac0e45e84537eaf38593af6c64000de1e06a2c779d33a4f9f638ec8938d3dd03d1124c56dbee4bf5c');
INSERT INTO adr_main_documents VALUES (26, 9, 'TNL2', 'tests/', '0012_170071085.png', 'dbb6a4f4b3e2377793fba9f0dd5f44bc3d795a7558ae63c3a3e27525b071790eb275e118a6644fcb1dbad903c5a890c038027203a2bb0d9114cf7653b076afd1');
INSERT INTO adr_main_documents VALUES (27, 10, 'TNL1', 'tests/', '0013_1896348238.png', '93d231e05ed0394e130b94359905b79bd8654a1730593a6663343a761d85345dae92160598f247d5102b02c60c12973a4767e29fc2d4fd86b39f0f8c76c97b6f');
INSERT INTO adr_main_documents VALUES (28, 11, 'TNL1', 'tests/', '0014_797656503.png', '1b0cad7ab160e0601458c3422248454bb1de45a88eca88c7275bcd0045e4dac7e7d7e7598cd156db0825e8e83943988d4376972021e627bb81ff584c86f1501c');
INSERT INTO adr_main_documents VALUES (29, 12, 'TNL1', 'tests/', '0015_1291509584.png', 'a29f424c9b2fc42631af5d4592e83bdf00a0a5806d230255dc50fcda371001feb52367febb0d6e5b053781c5244494e48e309f1d3bba5a0a9712e85f12b93b3d');
INSERT INTO adr_main_documents VALUES (30, 13, 'TNL1', 'tests/', '0016_1181140114.png', 'dc18508787da3ac7a069c9e95467e403851bbc5391e6c2c69177bc1c63ceef77b82df96b886072017698804344dcd39eccbe44a52ea6164528ac9bc6b0b55a08');
INSERT INTO adr_main_documents VALUES (31, 14, 'TNL1', 'tests/', '0017_1051851649.png', '2397a9cf25f2a295097ced456b8bf5e99517214819b56ae7eef1a9fa09d478e658abde3016fee635270f1b89f492089e8c7095a49ef670265731667e9eca52ce');
INSERT INTO adr_main_documents VALUES (32, 14, 'TNL2', 'tests/', '0018_1890918130.png', '34870d0b41b305fbf1c4df58334547bcc979ff03121661bf90c0c79c029acf57f10cc60f0700195e2a083e53cd0522a7733f96b5979ee0d476a61910f7dce46a');
INSERT INTO adr_main_documents VALUES (33, 14, 'TNL3', 'tests/', '0019_1234754257.png', 'f1f90dd83236ffbd2003e297dbf83f9b8bd43dbce541522c729f56b847d39c7cc9acd90bdb63279c4961aea5c0a88a33614475ff30ed5309ecb566d3b0032ba9');
INSERT INTO adr_attachments VALUES (7, 1, 'TNL1', 'tests/', '0001_1545338542.png', 'ca6201bbf45bc62cc674c989d1696da32813747102e64f1a5c577416d3b76adabce9a9a91d8f9445c13c07a3d4323ccc0752566369824671a30ab17cf5a0f540');
INSERT INTO adr_attachments VALUES (8, 2, 'TNL1', 'tests/', '0002_861991929.png', '88a70cb896fb40063f5e90327a509702e1abdc716983b88e7ea51c03242324ea6d507e146a492f6a2a552966299cc02cad8072d7556c9148fcbfe970a6a60f0e');
INSERT INTO adr_attachments VALUES (9, 3, 'TNL1', 'tests/', '0003_1572845301.png', '2461f70ee3f87f9cda355f296aa4ac5f67b8cc90d22984231087fb5fac0ec173cdb2a5a19da95ede4119ca6e04d37f33dc488e724e1fa3be765199546f300699');
INSERT INTO adr_attachments VALUES (10, 4, 'TNL1', 'tests/', '0004_552761620.png', 'dc577abdb884b09deadf6e00c1b73dc5df0bce99b138ffca0e0703fc59bd149535a407ffe23d13baa6dc3438d4125068e4b23b3a7978da5b4e22bd8f3e4d262c');
INSERT INTO adr_attachments VALUES (11, 5, 'TNL1', 'tests/', '0005_979446594.png', 'f87b91e923a72e3325f761bb8bc6f89987a3963d7937e725b54f1edbaead0ec1242df9fd777c90254dee40bf5ee7892f5ee6ff6743bd9d72830565917f1dd702');
INSERT INTO adr_attachments VALUES (12, 6, 'TNL1', 'tests/', '0006_503091136.png', 'd355f75a88d96dc85ce1e80f88a5800fb717131cd2b90134e04c5888f42d7fc8e519c61934ce6e25363758a1e027c58ba761b7025cf7db6cc6887d256bffc0eb');

ALTER SEQUENCE main_documents_id_seq RESTART WITH 1000;
ALTER SEQUENCE adr_main_documents_id_seq RESTART WITH 1000;
ALTER SEQUENCE attachments_id_seq RESTART WITH 1000;
ALTER SEQUENCE adr_attachments_id_seq RESTART WITH 1000;

-----
-- Signatures
-----
TRUNCATE TABLE signatures;
--mmanfred
INSERT INTO signatures VALUES (1, 3, 'tests/', '1.png', '8429de8819279f416f1427593798e7b8d22f92ae74b7635d74b450365aee90f7919540109e4b78179c19436dc673b47a7a4f133150c6a19ee5567c3a4b5cdf52');
--jjane
INSERT INTO signatures VALUES (2, 1, 'tests/', '2.png', '1add86db218fe4a0eb2385c826cfb7c51163b3a7862803c918ad122b30ea75021598540170f8ea9a97d97e7ce34df17530ec9d239cd7cfdf516e55b75f694364');
INSERT INTO signatures VALUES (3, 1, 'tests/', '3.png', '01ae8ee839a4e65cdc2c56ca6c3cf419c4c4e3a48656107e3adfacdfb105f407aef2d4f01e159871cac4af2c971f25acbc6909da2c21ccde18e05d15761bd588');
INSERT INTO signatures VALUES (4, 1, 'tests/', '4.png', '0a17f4f63490851c07c757c22b19846f17b4f36476f30900417ee56261dcfac319a350f33505cf45abd3f51cf0d94fffb0858708696fd616b7dc2f38bb1ef217');
INSERT INTO signatures VALUES (5, 1, 'tests/', '5.png', '26206efefbe4f3bcf674f72588d88c9f779e6f41945d92f45e3a4267abc69c5500e5769a4dc72a28c6cc508fced24e73131471392a7e22f290f2ce896aee7fce');
INSERT INTO signatures VALUES (6, 1, 'tests/', '6.png', '05cddd650ce17dcd3e14869f7a6d8dbc3d5a079a66f61bb0cf5532d17c53f1a552d207284072f75eb7ab13466efe9a16b3150115663c4c53d4c19c9566948208');
--mmanfred
INSERT INTO signatures VALUES (7, 3, 'tests/', '7.png', '9b10027df5a5c3730e7e8ae98117e5d597fff2e2777e175a7e8a8653fc8309ee74ab3221782fd2f551adef7ae18224107c5b4df7f0792b846106683bd258d6d6');
--ppetit
INSERT INTO signatures VALUES (8, 4, 'tests/', '8.png', '63fa29872286baf7ee9a295e6fce6c78e2a176ee5952b374dd5a448dac9cef42e393be7216584b3f3fefcaed8ffe60faa05210d200a6c02f5fe5f01480930db2');
INSERT INTO signatures VALUES (9, 4, 'tests/', '9.png', '06f847c16e32e4e2b761d30281e009d7621731606526e3b86aa0bd2672dbfa932bd55dbfc116b818c507c55f14d7940b87e1bab78bbbacea78178c330a89a289');
INSERT INTO signatures VALUES (10, 4, 'tests/', '10.png', '49163968e27e8232aa9d9fad817d78a233cb112dc49d0158644444d19f6c3b718731a442b401295bc02ea5af5f1dc93bed18b4709929ed3b882d87b8019ad8ff');
ALTER SEQUENCE signatures_id_seq RESTART WITH 1000;


