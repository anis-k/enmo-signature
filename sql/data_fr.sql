
-- Create USERGROUPS & USERGROUPS_SERVICES
TRUNCATE TABLE usergroups;
TRUNCATE TABLE usergroups_services;
DELETE FROM usergroups WHERE group_id = 'COURRIER';
DELETE FROM usergroups_services WHERE group_id = 'COURRIER';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('COURRIER', 'Opérateur de scan','Y');
DELETE FROM usergroups WHERE group_id = 'AGENT';
DELETE FROM usergroups_services WHERE group_id = 'AGENT';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('AGENT', 'Utilisateur','Y');
DELETE FROM usergroups WHERE group_id = 'RESP_COURRIER';
DELETE FROM usergroups_services WHERE group_id = 'RESP_COURRIER';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('RESP_COURRIER', 'Superviseur Courrier','Y');
DELETE FROM usergroups WHERE group_id = 'RESPONSABLE';
DELETE FROM usergroups_services WHERE group_id = 'RESPONSABLE';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('RESPONSABLE', 'Manager','Y');
DELETE FROM usergroups WHERE group_id = 'ADMINISTRATEUR';
DELETE FROM usergroups_services WHERE group_id = 'ADMINISTRATEUR';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('ADMINISTRATEUR', 'Admin. fonctionnel','Y');
DELETE FROM usergroups WHERE group_id = 'DIRECTEUR';
DELETE FROM usergroups_services WHERE group_id = 'DIRECTEUR';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('DIRECTEUR', 'Directeur','Y');
DELETE FROM usergroups WHERE group_id = 'ELU';
DELETE FROM usergroups_services WHERE group_id = 'ELU';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('ELU', 'Elu','Y');
DELETE FROM usergroups WHERE group_id = 'ARCHIVISTE';
DELETE FROM usergroups_services WHERE group_id = 'ARCHIVISTE';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('ARCHIVISTE', 'Archiviste','Y');
DELETE FROM usergroups WHERE group_id = 'MAARCHTOGEC';
DELETE FROM usergroups_services WHERE group_id = 'MAARCHTOGEC';
INSERT INTO usergroups (group_id,group_desc,enabled) VALUES ('MAARCHTOGEC', 'Envoi dématérialisé','Y');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'search_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'my_contacts_menu');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'create_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'update_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'reopen_mail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_full_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_links');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'print_doc_details_from_list');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'delete_document_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'edit_document_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_folder_tree');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'associate_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'folder_search');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'close_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'modify_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'folder_freeze');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'delete_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_copy_in_indexing_validation');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'entities_print_sep_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'use_mail_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'edit_attachments_from_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'modify_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'delete_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'view_version_letterbox');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'print_folder_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'add_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'create_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'private_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', '_print_sep');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'physical_archive_print_sep_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'physical_archive');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'admin_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'put_doc_in_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'notes_restriction');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('COURRIER', 'save_numeric_package');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'search_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'my_contacts_menu');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'update_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_links');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'print_doc_details_from_list');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'view_folder_tree');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'create_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'associate_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_copy_in_indexing_validation');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'use_mail_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'edit_attachments_from_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'modify_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'delete_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'view_version_letterbox');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'config_visa_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'config_visa_workflow_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'print_folder_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'config_avis_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'config_avis_workflow_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'private_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'put_doc_in_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'notes_restriction');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'thesaurus_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('AGENT', 'add_thesaurus_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'search_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'my_contacts_menu');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'update_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'view_full_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_links');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'print_doc_details_from_list');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'delete_document_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'edit_document_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'view_folder_tree');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'create_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'associate_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_copy_in_indexing_validation');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'use_mail_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'view_version_letterbox');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'print_folder_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'private_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'put_doc_in_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'notes_restriction');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'thesaurus_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESP_COURRIER', 'add_thesaurus_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'search_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'my_contacts_menu');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'update_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_links');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'print_doc_details_from_list');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'view_folder_tree');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'create_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'associate_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_copy_in_indexing_validation');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'use_mail_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'edit_attachments_from_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'modify_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'delete_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'view_version_letterbox');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'config_visa_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'config_visa_workflow_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'sign_document');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'visa_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'modify_visa_in_signatureBook');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'print_folder_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'config_avis_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'config_avis_workflow_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'avis_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'private_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'put_doc_in_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'notes_restriction');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'thesaurus_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('RESPONSABLE', 'add_thesaurus_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'search_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_users');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_groups');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_architecture');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_history_batch');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_status');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_actions');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'my_contacts_menu');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'create_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'reopen_mail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_docservers');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_full_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_links');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_parameters');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_priorities');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'print_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'print_doc_details_from_list');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'delete_document_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'edit_document_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'join_res_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'join_res_case_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'close_case');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_cases');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_folder_tree');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'create_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'associate_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'folder_search');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'close_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'modify_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'folder_freeze');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'delete_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_foldertypes');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'manage_entities');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_difflist_types');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_listmodels');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_copy_in_indexing_validation');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'update_list_diff_in_details');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'entities_print_sep_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'use_mail_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'graphics_reports');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'edit_attachments_from_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'modify_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'delete_attachments');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'view_version_letterbox');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'config_visa_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'config_visa_workflow_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'sign_document');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'visa_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'modify_visa_in_signatureBook');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'print_folder_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'config_avis_workflow');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_tag_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'create_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'private_tag');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_notif');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', '_print_sep');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'physical_archive_print_sep_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'physical_archive_batch_manage');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'physical_archive_batch_read');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'physical_archive');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'physical_archive_box_read');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'physical_archive_box_manage');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'put_doc_in_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_life_cycle');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'notes_restriction');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'admin_thesaurus');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'thesaurus_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEUR', 'add_thesaurus_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEUR', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'search_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'update_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'view_folder_tree');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'use_mail_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'add_new_version');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'view_version_letterbox');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'config_visa_workflow_in_detail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'sign_document');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'visa_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'modify_visa_in_signatureBook');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'print_folder_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'avis_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ELU', 'notes_restriction');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'create_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'view_technical_infos');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'view_doc_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'view_full_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'sendmail');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'avis_documents');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'tag_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'put_doc_in_fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'fileplan');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'export_seda_view');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTE', 'add_thesaurus_to_res');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('MAARCHTOGEC', 'save_numeric_package');

-- Create DOCTYPES
TRUNCATE TABLE DOCTYPES_FIRST_LEVEL;
TRUNCATE TABLE DOCTYPES_SECOND_LEVEL;
TRUNCATE TABLE DOCTYPES;
TRUNCATE TABLE MLB_DOCTYPE_EXT;
TRUNCATE TABLE DOCTYPES_INDEXES;
TRUNCATE TABLE TEMPLATES_DOCTYPE_EXT;
TRUNCATE TABLE FOLDERTYPES_DOCTYPES_LEVEL1;

INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, css_style, enabled) VALUES (1, 'COURRIERS', '#000000', 'Y');
INSERT INTO foldertypes_doctypes_level1 (foldertype_id, doctypes_first_level_id) VALUES (1, 1);
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (1, '01. Correspondances', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',101, 'Abonnements – documentation – archives', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (101, 30, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',102, 'Convocation', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (102, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',103, 'Demande de documents', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (103, 30, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',104, 'Demande de fournitures et matériels', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (104, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',105, 'Demande de RDV', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (105, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',106, 'Demande de renseignements', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (106, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',107, 'Demande mise à jour de fichiers', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (107, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',108, 'Demande Multi-Objet', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (108, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',109, 'Installation provisoire dans un équipement ville', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (109, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',110, 'Invitation', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (110, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',111, 'Rapport – Compte-rendu – Bilan', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (111, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',112, 'Réservation d''un local communal et scolaire', 'Y', 1, 1);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (112, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (2, '02. Cabinet', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',201, 'Pétition', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (201, 15, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',202, 'Communication', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (202, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',203, 'Politique', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (203, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',204, 'Relations et solidarité internationales ', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (204, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',205, 'Remerciements et félicitations', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (205, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',206, 'Sécurité', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (206, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',207, 'Suggestion', 'Y', 1, 2);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (207, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (3, '03. Éducation', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',301, 'Culture', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (301, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',302, 'Demande scolaire hors inscription et dérogation', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (302, 60, 14, 1, 'SVR');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',303, 'Éducation nationale', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (303, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',304, 'Jeunesse', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (304, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',305, 'Lycées et collèges', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (305, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',306, 'Parentalité', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (306, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',307, 'Petite Enfance', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (307, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',308, 'Sport', 'Y', 1, 3);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (308, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (4, '04. Finances', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',401, 'Contestation financière', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (401, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',402, 'Contrat de prêt', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (402, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',403, 'Garantie d''emprunt', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (403, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',404, 'Paiement', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (404, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',405, 'Quotient familial', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (405, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',406, 'Subvention', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (406, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',407, 'Facture ou avoir', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (407, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (407, 'letterbox_coll', 'custom_t1', 'Y');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (407, 'letterbox_coll', 'custom_t2', 'N');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (407, 'letterbox_coll', 'custom_f1', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',408, 'Proposition financière', 'Y', 1, 4);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (408, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (5, '05. Juridique', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',501, 'Hospitalisation d''office', 'Y', 1, 5);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (501, 2, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',502, 'Mise en demeure', 'Y', 1, 5);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (502, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',503, 'Plainte', 'Y', 1, 5);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (503, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',504, 'Recours contentieux', 'Y', 1, 5);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (504, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',505, 'Recours gracieux et réclamations', 'Y', 1, 5);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (505, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (6, '06. Population ', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',601, 'Débits de boisson', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (601, 60, 14, 1, 'SVR');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',602, 'Demande d’État Civil', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (602, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',603, 'Élections', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (603, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',604, 'Étrangers', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (604, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',605, 'Marché', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (605, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',606, 'Médaille du travail', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (606, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',607, 'Stationnement taxi', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (607, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',608, 'Vente au déballage', 'Y', 1, 6);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (608, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (7, '07. Ressources Humaines', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',701, 'Arrêts de travail et maladie', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (701, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (701, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',702, 'Assurance du personnel', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (702, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (702, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',703, 'Candidature', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (703, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',704, 'Carrière', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (704, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (704, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',705, 'Conditions de travail santé', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (705, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (705, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',706, 'Congés exceptionnels et concours', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (706, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (706, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',707, 'Formation', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (707, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (707, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',708, 'Instances RH', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (708, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (708, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',709, 'Retraite', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (709, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (709, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',710, 'Stage', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (710, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (710, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',711, 'Syndicats', 'Y', 1, 7);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (711, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_indexes (type_id, coll_id, field_name, mandatory) VALUES (711, 'letterbox_coll', 'custom_t3', 'N');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (8, '08. Social', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',801, 'Aide à domicile', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (801, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',802, 'Aide Financière', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (802, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',803, 'Animations retraités', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (803, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',804, 'Domiciliation', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (804, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',805, 'Dossier de logement', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (805, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',806, 'Expulsion', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (806, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',807, 'Foyer', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (807, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',808, 'Obligation alimentaire', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (808, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',809, 'RSA', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (809, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',810, 'Scolarisation à domicile', 'Y', 1, 8);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (810, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (9, '09. Technique', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',901, 'Aire d''accueil des gens du voyage', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (901, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',902, 'Assainissement', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (902, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',903, 'Assurance et sinistre', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (903, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',904, 'Autorisation d''occupation du domaine public', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (904, 60, 14, 1, 'SVR');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',905, 'Contrat et convention hors marchés publics', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (905, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',906, 'Détention de chiens dangereux', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (906, 60, 14, 1, 'SVR');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',907, 'Espaces verts – Environnement – Développement durable', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (907, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',908, 'Hygiène et Salubrité', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (908, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',909, 'Marchés Publics', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (909, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',910, 'Mobiliers urbains', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (910, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',911, 'NTIC', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (911, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',912, 'Opération d''aménagement', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (912, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',913, 'Patrimoine', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (913, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',914, 'Problème de voisinage', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (914, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',915, 'Propreté', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (915, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',916, 'Stationnement et circulation', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (916, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',917, 'Transports', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (917, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',918, 'Travaux', 'Y', 1, 9);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (918, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (10, '10. Urbanisme', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1001, 'Alignement', 'Y', 1, 10);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1001, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1002, 'Avis d''urbanisme', 'Y', 1, 10);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1002, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1003, 'Commerces', 'Y', 1, 10);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1003, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1004, 'Numérotation', 'Y', 1, 10);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1004, 60, 14, 1, 'NORMAL');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (11, '11. Silence vaut acceptation', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1101, 'Autorisation de buvette', 'Y', 1, 11);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1101, 60, 14, 1, 'SVA');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1102, 'Cimetière', 'Y', 1, 11);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1102, 60, 14, 1, 'SVA');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1103, 'Demande de dérogation scolaire', 'Y', 1, 11);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1103, 60, 14, 1, 'SVA');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1104, 'Inscription à la cantine et activités périscolaires ', 'Y', 1, 11);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1104, 60, 14, 1, 'SVA');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1105, 'Inscription toutes petites sections', 'Y', 1, 11);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1105, 90, 14, 1, 'SVA');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1106, 'Travaux ERP', 'Y', 1, 11);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1106, 60, 14, 1, 'SVA');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, css_style, enabled) VALUES (12, '12. Formulaires', 1, '#000000', 'Y');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1201, 'Appel téléphonique', 'Y', 1, 12);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1201, 21, 14, 1, 'NORMAL');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id) VALUES ('letterbox_coll',1202, 'Demande intervention voirie', 'Y', 1, 12);
INSERT INTO mlb_doctype_ext(type_id, process_delay, delay1, delay2, process_mode) VALUES (1202, 21, 14, 1, 'NORMAL');
select setval('doctypes_first_level_id_seq', (select max(doctypes_first_level_id)+1 from doctypes_first_level), false);
select setval('doctypes_second_level_id_seq', (select max(doctypes_second_level_id)+1 from doctypes_second_level), false);
select setval('doctypes_type_id_seq', (select max(type_id)+1 from doctypes), false);

-- Create USERS
DELETE FROM users WHERE user_id <> 'superadmin';
TRUNCATE TABLE users_entities;
DELETE FROM users WHERE user_id = 'rrenaud';
DELETE FROM users_entities WHERE user_id = 'rrenaud';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (1, 'rrenaud', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Robert', 'RENAUD', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('rrenaud', 'DGS', '', 'Y');
DELETE FROM users WHERE user_id = 'ccordy';
DELETE FROM users_entities WHERE user_id = 'ccordy';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (2, 'ccordy', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Chloé', 'CORDY', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ccordy', 'DSI', '', 'Y');
DELETE FROM users WHERE user_id = 'ssissoko';
DELETE FROM users_entities WHERE user_id = 'ssissoko';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (3, 'ssissoko', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Sylvain', 'SISSOKO', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssissoko', 'DSI', '', 'Y');
DELETE FROM users WHERE user_id = 'nnataly';
DELETE FROM users_entities WHERE user_id = 'nnataly';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (4, 'nnataly', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Nancy', 'NATALY', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('nnataly', 'PSO', '', 'Y');
DELETE FROM users WHERE user_id = 'ddur';
DELETE FROM users_entities WHERE user_id = 'ddur';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (5, 'ddur', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Dominique', 'DUR', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddur', 'ELUS', '', 'Y');
DELETE FROM users WHERE user_id = 'jjane';
DELETE FROM users_entities WHERE user_id = 'jjane';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (6, 'jjane', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Jenny', 'JANE', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjane', 'CCAS', '', 'Y');
DELETE FROM users WHERE user_id = 'eerina';
DELETE FROM users_entities WHERE user_id = 'eerina';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (7, 'eerina', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Edith', 'ERINA', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('eerina', 'CAB', '', 'Y');
DELETE FROM users WHERE user_id = 'kkaar';
DELETE FROM users_entities WHERE user_id = 'kkaar';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (8, 'kkaar', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Katy', 'KAAR', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('kkaar', 'DGA', '', 'Y');
DELETE FROM users WHERE user_id = 'bboule';
DELETE FROM users_entities WHERE user_id = 'bboule';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (9, 'bboule', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Bruno', 'BOULE', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bboule', 'PCU', '', 'Y');
DELETE FROM users WHERE user_id = 'ppetit';
DELETE FROM users_entities WHERE user_id = 'ppetit';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (10, 'ppetit', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Patricia', 'PETIT', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ppetit', 'VILLE', '', 'Y');
DELETE FROM users WHERE user_id = 'aackermann';
DELETE FROM users_entities WHERE user_id = 'aackermann';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (11, 'aackermann', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Amanda', 'ACKERMANN', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('aackermann', 'PSF', '', 'Y');
DELETE FROM users WHERE user_id = 'ppruvost';
DELETE FROM users_entities WHERE user_id = 'ppruvost';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (12, 'ppruvost', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Pierre', 'PRUVOST', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ppruvost', 'DRH', '', 'Y');
DELETE FROM users WHERE user_id = 'ttong';
DELETE FROM users_entities WHERE user_id = 'ttong';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (13, 'ttong', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Tony', 'TONG', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ttong', 'SP', '', 'Y');
DELETE FROM users WHERE user_id = 'sstar';
DELETE FROM users_entities WHERE user_id = 'sstar';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (14, 'sstar', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Suzanne', 'STAR', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('sstar', 'FIN', '', 'Y');
DELETE FROM users WHERE user_id = 'ssaporta';
DELETE FROM users_entities WHERE user_id = 'ssaporta';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (15, 'ssaporta', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Sabrina', 'SAPORTA', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssaporta', 'PE', '', 'Y');
DELETE FROM users WHERE user_id = 'ccharles';
DELETE FROM users_entities WHERE user_id = 'ccharles';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (16, 'ccharles', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Charlotte', 'CHARLES', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ccharles', 'PTE', '', 'Y');
DELETE FROM users WHERE user_id = 'mmanfred';
DELETE FROM users_entities WHERE user_id = 'mmanfred';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (17, 'mmanfred', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Martin', 'MANFRED', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('mmanfred', 'DGA', '', 'Y');
DELETE FROM users WHERE user_id = 'ddaull';
DELETE FROM users_entities WHERE user_id = 'ddaull';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (18, 'ddaull', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Denis', 'DAULL', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddaull', 'DSG', '', 'Y');
DELETE FROM users WHERE user_id = 'bbain';
DELETE FROM users_entities WHERE user_id = 'bbain';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (19, 'bbain', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Barbara', 'BAIN', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bbain', 'PJS', '', 'Y');
DELETE FROM users WHERE user_id = 'jjonasz';
DELETE FROM users_entities WHERE user_id = 'jjonasz';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (20, 'jjonasz', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Jean', 'JONASZ', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjonasz', 'PJU', '', 'Y');
DELETE FROM users WHERE user_id = 'bblier';
DELETE FROM users_entities WHERE user_id = 'bblier';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (21, 'bblier', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Bernard', 'BLIER', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bblier', 'COU', '', 'Y');
DELETE FROM users WHERE user_id = 'ggrand';
DELETE FROM users_entities WHERE user_id = 'ggrand';
INSERT INTO users (id, user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES (22, 'ggrand', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Georges', 'GRAND', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ggrand', 'COR', '', 'Y');
select setval('users_id_seq', (select max(id)+1 from users), false);

-- Create USERGROUP_CONTENT
TRUNCATE TABLE usergroup_content;
DELETE FROM usergroup_content WHERE user_id = 'rrenaud';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('rrenaud', 'RESPONSABLE', 'Y','');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('rrenaud', 'DIRECTEUR', 'N','');
DELETE FROM usergroup_content WHERE user_id = 'ccordy';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ccordy', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ssissoko';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssissoko', 'RESPONSABLE', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'nnataly';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('nnataly', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ddur';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddur', 'ELU', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'jjane';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('jjane', 'RESPONSABLE', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'eerina';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('eerina', 'RESPONSABLE', 'Y','');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('eerina', 'DIRECTEUR', 'N','');
DELETE FROM usergroup_content WHERE user_id = 'kkaar';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('kkaar', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'bboule';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bboule', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ppetit';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppetit', 'RESPONSABLE', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'aackermann';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('aackermann', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ppruvost';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ppruvost', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ttong';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ttong', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'sstar';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('sstar', 'RESPONSABLE', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ssaporta';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ssaporta', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ccharles';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ccharles', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'mmanfred';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('mmanfred', 'RESPONSABLE', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'ddaull';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddaull', 'COURRIER', 'N','');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddaull', 'RESP_COURRIER', 'N','');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ddaull', 'RESPONSABLE', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'bbain';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bbain', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'jjonasz';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('jjonasz', 'AGENT', 'Y','');
DELETE FROM usergroup_content WHERE user_id = 'bblier';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bblier', 'COURRIER', 'Y','');
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('bblier', 'ADMINISTRATEUR', 'N','');
DELETE FROM usergroup_content WHERE user_id = 'ggrand';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('ggrand', 'ARCHIVISTE', 'Y','');

-- Create ENTITIES and LISTMODELS
TRUNCATE TABLE entities;
TRUNCATE TABLE listmodels;
DELETE FROM entities WHERE entity_id = 'VILLE';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('VILLE', 'Ville de Maarch-les-bains', 'Ville de Maarch-les-bains', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', '', 'Direction');
DELETE FROM listmodels WHERE object_id = 'VILLE' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('VILLE', 'entity_id', 0, '', 'user_id', 'dest', 'Ville de Maarch-les-bains','Ville de Maarch-les-bains', '', 'Y');
DELETE FROM entities WHERE entity_id = 'CAB';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('CAB', 'Cabinet du Maire', 'Cabinet du Maire', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'VILLE', 'Direction');
DELETE FROM listmodels WHERE object_id = 'CAB' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('CAB', 'entity_id', 0, 'eerina', 'user_id', 'dest', 'Cabinet du Maire','Cabinet du Maire', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'CAB', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Cabinet du Maire','Cabinet du Maire', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'CAB', 'entity_id', 0, 'ppetit', 'user_id', 'cc', 'Cabinet du Maire','Cabinet du Maire', '', 'Y');
DELETE FROM entities WHERE entity_id = 'DGS';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGS', 'Direction Générale des Services', 'Direction Générale des Services', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'VILLE', 'Direction');
DELETE FROM listmodels WHERE object_id = 'DGS' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('DGS', 'entity_id', 0, 'rrenaud', 'user_id', 'dest', 'Direction Générale des Services','Direction Générale des Services', '', 'Y');
DELETE FROM entities WHERE entity_id = 'DGA';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGA', 'Direction Générale Adjointe', 'Direction Générale Adjointe', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGS', 'Bureau');
DELETE FROM listmodels WHERE object_id = 'DGA' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('DGA', 'entity_id', 0, 'mmanfred', 'user_id', 'dest', 'Direction Générale Adjointe','Direction Générale Adjointe', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'DGA', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Direction Générale Adjointe','Direction Générale Adjointe', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'DGA', 'entity_id', 0, 'kkaar', 'user_id', 'cc', 'Direction Générale Adjointe','Direction Générale Adjointe', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PCU';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PCU', 'Pôle Culturel', 'Pôle Culturel', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGA', 'Service');
DELETE FROM listmodels WHERE object_id = 'PCU' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PCU', 'entity_id', 0, 'bboule', 'user_id', 'dest', 'Pôle Culturel','Pôle Culturel', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PCU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Pôle Culturel','Pôle Culturel', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PJS';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJS', 'Pôle Jeunesse et Sport', 'Pôle Jeunesse et Sport', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGA', 'Service');
DELETE FROM listmodels WHERE object_id = 'PJS' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PJS', 'entity_id', 0, 'bbain', 'user_id', 'dest', 'Pôle Jeunesse et Sport','Pôle Jeunesse et Sport', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PJS', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Pôle Jeunesse et Sport','Pôle Jeunesse et Sport', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PE';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PE', 'Petite enfance', 'Petite enfance', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'PJS', 'Service');
DELETE FROM listmodels WHERE object_id = 'PE' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PE', 'entity_id', 0, 'ssaporta', 'user_id', 'dest', 'Petite enfance','Petite enfance', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PE', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Petite enfance','Petite enfance', '', 'Y');
DELETE FROM entities WHERE entity_id = 'SP';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('SP', 'Sport', 'Sport', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'PJS', 'Service');
DELETE FROM listmodels WHERE object_id = 'SP' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('SP', 'entity_id', 0, 'ttong', 'user_id', 'dest', 'Sport','Sport', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'SP', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Sport','Sport', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PSO';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSO', 'Pôle Social', 'Pôle Social', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGA', 'Service');
DELETE FROM listmodels WHERE object_id = 'PSO' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PSO', 'entity_id', 0, 'nnataly', 'user_id', 'dest', 'Pôle Social','Pôle Social', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PSO', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Pôle Social','Pôle Social', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PTE';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PTE', 'Pôle Technique', 'Pôle Technique', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGA', 'Service');
DELETE FROM listmodels WHERE object_id = 'PTE' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PTE', 'entity_id', 0, 'ccharles', 'user_id', 'dest', 'Pôle Technique','Pôle Technique', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PTE', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Pôle Technique','Pôle Technique', '', 'Y');
DELETE FROM entities WHERE entity_id = 'DRH';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DRH', 'Direction des Ressources Humaines', 'Direction des Ressources Humaines', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGS', 'Service');
DELETE FROM listmodels WHERE object_id = 'DRH' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('DRH', 'entity_id', 0, 'ppruvost', 'user_id', 'dest', 'Direction des Ressources Humaines','Direction des Ressources Humaines', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'DRH', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Direction des Ressources Humaines','Direction des Ressources Humaines', '', 'Y');
DELETE FROM entities WHERE entity_id = 'DSG';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DSG', 'Secrétariat Général', 'Secrétariat Général', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGS', 'Direction');
DELETE FROM listmodels WHERE object_id = 'DSG' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('DSG', 'entity_id', 0, 'ddaull', 'user_id', 'dest', 'Secrétariat Général','Secrétariat Général', '', 'Y');
DELETE FROM entities WHERE entity_id = 'COU';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COU', 'Service Courrier', 'Service Courrier', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DSG', 'Service');
DELETE FROM listmodels WHERE object_id = 'COU' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('COU', 'entity_id', 0, 'bblier', 'user_id', 'dest', 'Service Courrier','Service Courrier', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'COU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Service Courrier','Service Courrier', '', 'Y');
DELETE FROM entities WHERE entity_id = 'COR';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COR', 'Correspondants Archive', 'Correspondants Archive', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'COU', 'Service');
DELETE FROM listmodels WHERE object_id = 'COR' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('COR', 'entity_id', 0, 'ggrand', 'user_id', 'dest', 'Correspondants Archive','Correspondants Archive', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PSF';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSF', 'Pôle des Services Fonctionnels', 'Services Fonctionnels', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DSG', 'Service');
DELETE FROM listmodels WHERE object_id = 'PSF' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PSF', 'entity_id', 0, 'aackermann', 'user_id', 'dest', 'Pôle des Services Fonctionnels','Pôle des Services Fonctionnels', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PSF', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Pôle des Services Fonctionnels','Pôle des Services Fonctionnels', '', 'Y');
DELETE FROM entities WHERE entity_id = 'DSI';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DSI', 'Direction des Systèmes d''Information', 'Direction des Systèmes d''Information', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGS', 'Service');
DELETE FROM listmodels WHERE object_id = 'DSI' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('DSI', 'entity_id', 0, 'ssissoko', 'user_id', 'dest', 'Direction des Systèmes d''Information','Direction des Systèmes d''Information', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'DSI', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Direction des Systèmes d''Information','Direction des Systèmes d''Information', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'DSI', 'entity_id', 0, 'ccordy', 'user_id', 'cc', 'Direction des Systèmes d''Information','Direction des Systèmes d''Information', '', 'Y');
DELETE FROM entities WHERE entity_id = 'FIN';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('FIN', 'Direction des Finances', 'Direction des Finances', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'DGS', 'Service');
DELETE FROM listmodels WHERE object_id = 'FIN' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('FIN', 'entity_id', 0, 'sstar', 'user_id', 'dest', 'Direction des Finances','Direction des Finances', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'FIN', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Direction des Finances','Direction des Finances', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'FIN', 'entity_id', 0, 'jjane', 'user_id', 'cc', 'Direction des Finances','Direction des Finances', '', 'Y');
DELETE FROM entities WHERE entity_id = 'PJU';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJU', 'Pôle Juridique', 'Pôle Juridique', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'FIN', 'Service');
DELETE FROM listmodels WHERE object_id = 'PJU' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('PJU', 'entity_id', 0, 'jjonasz', 'user_id', 'dest', 'Pôle Juridique','Pôle Juridique', '', 'Y');
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ( 'PJU', 'entity_id', 0, 'DSG', 'entity_id', 'cc', 'Pôle Juridique','Pôle Juridique', '', 'Y');
DELETE FROM entities WHERE entity_id = 'ELUS';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('ELUS', 'Ensemble des élus', 'ELUS:Ensemble des élus', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', 'VILLE', 'Direction');
DELETE FROM listmodels WHERE object_id = 'ELUS' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('ELUS', 'entity_id', 0, '', 'user_id', 'dest', 'Ensemble des élus','Ensemble des élus', '', 'Y');
DELETE FROM entities WHERE entity_id = 'CCAS';
INSERT INTO entities (entity_id, entity_label, short_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('CCAS', 'Centre Communal d''Action Sociale', 'Centre Communal d''Action Sociale', 'Y', '', '', '', '', '', '', 'info@maarch.org', '', '', 'Direction');
DELETE FROM listmodels WHERE object_id = 'CCAS' AND object_type = 'entity_id';
INSERT INTO listmodels (object_id, object_type, "sequence", item_id, item_type, item_mode, title, description, process_comment, visible) VALUES ('CCAS', 'entity_id', 0, '', 'user_id', 'dest', 'Centre Communal d''Action Sociale','Centre Communal d''Action Sociale', '', 'Y');

-- Create BASKETS
TRUNCATE TABLE baskets;
DELETE FROM baskets WHERE basket_id = 'QualificationBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'QualificationBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'QualificationBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('QualificationBasket', 'Courriers à qualifier', 'Bannette de qualification', 'status=''INIT''', 'letterbox_coll', 'Y', 'Y',10);
DELETE FROM baskets WHERE basket_id = 'IndexingBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'IndexingBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'IndexingBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('IndexingBasket', 'Courriers à indexer', 'Bannette d''indexation', ' ', 'letterbox_coll', 'Y', 'Y',20);
DELETE FROM baskets WHERE basket_id = 'CopyMailBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'CopyMailBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'CopyMailBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('CopyMailBasket', 'Courriers en copie', 'Courriers en copie non clos ou sans suite', '(res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''user_id'' and item_id = @user and item_mode = ''cc'') or res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''entity_id'' and item_mode = ''cc'' and item_id in (@my_entities))) and status not in ( ''DEL'', ''END'', ''SSUITE'') and res_id not in (select res_id from res_mark_as_read WHERE user_id = @user)', 'letterbox_coll', 'Y', 'Y',30);
DELETE FROM baskets WHERE basket_id = 'RetourCourrier';
DELETE FROM actions_groupbaskets WHERE basket_id = 'RetourCourrier';
DELETE FROM groupbasket_redirect WHERE basket_id = 'RetourCourrier';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('RetourCourrier', 'Retours Courrier', 'Courriers retournés au service Courrier', 'STATUS=''RET''', 'letterbox_coll', 'Y', 'Y',40);
DELETE FROM baskets WHERE basket_id = 'DdeAvisBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'DdeAvisBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'DdeAvisBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('DdeAvisBasket', 'Avis : Avis à émettre', 'Courriers nécessitant un avis', 'status = ''EAVIS'' AND res_id IN (SELECT res_id FROM listinstance WHERE coll_id = ''letterbox_coll'' AND item_type = ''user_id'' AND item_id = @user AND item_mode = ''avis'' and process_date is NULL)', 'letterbox_coll', 'Y', 'Y',50);
DELETE FROM baskets WHERE basket_id = 'SupAvisBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'SupAvisBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'SupAvisBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('SupAvisBasket', 'Avis : En attente de réponse', 'Courriers en attente d''avis', 'status=''EAVIS'' and ((DEST_USER = @user) OR (DEST_USER IN (select user_id from users_entities WHERE entity_id IN( @my_entities)) or DESTINATION in (@subentities[@my_entities]))) and res_id NOT IN (SELECT res_id FROM listinstance WHERE item_mode = ''avis'' and difflist_type = ''entity_id'' and process_date is not NULL and res_view_letterbox.res_id = res_id group by res_id) AND res_id IN (SELECT res_id FROM listinstance WHERE item_mode = ''avis'' and difflist_type = ''entity_id'' and process_date is NULL and res_view_letterbox.res_id = res_id group by res_id)', 'letterbox_coll', 'Y', 'Y',60);
DELETE FROM baskets WHERE basket_id = 'RetAvisBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'RetAvisBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'RetAvisBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('RetAvisBasket', 'Avis : Retours partiels', 'Courriers avec avis reçus', 'status=''EAVIS'' and ((DEST_USER = @user) OR (DEST_USER IN (select user_id from users_entities WHERE entity_id IN( @my_entities)) or DESTINATION in (@subentities[@my_entities]))) and res_id IN (SELECT res_id FROM listinstance WHERE item_mode = ''avis'' and difflist_type = ''entity_id'' and process_date is not NULL and res_view_letterbox.res_id = res_id group by res_id)', 'letterbox_coll', 'Y', 'Y',70);
DELETE FROM baskets WHERE basket_id = 'ValidationBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'ValidationBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'ValidationBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('ValidationBasket', 'Attributions à vérifier', 'Courriers signalés en attente d''instruction pour les services', 'status=''VAL''', 'letterbox_coll', 'Y', 'Y',80);
DELETE FROM baskets WHERE basket_id = 'InValidationBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'InValidationBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'InValidationBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('InValidationBasket', 'Courriers signalés en attente d''instruction', 'Courriers signalés en attente d''instruction par le responsable', 'destination in (@my_entities, @subentities[@my_entities]) and status=''VAL''', 'letterbox_coll', 'Y', 'Y',90);
DELETE FROM baskets WHERE basket_id = 'MyBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'MyBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'MyBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('MyBasket', 'Courriers à traiter', 'Bannette de traitement', 'status in (''NEW'', ''COU'') and dest_user = @user', 'letterbox_coll', 'Y', 'Y',100);
DELETE FROM baskets WHERE basket_id = 'LateMailBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'LateMailBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'LateMailBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('LateMailBasket', 'Courriers en retard', 'Courriers en retard', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'') and (now() > process_limit_date)', 'letterbox_coll', 'Y', 'Y',110);
DELETE FROM baskets WHERE basket_id = 'DepartmentBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'DepartmentBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'DepartmentBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('DepartmentBasket', 'Courriers de ma direction', 'Bannette de supervision', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'' and status <> ''VAL'')', 'letterbox_coll', 'Y', 'Y',120);
DELETE FROM baskets WHERE basket_id = 'ParafBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'ParafBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'ParafBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('ParafBasket', 'Parapheur électronique', 'Courriers à viser ou signer dans mon parapheur', 'status in (''ESIG'', ''EVIS'') AND ((res_id, @user) IN (SELECT res_id, item_id FROM listinstance WHERE difflist_type = ''VISA_CIRCUIT'' and process_date ISNULL and res_view_letterbox.res_id = res_id order by listinstance_id asc limit 1))', 'letterbox_coll', 'Y', 'Y',130);
DELETE FROM baskets WHERE basket_id = 'SuiviParafBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'SuiviParafBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'SuiviParafBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('SuiviParafBasket', 'Courriers en circuit de visa/signature', 'Courriers en circulation dans les parapheurs électroniques', 'status in (''ESIG'', ''EVIS'') AND dest_user = @user', 'letterbox_coll', 'Y', 'Y',140);
DELETE FROM baskets WHERE basket_id = 'EsigARBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'EsigARBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'EsigARBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('EsigARBasket', 'AR à e-signer', 'AR à e-signer', 'status=''ESIGAR'' and (res_id,@user) IN (SELECT res_id, item_id FROM listinstance WHERE item_mode = ''sign'' and process_date ISNULL and res_view_letterbox.res_id = res_id order by listinstance_id asc limit 1)', 'letterbox_coll', 'Y', 'Y',150);
DELETE FROM baskets WHERE basket_id = 'EenvBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'EenvBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'EenvBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('EenvBasket', 'Courriers à envoyer', 'Courriers visés/signés prêts à être envoyés', 'status=''EENV'' and dest_user = @user', 'letterbox_coll', 'Y', 'Y',160);
DELETE FROM baskets WHERE basket_id = 'EenvARBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'EenvARBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'EenvARBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('EenvARBasket', 'AR à e-envoyer', 'AR à e-envoyer', 'status=''EENVAR'' and dest_user = @user', 'letterbox_coll', 'Y', 'Y',170);
DELETE FROM baskets WHERE basket_id = 'ToArcBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'ToArcBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'ToArcBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('ToArcBasket', 'Courriers à archiver', 'Courriers arrivés en fin de DUC à envoyer en archive intermédiaire', 'status = ''EXP_SEDA'' OR status = ''END'' OR status = ''SEND_SEDA''', 'letterbox_coll', 'Y', 'Y',180);
DELETE FROM baskets WHERE basket_id = 'SentArcBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'SentArcBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'SentArcBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('SentArcBasket', 'Courriers en cours d''archivage', 'Courriers envoyés au SAE, en attente de réponse de transfert', 'status=''ACK_SEDA''', 'letterbox_coll', 'Y', 'Y',190);
DELETE FROM baskets WHERE basket_id = 'AckArcBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'AckArcBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'AckArcBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('AckArcBasket', 'Courriers archivés', 'Courriers archivés et acceptés dans le SAE', 'status=''REPLY_SEDA''', 'letterbox_coll', 'Y', 'Y',200);
DELETE FROM baskets WHERE basket_id = 'ReconcilBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'ReconcilBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'ReconcilBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('ReconcilBasket', 'Réponses à réconcilier', 'Réponses à réconcilier', 'status=''PJQUAL''', 'letterbox_coll', 'Y', 'Y',210);
DELETE FROM baskets WHERE basket_id = 'NumericBasket';
DELETE FROM actions_groupbaskets WHERE basket_id = 'NumericBasket';
DELETE FROM groupbasket_redirect WHERE basket_id = 'NumericBasket';
INSERT INTO baskets (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, enabled, basket_order) VALUES ('NumericBasket', 'Plis numériques à qualifier', 'Plis numériques à qualifier', 'status = ''NUMQUAL''', 'letterbox_coll', 'Y', 'Y',220);

-- Create GROUPBASKET
TRUNCATE TABLE groupbasket;
DELETE FROM groupbasket WHERE basket_id = 'QualificationBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('COURRIER', 'QualificationBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'IndexingBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('COURRIER', 'IndexingBasket', 'redirect_to_action');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'IndexingBasket', 'redirect_to_action');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'IndexingBasket', 'redirect_to_action');
DELETE FROM groupbasket WHERE basket_id = 'CopyMailBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'CopyMailBasket', 'list_copies');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'CopyMailBasket', 'list_copies');
DELETE FROM groupbasket WHERE basket_id = 'RetourCourrier';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('COURRIER', 'RetourCourrier', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'DdeAvisBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'DdeAvisBasket', 'list_with_avis');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'DdeAvisBasket', 'list_with_avis');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('ELU', 'DdeAvisBasket', 'list_with_avis');
DELETE FROM groupbasket WHERE basket_id = 'SupAvisBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'SupAvisBasket', 'list_with_avis');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'SupAvisBasket', 'list_with_avis');
DELETE FROM groupbasket WHERE basket_id = 'RetAvisBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'RetAvisBasket', 'list_with_avis');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'RetAvisBasket', 'list_with_avis');
DELETE FROM groupbasket WHERE basket_id = 'ValidationBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESP_COURRIER', 'ValidationBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'InValidationBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'InValidationBasket', 'list_with_attachments');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'InValidationBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'MyBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'MyBasket', 'list_with_attachments');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'MyBasket', 'list_with_attachments');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('ELU', 'MyBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'LateMailBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'LateMailBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'DepartmentBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'DepartmentBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'ParafBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'ParafBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'SuiviParafBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'SuiviParafBasket', 'list_with_attachments');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'SuiviParafBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'EsigARBasket';
DELETE FROM groupbasket WHERE basket_id = 'EenvBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('AGENT', 'EenvBasket', 'list_with_attachments');
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('RESPONSABLE', 'EenvBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'EenvARBasket';
DELETE FROM groupbasket WHERE basket_id = 'ToArcBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('ARCHIVISTE', 'ToArcBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'SentArcBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('ARCHIVISTE', 'SentArcBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'AckArcBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('ARCHIVISTE', 'AckArcBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'ReconcilBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('COURRIER', 'ReconcilBasket', 'list_with_attachments');
DELETE FROM groupbasket WHERE basket_id = 'NumericBasket';
INSERT INTO groupbasket (group_id, basket_id, result_page) VALUES ('COURRIER', 'NumericBasket', 'list_with_attachments');


-- Create Security
TRUNCATE TABLE security;
DELETE FROM security WHERE group_id = 'COURRIER';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('COURRIER', 'letterbox_coll', 'typist=@user', 'Les courriers que j''ai numérisé, pendant 3 mois');
DELETE FROM security WHERE group_id = 'AGENT';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('AGENT', 'letterbox_coll', 'destination in (@my_entities, @subentities[@my_primary_entity])', 'Les courriers non confidentiels de mes services et sous-services');
DELETE FROM security WHERE group_id = 'RESP_COURRIER';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('RESP_COURRIER', 'letterbox_coll', '1=1', 'Tous les courriers');
DELETE FROM security WHERE group_id = 'RESPONSABLE';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('RESPONSABLE', 'letterbox_coll', 'destination in (@my_entities, @subentities[@my_primary_entity])', 'Les courriers de mes services et sous-services');
DELETE FROM security WHERE group_id = 'ADMINISTRATEUR';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('ADMINISTRATEUR', 'letterbox_coll', '1=1', 'Tous les courriers');
DELETE FROM security WHERE group_id = 'DIRECTEUR';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('DIRECTEUR', 'letterbox_coll', '1=0', 'Tous les courriers');
DELETE FROM security WHERE group_id = 'ELU';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('ELU', 'letterbox_coll', '1=1', 'Tous les courriers');
DELETE FROM security WHERE group_id = 'ARCHIVISTE';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('ARCHIVISTE', 'letterbox_coll', '1=1', 'Tous les courriers');
DELETE FROM security WHERE group_id = 'MAARCHTOGEC';
INSERT INTO security (group_id, coll_id, where_clause, maarch_comment) VALUES ('MAARCHTOGEC', 'letterbox_coll', '1=0', 'Aucun courrier');

-- Donnees manuelles
------------
--DOCSERVERS
------------
TRUNCATE TABLE docserver_types;
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('DOC', 'Documents numériques', 'Y', 'SHA512');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('CONVERT', 'Conversions de formats', 'Y', 'SHA256');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('FULLTEXT', 'Plein texte', 'Y', 'SHA256');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('TNL', 'Miniatures', 'Y', 'NONE');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('TEMPLATES', 'Modèles de documents', 'Y', 'NONE');
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode)
VALUES ('ARCHIVETRANSFER', 'Archives numériques', 'Y', 'SHA256');

TRUNCATE TABLE docservers;
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FASTHD_AI', 'DOC', 'Dépôt documentaire issue d''imports de masse', 'Y', 50000000000, 1, '/opt/maarch/docservers/ai/', '2011-01-07 13:43:48.696644', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FASTHD_MAN', 'DOC', 'Dépôt documentaire de numérisation manuelle', 'N', 50000000000, 1290730, '/opt/maarch/docservers/manual/', '2011-01-13 14:47:49.197164', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FASTHD_ATTACH', 'FASTHD', 'Dépôt des pièces jointes', 'N', 50000000000, 1, '/opt/maarch/docservers/manual_attachments/', '2011-01-13 14:47:49.197164', 'attachments_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FASTHD_ATTACH_VERSION', 'FASTHD', 'Dépôt des pièces jointes versionnées', 'N', 50000000000, 1, '/opt/maarch/docservers/manual_attachments_version/', '2011-01-13 14:47:49.197164', 'attachments_version_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('CONVERT_MLB', 'CONVERT', 'Dépôt des formats des documents numérisés', 'N', 50000000000, 0, '/opt/maarch/docservers/convert_mlb/', '2015-03-16 14:47:49.197164', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('CONVERT_ATTACH', 'CONVERT', 'Dépôt des formats des pièces jointes', 'N', 50000000000, 0, '/opt/maarch/docservers/convert_attachments/', '2015-03-16 14:47:49.197164', 'attachments_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('CONVERT_ATTACH_VERSION', 'CONVERT', 'Dépôt des formats des pièces jointes versionnées', 'N', 50000000000, 0, '/opt/maarch/docservers/convert_attachments_version/', '2015-03-16 14:47:49.197164', 'attachments_version_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('TNL_MLB', 'TNL', 'Dépôt des maniatures des documents numérisés', 'N', 50000000000, 0, '/opt/maarch/docservers/thumbnails_mlb/', '2015-03-16 14:47:49.197164', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('TNL_ATTACH', 'TNL', 'Dépôt des maniatures des pièces jointes', 'N', 50000000000, 0, '/opt/maarch/docservers/thumbnails_attachments/', '2015-03-16 14:47:49.197164', 'attachments_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('TNL_ATTACH_VERSION', 'TNL', 'Dépôt des maniatures des pièces jointes versionnées', 'N', 50000000000, 0, '/opt/maarch/docservers/thumbnails_attachments_version/', '2015-03-16 14:47:49.197164', 'attachments_version_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FULLTEXT_MLB', 'FULLTEXT', 'Dépôt de l''extraction plein texte des documents numérisés', 'N', 50000000000, 0, '/opt/maarch/docservers/fulltext_mlb/', '2015-03-16 14:47:49.197164', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FULLTEXT_ATTACH', 'FULLTEXT', 'Dépôt de l''extraction plein texte des pièces jointes', 'N', 50000000000, 0, '/opt/maarch/docservers/fulltext_attachments/', '2015-03-16 14:47:49.197164', 'attachments_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FULLTEXT_ATTACH_VERSION', 'FULLTEXT', 'Dépôt de l''extraction plein texte des pièces jointes versionnées', 'N', 50000000000, 0, '/opt/maarch/docservers/fulltext_attachments_version/', '2015-03-16 14:47:49.197164', 'attachments_version_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('TEMPLATES', 'TEMPLATES', 'Dépôt des modèles de documents', 'N', 50000000000, 71511, '/opt/maarch/docservers/templates/', '2012-04-01 14:49:05.095119', 'templates');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('ARCHIVETRANSFER', 'ARCHIVETRANSFER', 'Dépôt des archives numériques', 'N', 50000000000, 1, '/opt/maarch/docservers/archive_transfer/', '2017-01-13 14:47:49.197164', 'archive_transfer_coll');
------------
--SUPERADMIN USER
------------
DELETE FROM users WHERE user_id='superadmin';
INSERT INTO users (user_id, password, firstname, lastname, phone, mail, custom_t2, custom_t3, enabled, change_password, status, loginmode) VALUES ('superadmin', '$2y$10$Vq244c5s2zmldjblmMXEN./Q2qZrqtGVgrbz/l1WfsUJbLco4E.e.', 'Super', 'ADMIN', '0147245159', 'info@maarch.org', NULL, NULL, 'Y', 'N', 'OK', 'standard');
--MAARCH2GEC USER
DELETE FROM users WHERE user_id = 'cchaplin';
INSERT INTO users (user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) VALUES ('cchaplin', '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', 'Charlie', 'CHAPLIN', 'info@maarch.org', 'Y', 'N', 'OK', 'restMode');
DELETE FROM usergroup_content WHERE user_id = 'cchaplin';
INSERT INTO usergroup_content (user_id, group_id, primary_group, role) VALUES ('cchaplin', 'MAARCHTOGEC', 'Y','');
------------
-- CONTACTS
------------
TRUNCATE TABLE contact_types;
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (100, '1. Entreprises', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (101, '2. Associations', 'Y', 'both');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (102, '3. Administrations', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (103, '4. Collectivités territoriales', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (104, '5. Autorités juridictionnelles', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (105, '6. Organisations syndicales', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (106, '0. Particuliers', 'Y', 'no_corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (107, '7. Banques', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (108, '8. CCI', 'Y', 'corporate');
INSERT INTO contact_types (id, label, can_add_contact, contact_target) VALUES (109, 'NON DEFINI', 'N', 'no_corporate');
select setval('contact_types_id_seq', (select max(id)+1 from contact_types), false);
TRUNCATE TABLE contact_purposes;
INSERT INTO contact_purposes (id, label) VALUES (1, 'Siège social France');
INSERT INTO contact_purposes (id, label) VALUES (2, 'Siège social Sénégal');
INSERT INTO contact_purposes (id, label) VALUES (3, 'Adresse principale');
Select setval('contact_purposes_id_seq', (select max(id)+1 from contact_purposes), false);
TRUNCATE TABLE contacts_v2;
INSERT INTO contacts_v2 (contact_id, contact_type, is_corporate_person, society, society_short, firstname, lastname, title, function, other_data, user_id, entity_id, creation_date, update_date, enabled) VALUES (1, 100, 'Y', 'MAARCH', '', '', '', '', '', 'Editeur du logiciel libre Maarch', 'bblier', 'VILLE', '2015-04-24 12:43:54.97424', '2016-07-25 16:28:38.498185', 'Y');
INSERT INTO contacts_v2 (contact_id, contact_type, is_corporate_person, society, society_short, firstname, lastname, title, function, other_data, user_id, entity_id, creation_date, update_date, enabled) VALUES (2, 102, 'Y', 'Préfecture de Maarch Les Bains', '', '', '', '', '', 'Préfecture de Maarch Les Bains', 'bblier', 'VILLE', '2018-04-18 12:43:54.97424', '2018-04-18 16:28:38.498185', 'Y');
Select setval('contact_v2_id_seq', (select max(contact_id)+1 from contacts_v2), false);
-- Default adresses
TRUNCATE TABLE contact_addresses;
INSERT INTO contact_addresses (id, contact_id, contact_purpose_id, departement, firstname, lastname, title, function, occupancy, address_num, address_street, address_complement, address_town, address_postal_code, address_country, phone, email, website, salutation_header, salutation_footer, other_data, user_id, entity_id, is_private, enabled) VALUES (1, 1, 1, '', 'Jean-Louis', 'ERCOLANI', 'title1', 'Président', '', '11', 'Boulevard du Sud-Est', '92000', 'NANTERRE', 'Nanterre', '', '', 'jeanlouis.ercolani@maarch.org', 'http://www.maarch.com', '', '', '', 'bblier', 'VILLE', 'N', 'Y');
INSERT INTO contact_addresses (id, contact_id, contact_purpose_id, departement, firstname, lastname, title, function, occupancy, address_num, address_street, address_complement, address_town, address_postal_code, address_country, phone, email, website, salutation_header, salutation_footer, other_data, user_id, entity_id, is_private, enabled) VALUES (2, 1, 2, '', 'Karim', 'SY', 'title1', 'Administrateur', '', '', 'Sacré Coeur 3', 'Villa 9653 4ème phase', 'DAKAR', '', 'SENEGAL', '', 'karim.sy@maarch.org', 'http://www.maarch.com', '', '', '', 'bblier', 'VILLE', 'N', 'Y');
INSERT INTO contact_addresses (id, contact_id, contact_purpose_id, departement, firstname, lastname, title, function, occupancy, address_num, address_street, address_complement, address_town, address_postal_code, address_country, phone, email, website, salutation_header, salutation_footer, other_data, user_id, entity_id, is_private, enabled) VALUES (3, 1, 1, '', 'Laurent', 'GIOVANNONI', 'title1', 'Directeur Général', NULL, '11', 'Boulevard du Sud-Est', '92000', 'NANTERRE', 'Nanterre', 'FRANCE', '', 'laurent.giovannoni@maarch.org', 'http://www.maarch.com', '', '', '', 'bblier', 'COU', 'N', 'Y');
INSERT INTO contact_addresses (id, contact_id, contact_purpose_id, departement, firstname, lastname, title, function, occupancy, address_num, address_street, address_complement, address_town, address_postal_code, address_country, phone, email, website, salutation_header, salutation_footer, other_data, user_id, entity_id, is_private, enabled, external_contact_id) VALUES (4, 2, 1, '', 'Nicolas', 'MARTIN', 'title1', 'Le Préfet', NULL, '13', 'RUE LA PREFECTURE', '', 'MAARCH LES BAINS', '06777', 'FRANCE', '', 'info@maarch.org', 'http://www.maarch.com', '', '', '', 'bblier', 'COU', 'N', 'Y', 'org_987654321_DGS_SF');
Select setval('contact_addresses_id_seq', (select max(id)+1 from contact_addresses), false);
-- Default contact_communication
TRUNCATE TABLE contact_communication;
INSERT INTO contact_communication (contact_id, type, value) VALUES (2, 'url', 'https://cchaplin:maarch@demo.maarchcourrier.com');
------------
--STATUS-
------------
TRUNCATE TABLE status;
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('COU', 'En cours', 'Y', 'fm-letter-status-inprogress', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('DEL', 'Supprimé', 'Y', 'fm-letter-del', 'apps', 'N', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('END', 'Clos / fin du workflow', 'Y', 'fm-letter-status-end', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('NEW', 'Nouveau courrier pour le service', 'Y', 'fm-letter-status-new', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('RET', 'Retour courrier ou document en qualification', 'N', 'fm-letter-status-rejected', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('VAL', 'Courrier signalé', 'Y', 'fm-letter-status-aval', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('INIT', 'Nouveau courrier ou document non qualifié', 'Y', 'fm-letter-status-attr', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('VALSG', 'Nouveau courrier ou document en validation SG', 'Y', 'fm-letter-status-attr', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('VALDGS', 'Nouveau courrier ou document en validation DGS', 'Y', 'fm-letter-status-attr', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EAVIS', 'Avis demandé', 'N', 'fa-lightbulb', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EENV', 'A e-envoyer', 'N', 'fm-letter-status-aenv', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('ESIG', 'A e-signer', 'N', 'fm-file-fingerprint', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EVIS', 'A e-viser', 'N', 'fm-letter-status-aval', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('ESIGAR', 'AR à e-signer', 'N', 'fm-file-fingerprint', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EENVAR', 'AR à e-envoyer', 'N', 'fm-letter-status-aenv', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('SVX', 'En attente  de traitement SVE', 'N', 'fm-letter-status-wait', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('SSUITE', 'Sans suite', 'Y', 'fm-letter-del', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('A_TRA', 'PJ à traiter', 'Y', 'fm-letter-new', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('FRZ', 'PJ gelée', 'Y', 'fm-letter-status-inprogress', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('TRA', 'PJ traitée', 'Y', 'fm-letter-end', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('OBS', 'PJ obsolète', 'Y', 'fm-letter-end', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('TMP', 'PJ brouillon', 'Y', 'fm-letter-cou', 'apps', 'N', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('EXP_SEDA', 'A archiver', 'Y', 'fm-letter-status-acla', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('SEND_SEDA ', 'Courrier envoyé au système d''archivage', 'Y', 'fm-letter-status-inprogress', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('ACK_SEDA ', 'Accusé de réception reçu', 'Y', 'fm-letter-status-acla', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('REPLY_SEDA', 'Courrier archivé', 'Y', 'fm-letter-status-acla', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('GRC', 'Envoyé en GRC', 'N', 'fm-letter-cou', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('GRC_TRT', 'En traitement GRC', 'N', 'fm-letter-cou', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('GRC_ALERT', 'Retourné par la GRC', 'N', 'fm-letter-cou', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('RETRN', 'Retourné', 'Y', '', 'apps', 'N', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('NO_RETRN', 'Pas de retour', 'Y', '', 'apps', 'N', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('PJQUAL', 'PJ à réconcilier', 'Y', 'fm-letter-status-attr', 'apps', 'Y', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched, can_be_modified) VALUES ('NUMQUAL', 'Plis à qualifier', 'Y', 'fm-letter-status-attr', 'apps', 'Y', 'Y');
------------
--STATUS IMAGES-
------------
TRUNCATE TABLE status_images;
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-new');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-inprogress');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-info');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-wait');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-validated');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-rejected');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-end');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-newmail');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-attr');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-arev');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aval');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aimp');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-imp');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aenv');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-acla');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aarch');
INSERT INTO status_images (image_name) VALUES ('fm-letter');
INSERT INTO status_images (image_name) VALUES ('fm-letter-add');
INSERT INTO status_images (image_name) VALUES ('fm-letter-search');
INSERT INTO status_images (image_name) VALUES ('fm-letter-del');
INSERT INTO status_images (image_name) VALUES ('fm-letter-incoming');
INSERT INTO status_images (image_name) VALUES ('fm-letter-outgoing');
INSERT INTO status_images (image_name) VALUES ('fm-letter-internal');
INSERT INTO status_images (image_name) VALUES ('fm-file-fingerprint');
INSERT INTO status_images (image_name) VALUES ('fm-classification-plan-l1');
INSERT INTO status_images (image_name) VALUES ('fa-question');
INSERT INTO status_images (image_name) VALUES ('fa-check');
INSERT INTO status_images (image_name) VALUES ('fa-pause');
INSERT INTO status_images (image_name) VALUES ('fa-mail-bulk');


------------
--PARAMETERS
------------
TRUNCATE TABLE parameters;
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('apa_reservation_batch', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('workbatch_rec', '', 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('folder_id_increment', '', 200, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('work_batch_autoimport_id', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('postindexing_workbatch', NULL, 1, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('database_version', '18.10.1', NULL, NULL);
INSERT INTO parameters (id, param_value_string, param_value_int, param_value_date) VALUES ('user_quota', '', 0, NULL);
INSERT INTO parameters (id, description, param_value_string, param_value_int, param_value_date) VALUES ('defaultDepartment', 'Département par défaut sélectionné dans le formulaire des adresses', NULL, 75, NULL);
------------
--DIFFLIST_TYPES
------------
TRUNCATE TABLE difflist_types;
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('entity_id', 'Diffusion aux services', 'dest copy avis', 'Y', 'Y');
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('type_id', 'Diffusion selon le type de document', 'dest copy', 'Y', 'Y');
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('foldertype_id', 'Diffusion selon le type de dossiers', 'dest copy', 'Y', 'Y');
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('VISA_CIRCUIT', 'Circuit de visa', 'visa sign ', 'N', 'Y');
INSERT INTO difflist_types (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities, is_system) VALUES ('AVIS_CIRCUIT', 'Circuit d''avis', 'avis ', 'N', 'Y');
------------
--ACTIONS
------------
TRUNCATE TABLE actions;
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (1, 'redirect', 'Rediriger', 'NEW', 'Y', 'Y', 'redirect', 'Y', 'entities', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (2, '', 'Attribuer au service', 'NEW', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (3, '', 'Retourner au service Courrier', 'RET', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (4, '', 'Enregistrer les modifications', '_NOSTATUS_', 'N', 'Y', 'process', 'N', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (5, '', 'Remettre en traitement', 'COU', 'N', 'Y', '', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (6, '', 'Supprimer le courrier', 'DEL', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (18, 'indexing', 'Qualifier le courrier', '_NOSTATUS', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (19, '', 'Traiter courrier', 'COU', 'N', 'Y', 'process', 'N', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (20, '', 'Cloturer', 'END', 'N', 'Y', 'close_mail', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (21, 'indexing', 'Indexation', 'INIT', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'Y', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (22, 'indexing', 'Attribuer au service', 'NEW', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (23, 'indexing', 'Attribuer au(x) service(s)', 'NEW', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (24, 'indexing', 'Remettre en validation', 'VAL', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (36, '', 'Envoyer pour avis', 'EAVIS', 'N', 'Y','send_docs_to_recommendation', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (37, '', 'Donner un avis', '_NOSTATUS_', 'N', 'Y','avis_workflow_simple', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (100, '', 'Voir le document', '', 'N', 'Y', 'view', 'N', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (101, '', 'Envoyer pour visa', 'VIS', 'N', 'Y', '', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (112, 'indexing', 'Enregistrer', '_NOSTATUS_', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (113, 'redirect', 'Ajouter en copie', '', 'N', 'Y', 'put_in_copy', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (114, '', 'Marquer comme lu', '', 'N', 'Y', 'mark_as_read', 'N', 'apps', 'N', NULL);
--INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (200, '', 'Envoyer l''AR pour e-signature', 'ESIGAR', 'N',, 'Y', 'redirect_visa_sign', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (210, '', 'Transmettre l''AR signé', 'EENVAR', 'N', 'Y', 'confirm_status', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (400, '', 'Envoyer un AR', '_NOSTATUS_', 'N', 'Y', 'send_attachments_to_contact', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (405, '', 'Viser le courrier', '_NOSTATUS_', 'N', 'Y', 'visa_mail', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (407, '', 'Renvoyer pour traitement', 'COU', 'N', 'Y', 'confirm_status', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (410, '', 'Transmettre la réponse signée', 'EENV', 'N', 'Y', 'interrupt_visa', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (414, '', 'Envoyer au parapheur', '_NOSTATUS_', 'N', 'Y', 'send_to_visa', 'Y', 'visa', 'N', NULL);
-- INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (415, '', 'Envoyer pour e-signature', 'ESIG', 'N', 'Y', 'redirect_visa_sign', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (416, '', 'Valider et poursuivre le circuit', '_NOSTATUS_', 'N', 'Y', 'visa_workflow', 'Y', 'visa', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (417, '', 'Envoyer l''AR', 'SVX', 'N', 'Y', 'send_to_contact_with_mandatory_attachment', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (420, '', 'Classer sans suite', 'SSUITE', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (421, '', 'Retourner au Service Courrier', 'RET', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (431, '', 'Envoyer en GRC', 'GRC', 'N', 'Y', '', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (500, '', 'Transférer au système d''archivage', 'SEND_SEDA', 'N', 'Y', 'export_seda', 'Y', 'export_seda', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (501, '', 'Valider la réception du courrier par le système d''archivage', 'ACK_SEDA', 'N', 'Y', 'ack_seda', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (502, '', 'Valider l''archivage du courrier', 'REPLY_SEDA', 'N', 'Y', 'reply_seda', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (503, '', 'Supprimer courrier', 'DEL', 'N', 'Y', 'purge_letter', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (504, '', 'Remise à zero du courrier', 'END', 'N', 'Y', 'reset_letter', 'Y', 'apps', 'N', NULL);
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id, category_id) VALUES (505, 'indexing', 'Réconcilier une réponse à un courrier', 'DEL', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N', NULL);
Select setval('actions_id_seq', (select max(id)+1 from actions), false);
------------
-- BANNETTES SECONDAIRES
TRUNCATE TABLE users_baskets_preferences;
INSERT INTO users_baskets_preferences (user_serial_id, group_serial_id, basket_id, display)
SELECT users.id, usergroups.id, groupbasket.basket_id, TRUE FROM users, usergroups, groupbasket, usergroup_content
WHERE groupbasket.group_id = usergroup_content.group_id AND users.user_id = usergroup_content.user_id AND usergroups.group_id = usergroup_content.group_id
ORDER BY users.id;
------------
--ACTIONS_GROUPBASKETS
------------
TRUNCATE TABLE actions_groupbaskets;
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'COURRIER', 'IndexingBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (24, '', 'COURRIER', 'RetourCourrier', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (6, '', 'COURRIER', 'RetourCourrier', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (18, '', 'COURRIER', 'QualificationBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (505, '', 'COURRIER', 'ReconcilBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (18, '', 'COURRIER', 'NumericBasket', 'Y', 'Y', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (22, '', 'RESP_COURRIER', 'ValidationBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (23, '', 'RESP_COURRIER', 'ValidationBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (420, '', 'RESP_COURRIER', 'ValidationBasket', 'N', 'Y', 'N');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'AGENT', 'CopyMailBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (114, '', 'AGENT', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'AGENT', 'MyBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (414, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (36, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'AGENT', 'InValidationBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'AGENT', 'RetAvisBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (5, '', 'AGENT', 'RetAvisBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'AGENT', 'IndexingBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (37, '', 'AGENT', 'DdeAvisBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'AGENT', 'DdeAvisBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'AGENT', 'SupAvisBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (5, '', 'AGENT', 'SupAvisBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'AGENT', 'EenvBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'AGENT', 'EenvBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'AGENT', 'SuiviParafBasket', 'Y', 'Y', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'MyBasket', '', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (414, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (36, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'InValidationBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'CopyMailBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (113, '', 'RESPONSABLE', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (114, '', 'RESPONSABLE', 'CopyMailBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'RESPONSABLE', 'ValidAnswerBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'DepartmentBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'RESPONSABLE', 'IndexingBasket', 'Y', 'Y', 'Y');
--INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (36, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (37, '', 'RESPONSABLE', 'DdeAvisBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'RESPONSABLE', 'DdeAvisBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'RESPONSABLE', 'SupAvisBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (5, '', 'RESPONSABLE', 'SupAvisBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'RESPONSABLE', 'RetAvisBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (5, '', 'RESPONSABLE', 'RetAvisBasket', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (405, '', 'RESPONSABLE', 'ParafBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (416, '', 'RESPONSABLE', 'ParafBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (407, '', 'RESPONSABLE', 'ParafBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (410, '', 'RESPONSABLE', 'ParafBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'EenvBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'RESPONSABLE', 'EenvBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'RESPONSABLE', 'SuiviParafBasket', 'Y', 'Y', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (18, '', 'DIRECTEUR', 'ValidationBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'DIRECTEUR', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (3, '', 'DIRECTEUR', 'ValidationBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (6, '', 'DIRECTEUR', 'ValidationBasket', 'N', 'Y', 'N');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'ELU', 'MyBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (112, '', 'ELU', 'IndexingBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (37, '', 'ELU', 'DdeAvisBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (4, '', 'ELU', 'DdeAvisBasket', 'Y', 'Y', 'Y');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (500, '', 'ARCHIVISTE', 'ToArcBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (501, '', 'ARCHIVISTE', 'ToArcBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (502, '', 'ARCHIVISTE', 'SentArcBasket', 'Y', 'Y', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (503, '', 'ARCHIVISTE', 'AckArcBasket', 'Y', 'N', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (504, '', 'ARCHIVISTE', 'AckArcBasket', 'Y', 'Y', 'Y');
------------
TRUNCATE TABLE groupbasket_redirect;
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'IndexingBasket', 112, '', 'MY_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'IndexingBasket', 112, '', 'ENTITIES_BELOW', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'MyBasket', 1, '', 'MY_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'MyBasket', 1, '', 'ENTITIES_BELOW', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'MyBasket', 1, '', 'MY_ENTITIES', 'USERS');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'MyBasket', 1, '', 'ENTITIES_BELOW', 'USERS');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('AGENT', 'MyBasket', 1, 'PSO', '', 'ENTITY');

INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('RESPONSABLE', 'IndexingBasket', 112, '', 'MY_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('RESPONSABLE', 'IndexingBasket', 112, '', 'ENTITIES_BELOW', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('RESPONSABLE', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('RESPONSABLE', 'MyBasket', 1, '', 'ALL_ENTITIES', 'USERS');

INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('ELU', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES ('ELU', 'IndexingBasket', 112, '', 'ALL_ENTITIES', 'ENTITY');
Select setval('groupbasket_redirect_system_id_seq', (select max(system_id)+1 from groupbasket_redirect), false);
------------
--GROUPBASKET_STATUS
------------
TRUNCATE TABLE groupbasket_status;
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'IndexingBasket', 112, 'VAL', 2);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'IndexingBasket', 112, 'NEW', 1);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'IndexingBasket', 112, 'PJQUAL', 3);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'QualificationBasket', 18, 'VAL', 4);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'QualificationBasket', 18, 'NEW', 5);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'NumericBasket', 18, 'VAL', 6);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('COURRIER', 'NumericBasket', 18, 'NEW', 7);

INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('AGENT', 'IndexingBasket', 112, 'END', 2);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('AGENT', 'IndexingBasket', 112, 'NEW', 1);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('RESPONSABLE', 'IndexingBasket', 112, 'END', 2);
INSERT INTO groupbasket_status (group_id, basket_id, action_id, status_id, "order") VALUES ('RESPONSABLE', 'IndexingBasket', 112, 'NEW', 1);
Select setval('groupbasket_status_system_id_seq', (select max(system_id)+1 from groupbasket_status), false);
------------
--FOLDERTYPES
------------
TRUNCATE TABLE foldertypes;
INSERT INTO foldertypes (foldertype_id, foldertype_label, maarch_comment, retention_time, custom_d1, custom_f1, custom_n1, custom_t1, custom_d2, custom_f2, custom_n2, custom_t2, custom_d3, custom_f3, custom_n3, custom_t3, custom_d4, custom_f4, custom_n4, custom_t4, custom_d5, custom_f5, custom_n5, custom_t5, custom_d6, custom_t6, custom_d7, custom_t7, custom_d8, custom_t8, custom_d9, custom_t9, custom_d10, custom_t10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, coll_id)
VALUES (1, 'Les courriers', '', NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'letterbox_coll');
Select setval('foldertype_id_id_seq', (select max(foldertype_id)+1 from foldertypes), false);
------------
--TEMPLATES_DOCTYPE_EXT--
------------
TRUNCATE TABLE templates_doctype_ext;
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (3, 1201, 'Y');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (9, 1202, 'Y');
------------
--FOLDERS
------------
TRUNCATE TABLE folders;
INSERT INTO folders (folders_system_id, folder_id, foldertype_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date, folder_out_id, video_status, video_user, is_frozen, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_d11, custom_t12, custom_d12, custom_t13, custom_d13, custom_t14, custom_d14, custom_t15, is_complete, is_folder_out, last_modified_date) VALUES (23, 'COURRIERS', 1, 0, 'Courriers', NULL, NULL, NULL, 'superadmin', 'FOLDNEW', 1, '2012-03-02 18:31:27.487', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', '2012-03-02 18:31:27.487');
Select setval('folders_system_id_seq', (select max(folders_system_id)+1 from folders), false);
------------
--KEYWORDS / TAGS
------------
TRUNCATE TABLE tags;
ALTER SEQUENCE tag_id_seq RESTART WITH 1;
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('SEMINAIRE', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('INNOVATION', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('MAARCH', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('ENVIRONNEMENT', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('PARTENARIAT', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('JUMELAGE', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('ECONOMIE', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('ASSOCIATIONS', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('RH', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('BUDGET', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('QUARTIERS', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('LITTORAL', 'letterbox_coll', 'COU');
INSERT INTO tags (tag_label, coll_id, entity_id_owner) VALUES ('SPORT', 'letterbox_coll', 'COU');
Select setval('tag_id_seq', (select max(tag_id)+1 from tags), false);

TRUNCATE TABLE tags_entities;
INSERT INTO tags_entities (tag_id, entity_id) VALUES (1, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (2, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (3, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (4, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (5, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (6, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (7, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (8, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (9, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (10, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (11, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (12, 'COU');
INSERT INTO tags_entities (tag_id, entity_id) VALUES (13, 'COU');

------------
------------
--TEMPLATES
------------
TRUNCATE TABLE templates;
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (3, 'AppelTel', 'Appel téléphonique', '<h2><span style="color: #000000;"><strong>Appel t&eacute;l&eacute;phonique</strong></span></h2>
<hr />
<p>&nbsp;</p>
<p>Bonjour,</p>
<p>Vous avez re&ccedil;u un appel t&eacute;l&eacute;phonique dont voici les informations :</p>
<table style="height: 61px; border-color: #f0f0f0;" border="1" width="597"><caption>&nbsp;</caption>
<tbody>
<tr>
<td style="text-align: center;"><strong>Date</strong></td>
<td style="text-align: center;"><strong>Heure</strong></td>
<td style="text-align: center;"><strong>Soci&eacute;t&eacute;</strong></td>
<td style="text-align: center;"><strong>Contact</strong></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<h4>Notes :</h4>
<p>&nbsp;</p>', 'HTML', NULL, NULL, '', '', 'doctypes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)
VALUES (2, '[notification] Notifications événement', 'Notifications des événements système',
'<p><font face="verdana,geneva" size="1">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p><font face="verdana,geneva" size="1"> </font></p>
<p><font face="verdana,geneva" size="1">Voici la liste des &eacute;v&eacute;nements de l''application qui vous sont notifi&eacute;s ([notification.description]) :</font></p>
<table style="width: 800px; height: 36px;" border="0" cellspacing="1" cellpadding="1">
<tbody>
<tr>
<td style="width: 150px; background-color: #0099ff;"><font face="verdana,geneva" size="1"><strong><font color="#FFFFFF">Date</font></strong></font></td>
<td style="width: 150px; background-color: #0099ff;"><font face="verdana,geneva" size="1"><strong><font color="#FFFFFF">Utilisateur </font></strong></font><font face="verdana,geneva" size="1"><strong></strong></font></td>
<td style="width: 500px; background-color: #0099ff;"><font face="verdana,geneva" size="1"><strong><font color="#FFFFFF">Description</font></strong></font></td>
</tr>
<tr>
<td><font face="verdana,geneva" size="1">[events.event_date;block=tr;frm=dd/mm/yyyy hh:nn:ss]</font></td>
<td><font face="verdana,geneva" size="1">[events.user_id]</font></td>
<td><font face="verdana,geneva" size="1">[events.event_info]</font></td>
</tr>
</tbody>
</table>',
'HTML', NULL, NULL, '', 'notif_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)
VALUES (5, '[notification courrier] Alerte 2', '[notification] Alerte 2', '<p><font face="arial,helvetica,sans-serif" size="2">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2">Voici la liste des courriers dont la date limite de traitement est dépassée :n</font></p>
<table style="border: 1pt solid #000000; width: 1582px; height: 77px;" border="1" cellspacing="1" cellpadding="5" frame="box">
<tbody>
<tr>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Référence</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Origine</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Emetteur</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Date</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Objet</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Type</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#FFFFFF"><strong>Liens</strong></font></td>
</tr>
<tr>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.res_id]</font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.typist]</font></td>
<td>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</font></p>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.tag_label]</font></p>
</td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.doc_date;block=tr;frm=dd/mm/yyyy]</font></td>
<td><font face="arial,helvetica,sans-serif" color="#FF0000"><strong><font size="2">[res_letterbox.subject]</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.type_label]</font></td>
<td><font face="arial,helvetica,sans-serif"><a href="[res_letterbox.linktoprocess]" name="traiter">traiter</a> <a href="[res_letterbox.linktodoc]" name="doc">Afficher</a></font></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)
VALUES (6, '[notification courrier] Alerte 1', '[notification] Alerte 1', '<p><font face="arial,helvetica,sans-serif" size="2">Bonjour [recipient.firstname] [recipient.lastname],</font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2"> </font></p>
<p> </p>
<p><font face="arial,helvetica,sans-serif" size="2">Voici la liste des courriers toujours en attente de traitement :</font></p>
<p> </p>
<table style="border: 1pt solid #000000; width: 1582px; height: 77px;" border="1" cellspacing="1" cellpadding="5" frame="box">
<tbody>
<tr>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Référence</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Origine</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif"><strong><font size="2">Emetteur</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Date</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Objet</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#000000"><strong>Type</strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2" color="#FFFFFF"><strong>Liens</strong></font></td>
</tr>
<tr>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.res_id]</font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.typist]</font></td>
<td>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</font></p>
<p><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.tag_label]</font></p>
</td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.doc_date;block=tr;frm=dd/mm/yyyy]</font></td>
<td><font face="arial,helvetica,sans-serif" color="#FF0000"><strong><font size="2">[res_letterbox.subject]</font></strong></font></td>
<td><font face="arial,helvetica,sans-serif" size="2">[res_letterbox.type_label]</font></td>
<td><font face="arial,helvetica,sans-serif"><a href="[res_letterbox.linktoprocess]" name="traiter">traiter</a> <a href="[res_letterbox.linktodoc]" name="doc">Afficher</a></font></td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)
VALUES (7, '[notification courrier] Diffusion de courrier', 'Alerte de courriers présents dans les bannettes', '<p style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif;">Bonjour <strong>[recipient.firstname] [recipient.lastname]</strong>,</p>
<p>&nbsp;</p>
<p style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif;">Voici la liste des nouveaux courriers pr&eacute;sents dans cette bannette :</p>
<table style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%;">
<tbody>
<tr>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">R&eacute;f&eacute;rence</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Origine</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Emetteur</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Date</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Objet</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Type</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">&nbsp;</th>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 8px;">[res_letterbox.res_id]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[res_letterbox.typist]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[res_letterbox.contact_society] [res_letterbox.contact_firstname] [res_letterbox.contact_lastname][res_letterbox.function][res_letterbox.address_num][res_letterbox.address_street][res_letterbox.address_postal_code][res_letterbox.address_town]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[res_letterbox.doc_date;block=tr;frm=dd/mm/yyyy]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[res_letterbox.subject]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[res_letterbox.type_label]</td>
<td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><a style="text-decoration: none; background: #135f7f; padding: 5px; color: white; -webkit-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); -moz-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75);" href="[res_letterbox.linktodetail]" name="detail">D&eacute;tail</a> <a style="text-decoration: none; background: #135f7f; padding: 5px; color: white; -webkit-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); -moz-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75);" href="[res_letterbox.linktodoc]" name="doc">Afficher</a></td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<p style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif; width: 100%; text-align: center; font-size: 9px; font-style: italic; opacity: 0.5;">Message g&eacute;n&eacute;r&eacute; via l''application MaarchCourrier</p>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_events', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target)
VALUES (8, '[notification courrier] Nouvelle annotation', '[notification] Nouvelle annotation', '<p style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif;">Bonjour <strong>[recipient.firstname] [recipient.lastname]</strong>,</p>
<p>&nbsp;</p>
<p style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif;">Voici les nouvelles annotations sur les courriers suivants :</p>
<table style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%;">
<tbody>
<tr>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">R&eacute;f&eacute;rence</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Num</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Date</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Objet</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Note</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Ajout&eacute; par</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">Contact</th>
<th style="border: 1px solid #ddd; padding: 8px; padding-top: 12px; padding-bottom: 12px; text-align: left; background-color: #135f7f; color: white;">&nbsp;</th>
</tr>
<tr>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.identifier]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.# ;frm=0000]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.doc_date;block=tr;frm=dd/mm/yyyy]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.subject]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.note_text]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.user_id]</td>
<td style="border: 1px solid #ddd; padding: 8px;">[notes.contact_society] [notes.contact_firstname] [notes.contact_lastname]</td>
<td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><a style="text-decoration: none; background: #135f7f; padding: 5px; color: white; -webkit-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); -moz-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75);" href="[res_letterbox.linktodetail]" name="detail">D&eacute;tail</a> <a style="text-decoration: none; background: #135f7f; padding: 5px; color: white; -webkit-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); -moz-box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75); box-shadow: 6px 4px 5px 0px rgba(0,0,0,0.75);" href="[res_letterbox.linktodoc]" name="doc">Afficher</a></td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<p style="font-family: Trebuchet MS, Arial, Helvetica, sans-serif; width: 100%; text-align: center; font-size: 9px; font-style: italic; opacity: 0.5;">Message g&eacute;n&eacute;r&eacute; via l''application MaarchCourrier</p>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'notes', 'notifications');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (9, 'Demande - Voirie', 'Demande - Voirie', '<h2>Demande Intervention VOIRIE</h2>
<hr />
<table style="border: 1pt solid #000000; width: 597px; background-color: #f0f0f0; height: 172px;" border="1" cellspacing="1" cellpadding="5"><caption>&nbsp;</caption>
<tbody>
<tr>
<td style="width: 200px; background-color: #ffffff;"><strong>NOM, PRENOM demandeur</strong></td>
<td style="width: 200px; background-color: #ffffff;">&nbsp;</td>
</tr>
<tr style="background-color: #ffffff;">
<td style="width: 200px;">Adresse</td>
<td>&nbsp;</td>
</tr>
<tr style="background-color: #ffffff;">
<td style="width: 200px;"><strong>Contact</strong></td>
<td>&nbsp;</td>
</tr>
<tr style="background-color: #ffffff;">
<td style="width: 200px;"><strong>Intitul&eacute; demande</strong></td>
<td>&nbsp;</td>
</tr>
<tr style="background-color: #ffffff;">
<td style="width: 200px;">Compl&eacute;ment</td>
<td>&nbsp;</td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'DOCX: demo_document_msoffice', '', 'doctypes', 'all');
INSERT INTO templates VALUES (10, '[maarch mairie] Clôture de demande', '[maarch mairie] Clôture de demande', '<p style="text-align: left;"><span style="font-size: small;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="text-decoration: underline;"><span style="font-size: small;">CLOTURE DEMANDE Maarch Mairie - [res_letterbox.type_label] - [res_letterbox.res_id] </span></span></p>
<p style="text-align: center;">&nbsp;</p>
<table style="background-color: #a8c33c; width: 800px; border: #000000 1pt solid;" border="1" cellspacing="1" cellpadding="5">
<tbody>
<tr>
<td style="width: 200px;">CLOTURE&nbsp;DE LA DEMANDE</td>
<td>DATE: [dates]</td>
<td>HEURE: [time]</td>
</tr>
</tbody>
</table>
<table style="width: 800px; border: #000000 1pt solid;" border="1" cellspacing="1" cellpadding="5">
<tbody>
<tr>
<td style="width: 200px; background-color: #a8c33c;">OBJET</td>
<td style="background-color: #e1f787;">&nbsp;[res_letterbox.subject]</td>
</tr>
<tr>
<td style="width: 200px; background-color: #a8c33c;">ACTIONS CONDUITES</td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="width: 200px; background-color: #a8c33c;">DATE DE REMISE EN ETAT / SERVICE</td>
<td style="background-color: #e1f787;">&nbsp;</td>
</tr>
<tr>
<td style="width: 200px; background-color: #a8c33c;">CONSIGNES COMPLEMENTAIRES</td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="width: 200px; background-color: #a8c33c;">AUTRES OBSERVATIONS</td>
<td style="background-color: #e1f787;">&nbsp;</td>
</tr>
<tr>
<td style="width: 200px; background-color: #a8c33c;">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="width: 200px; background-color: #a8c33c;">&nbsp;</td>
<td style="background-color: #e1f787;">&nbsp;</td>
</tr>
</tbody>
</table>', 'HTML', NULL, NULL, 'DOCX: demo_document_msoffice', '', 'doctypes');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (20, 'Accompagnement courriel', 'Modèle de courriel d''''accompagnement', '<p>Bonjour,</p>
<p>En r&eacute;ponse &agrave; votre courrier en date du [res_letterbox.doc_date], veuillez trouver notre r&eacute;ponse en pi&egrave;ce-jointe.</p>
<p>Cordialement,</p>
<p><strong>Ville de Maarch-les-Bains</strong><br /><em>[user.firstname] [user.lastname]</em><br /><em>[user.phone]</em></p>', 'HTML', NULL, NULL, 'DOCX: standard_nosign', 'letterbox_attachment', 'sendmail', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (21, 'AR dérogation LO', 'AR derogation carte scolaire', '', 'OFFICE', '0000#', 'ar_derogation.odt', 'ODT: ARderogation', 'letterbox_attachment', 'attachments', 'response_project');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (22, 'Réponse crèche LO', 'Réponse à une demande de place en crèche', '', 'OFFICE', '0000#', 'rep_creche.odt', 'ODT: Repddeplacecreche', 'letterbox_attachment', 'attachments', 'response_project');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (23, 'Réponse générique LO', 'Modèle de réponse générique', '', 'OFFICE', '0000#', 'rep_standard.odt', 'ODT: standard_sign', 'letterbox_attachment', 'attachments', 'response_project');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (24, 'Réponse générique MS', 'Modèle de réponse MS Office', '', 'OFFICE', '0000#', 'rep_standard.docx', 'DOCX: standard_sign', 'letterbox_attachment', 'attachments', 'response_project');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (25, 'AR SVA LO', 'AR SVA LO', '', 'OFFICE', '0000#', 'ar_sva.odt', 'ODT: ar_sva', 'letterbox_attachment', 'attachments', 'sva');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (26, 'AR SVR LO', 'AR SVR LO', '', 'OFFICE', '0000#', 'ar_svr.odt', 'ODT: ar_svr', 'letterbox_attachment', 'attachments', 'svr');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (27, 'Réponse avec transmission LO', 'Réponse avec transmission LO', '', 'OFFICE', '0000#', 'rep_transmission.odt', 'ODT: rep_transmission', 'letterbox_attachment', 'attachments', 'response_project');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (28, 'Transmission LO', 'Transmission LO', '', 'OFFICE', '0000#', 'transmission.odt', 'ODT: transmission', 'letterbox_attachment', 'attachments', 'transmission');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (29, 'Courrier invitation PME LO', 'Courrier invitation PME LO', '', 'OFFICE', '0000#', 'invitation.odt', 'ODT: invitation', 'letterbox_attachment', 'attachments', 'outgoing_mail');
------------
--TEMPLATES réponses mail SVE
--
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (900, '[TRT] Passer me voir', 'Passer me voir', 'Passer me voir à mon bureau, merci.', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (901, '[TRT] Compléter', 'Compléter', 'Le projet de réponse doit être complété/révisé sur les points suivants : \n\n- ', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (902, '[AVIS] Demande avis', 'Demande avis', 'Merci de me fournir les éléments de langage pour répondre à ce courrier.', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (904, '[AVIS] Avis favorable', 'Avis favorable', 'Merci de répondre favorablement à la demande inscrite dans ce courrier', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (905, '[CLOTURE] Clôture pour REJET', 'Clôture pour REJET', 'Clôture pour REJET', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (906, '[CLOTURE] Clôture pour ABANDON', 'Clôture pour ABANDON', 'Clôture pour ABANDON', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (907, '[CLOTURE] Clôture RAS', 'Clôture RAS', 'Clôture NORMALE', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (908, '[CLOTURE] Clôture AUTRE', 'Clôture AUTRE', 'Clôture pour ce motif : ', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (909, '[REJET] Erreur affectation', 'Erreur affectation', 'Ce courrier ne semble pas concerner mon service', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
INSERT INTO templates (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (910, '[REJET] Anomalie de numérisation', 'Anomalie de numérisation', 'Le courrier présente des anomalies de numérisation', 'TXT', NULL, NULL, 'XLSX: demo_spreadsheet_msoffice', '', 'notes', 'all');
--
INSERT INTO templates  (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (1000, '[MAIL] AR TYPE Réorientation d’une saisine électronique vers l’autorité compétente', '[MAIL] AR TYPE Réorientation d’une saisine électronique vers l’autorité compétente', '<h2>Ville de Maarch-les-Bains</h2>
<p><em>[entities.adrs_1]</em><br /><em>[entities.adrs_2]</em><br /><em>[entities.zipcode] [entities.city]<br /></em></p>
<p>(Veuillez renseigner le numero de telephone de votre service)</p>
<p>Le [res_letterbox.doc_date], vous avez transmis par voie &eacute;lectronique &agrave; la Ville une demande qui ne rel&egrave;ve pas de sa comp&eacute;tence.</p>
<p>Votre demande concerne [res_letterbox.subject].</p>
<p>Cette demande a &eacute;t&eacute; transmise &agrave; (veuillez renseigner le nom de l''AUTORITE COMPETENTE).</p>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_attachment', 'sendmail', 'all');
--
INSERT INTO templates  (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (1020, '[MAIL] AR TYPE dans le cas d’une décision implicite de rejet', '[MAIL] AR TYPE dans le cas d’une décision implicite de rejet', '<h2>Ville de Maarch-les-Bains</h2>
<p><em>[entities.adrs_1]</em><br /><em>[entities.adrs_2]</em><br /><em>[entities.zipcode] [entities.city]<br /></em></p>
<p>(Veuillez renseigner le numero de telephone de votre service)</p>
<p>Le [res_letterbox.doc_date], vous avez transmis par voie &eacute;lectronique &agrave; la Ville une demande qui rel&egrave;ve de sa comp&eacute;tence.</p>
<p>Votre demande concerne : [res_letterbox.subject].</p>
<p>Le pr&eacute;sent accus&eacute; r&eacute;ception atteste la r&eacute;ception de votre demande, il ne pr&eacute;juge pas de la conformit&eacute; de son contenu qui d&eacute;pend entre autres de l''&eacute;tude des pi&egrave;ces fournies. Pour tout renseignement concernant votre dossier, vous pouvez contacter le service charg&eacute; du dossier par t&eacute;l&eacute;phone [users.phone] ou par messagerie [users.mail].</p>
<p>Votre demande est susceptible de faire l''objet d''une d&eacute;cision implicite de rejet en l''absence de r&eacute;ponse dans les (XX) jours suivant sa r&eacute;ception, soit le [res_letterbox.process_limit_date].</p>
<p>Si l''instruction de votre demande n&eacute;cessite des informations ou pi&egrave;ces compl&eacute;mentaires, la Ville vous contactera afin de les fournir, dans un d&eacute;lai de production qui sera fix&eacute;.</p>
<p>Dans ce cas, le d&eacute;lai de d&eacute;cision implicite de rejet serait alors suspendu le temps de produire les pi&egrave;ces demand&eacute;es.</p>
<p>Si vous estimez que la d&eacute;cision qui sera prise par l''administration est contestable, vous pourrez formuler :</p>
<p>- Soit un recours gracieux devant l''auteur de la d&eacute;cision</p>
<p>- Soit un recours hi&eacute;rarchique devant le Maire</p>
<p>- Soit un recours contentieux devant le Tribunal Administratif territorialement comp&eacute;tent.</p>
<p>Le recours gracieux ou le recours hi&eacute;rarchique peuvent &ecirc;tre faits sans condition de d&eacute;lais.</p>
<p>Le recours contentieux doit intervenir dans un d&eacute;lai de deux mois &agrave; compter de la notification de la d&eacute;cision.</p>
<p>Toutefois, si vous souhaitez en cas de rejet du recours gracieux ou du recours hi&eacute;rarchique former un recours contentieux, ce recours gracieux ou hi&eacute;rarchique devra avoir &eacute;t&eacute; introduit dans le d&eacute;lai sus-indiqu&eacute; du recours contentieux.</p>
<p>Vous conserverez ainsi la possibilit&eacute; de former un recours contentieux, dans un d&eacute;lai de deux mois &agrave; compter de la d&eacute;cision intervenue sur ledit recours gracieux ou hi&eacute;rarchique.</p>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_attachment', 'sendmail', 'all');
--
INSERT INTO templates  (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (1010, '[MAIL] AR TYPE dans le cas d’une décision implicite d’acceptation', '[MAIL] AR TYPE dans le cas d’une décision implicite d’acceptation', '<h2>Ville de Maarch-les-Bains</h2>
<p><em>[entities.adrs_1]</em><br /><em>[entities.adrs_2]</em><br /><em>[entities.zipcode] [entities.city]<br /></em></p>
<p>(Veuillez renseigner le numero de telephone de votre service)</p>
<p>Le [res_letterbox.doc_date], vous avez transmis par voie &eacute;lectronique &agrave; la Ville une demande qui rel&egrave;ve de sa comp&eacute;tence.</p>
<p>Votre demande concerne [res_letterbox.subject].</p>
<p>Le pr&eacute;sent accus&eacute; de r&eacute;ception atteste de la r&eacute;ception de votre demande. il ne pr&eacute;juge pas de la conformit&eacute; de son contenu qui d&eacute;pend entre autres de l''''&eacute;tude des pi&egrave;ces fournies.</p>
<p>Pour tout renseignement concernant votre dossier, vous pouvez contacter le service charg&eacute; du dossier par t&eacute;l&eacute;phone [users.phone] ou par messagerie [users.mail].</p>
<p>Votre demande est susceptible de faire l''objet d''''une d&eacute;cision implicite d''''acceptation en l''absence de r&eacute;ponse dans les (XX) jours suivant sa r&eacute;ception, soit le [res_letterbox.process_limit_date].</p>
<p>Si l''instruction de votre demande n&eacute;cessite des informations ou pi&egrave;ces compl&eacute;mentaires, la Ville vous contactera afin de les fournir, dans un d&eacute;lai de production qui sera fix&eacute;.</p>
<p>Le cas &eacute;ch&eacute;ant, le d&eacute;lai de d&eacute;cision implicite d''acceptation ne d&eacute;butera qu''''apr&egrave;s la production des pi&egrave;ces demand&eacute;es.</p>
<p>En cas de d&eacute;cision implicite d''''acceptation vous avez la possibilit&eacute; de demander au service charg&eacute; du dossier une attestation conform&eacute;ment aux dispositions de l''article 22 de la loi n&deg; 2000-321 du 12 avril 2000 relative aux droits des citoyens dans leurs relations avec les administrations modifi&eacute;e.</p>', 'HTML', NULL, NULL, 'ODP: open_office_presentation', 'letterbox_attachment', 'sendmail', 'all');
--
INSERT INTO templates  (template_id, template_label, template_comment, template_content, template_type, template_path, template_file_name, template_style, template_datasource, template_target, template_attachment_type) VALUES (1030, '[MAIL] AR TYPE dans le cas d’une demande n’impliquant pas de décision implicite de l’administration', '[MAIL] AR TYPE dans le cas d’une demande n’impliquant pas de décision implicite de l’administration', '<h2>Ville de Maarch-les-Bains</h2>
<p><em>[entities.adrs_1]</em><br /><em>[entities.adrs_2]</em><br /><em>[entities.zipcode] [entities.city]<br /></em></p>
<p>(Veuillez renseigner le numero de telephone de votre service)</p>
<p>Le [res_letterbox.doc_date], vous avez transmis par voie &eacute;lectronique &agrave; la Ville une demande qui rel&egrave;ve de sa comp&eacute;tence.</p>
<p>Votre demande concerne [res_letterbox.subject].</p>
<p>Le pr&eacute;sent accus&eacute; de r&eacute;ception atteste de la r&eacute;ception de votre demande. Il ne pr&eacute;juge pas de la conformit&eacute; de son contenu qui d&eacute;pend entre autres de l''&eacute;tude des pi&egrave;ces fournies.</p>
<p>Si l''instruction de votre demande n&eacute;cessite des informations ou pi&egrave;ces compl&eacute;mentaires, la Ville vous contactera afin de les fournir, dans un d&eacute;lai de production qui sera fix&eacute;.</p>
<p>Pour tout renseignement concernant votre dossier, vous pouvez contacter le service charg&eacute; du dossier par t&eacute;l&eacute;phone [users.phone] ou par messagerie [users.mail].</p>', 'HTML', NULL, NULL, 'TXT: document_texte', 'letterbox_attachment', 'sendmail', 'all');

INSERT INTO templates (template_label, template_comment, template_content, template_type, template_style, template_target, template_attachment_type) VALUES ('Quota d''utilisateur', 'Modèle de notification pour le quota utilisateur', '<p>Quota utilisateur atteint</p>', 'HTML', 'ODT: rep_standard', 'notifications', 'all');
------------
Select setval('templates_seq', (select max(template_id)+1 from templates), false);
------------
--PRIORITES
------------
TRUNCATE TABLE priorities;
INSERT INTO priorities (id, label, color, working_days, delays, default_priority, "order") VALUES ('poiuytre1357nbvc', 'Normal', '#009dc5', TRUE, null, TRUE, 1);
INSERT INTO priorities (id, label, color, working_days, delays, default_priority, "order") VALUES ('poiuytre1379nbvc', 'Urgent', '#ffa500', TRUE, 8, FALSE, 2);
INSERT INTO priorities (id, label, color, working_days, delays, default_priority, "order") VALUES ('poiuytre1391nbvc', 'Très urgent', '#ff0000', TRUE, 4, FALSE, 3);
------------
--NOTIFICATIONS
------------
TRUNCATE TABLE notifications;

INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties)
VALUES (1, 'USERS', '[administration] Actions sur les utilisateurs de l''application', 'users%', 'EMAIL', 2, 'user', 'superadmin', '', '');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled)
VALUES (2, 'RET2', 'Courriers en retard de traitement', 'alert2', 'EMAIL', 5, 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled)
VALUES (3, 'RET1', 'Courriers arrivant à échéance', 'alert1', 'EMAIL', 6, 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled)
VALUES (4, 'BASKETS', 'Notification de bannettes', 'baskets', 'EMAIL', 7, 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled)
VALUES (5, 'ANC', 'Nouvelle annotation sur courrier en copie', 'noteadd', 'EMAIL', 8, 'copy_list', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled)
VALUES (6, 'AND', 'Nouvelle annotation sur courrier destinataire', 'noteadd', 'EMAIL', 8, 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (notification_sid, notification_id, description, event_id, notification_mode, template_id, diffusion_type, diffusion_properties, attachfor_type, attachfor_properties, is_enabled)
VALUES (7, 'RED', 'Redirection de courrier', '1', 'EMAIL', 7, 'dest_user', '', '', '', 'Y');
INSERT INTO notifications (template_id, notification_id, description, is_enabled, event_id, notification_mode, diffusion_type, diffusion_properties) SELECT template_id, 'QUOTA', 'Alerte lorsque le quota est dépassé', 'Y', 'user_quota', 'EMAIL', 'user', 'superadmin' FROM templates WHERE template_label = 'Quota d''utilisateur';
Select setval('notifications_seq', (select max(notification_sid)+1 from notifications), false);
------------
--TEMPLATES_ASSOCIATION
------------
--Rebuild template association for OFFICE documents
------------
TRUNCATE TABLE templates_association;
INSERT INTO templates_association
(template_id, value_field)
SELECT template_id, entity_id
FROM templates, entities
WHERE template_type in ('OFFICE','TXT');
-----
-- Archive identifiers
-----
UPDATE entities SET business_id = 'org_987654321_DGS_SF';
UPDATE entities SET archival_agency = 'org_123456789_Archives';
UPDATE entities SET archival_agreement = 'MAARCH_LES_BAINS_ACTES_V2';
--UPDATE entities SET business_id = 'org_987654321_DGS_SF' WHERE entity_id = 'COU';
--UPDATE entities SET business_id = 'org_123456789_CAB_SF' WHERE entity_id = 'CAB';

UPDATE doctypes SET retention_final_disposition = 'destruction';
UPDATE doctypes SET retention_rule = 'compta_3_03';
UPDATE doctypes SET duration_current_use = 12;

/* Password Management */
INSERT INTO password_rules (label, "value", enabled) VALUES ('minLength', 6, true);
INSERT INTO password_rules (label, "value") VALUES ('complexityUpper', 0);
INSERT INTO password_rules (label, "value") VALUES ('complexityNumber', 0);
INSERT INTO password_rules (label, "value") VALUES ('complexitySpecial', 0);
INSERT INTO password_rules (label, "value") VALUES ('lockAttempts', 3);
INSERT INTO password_rules (label, "value") VALUES ('lockTime', 5);
INSERT INTO password_rules (label, "value") VALUES ('historyLastUse', 2);
INSERT INTO password_rules (label, "value") VALUES ('renewal', 90);

/* Contacts filling rate*/
INSERT INTO contacts_filling (enable, rating_columns, first_threshold, second_threshold) VALUES (FALSE, '{}', 33, 66);


--Inscrire ici les clauses de conversion spécifiques en cas de reprise
--Update res_letterbox set status='VAL' where res_id=108;
--Update res_letterbox set status='INIT', subject='', destination='COU', type_id=001;
--delete from mlb_coll_ext;
--delete from res_attachments;
--delete from listinstance;
--delete from listinstance_history;
--delete from listinstance_history_details;
--delete from notes;
-- END
