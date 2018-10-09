-- core/sql/structure/core.postgresql.sql

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--DROP PROCEDURAL LANGUAGE IF EXISTS plpgsql CASCADE;
--CREATE PROCEDURAL LANGUAGE plpgsql;

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

CREATE SEQUENCE actions_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 500
  CACHE 1;

CREATE TABLE actions
(
  id integer NOT NULL DEFAULT nextval('actions_id_seq'::regclass),
  keyword character varying(32) NOT NULL DEFAULT ''::bpchar,
  label_action character varying(255),
  id_status character varying(10),
  is_system character(1) NOT NULL DEFAULT 'N'::bpchar,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  action_page character varying(255),
  history character(1) NOT NULL DEFAULT 'N'::bpchar,
  origin character varying(255) NOT NULL DEFAULT 'apps'::bpchar,
  create_id  character(1) NOT NULL DEFAULT 'N'::bpchar,
  category_id character varying(255),
  CONSTRAINT actions_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


CREATE TABLE docserver_types
(
  docserver_type_id character varying(32) NOT NULL,
  docserver_type_label character varying(255) DEFAULT NULL::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  fingerprint_mode character varying(32) DEFAULT NULL::character varying,
  CONSTRAINT docserver_types_pkey PRIMARY KEY (docserver_type_id)
)
WITH (OIDS=FALSE);

CREATE TABLE docservers
(
  id serial,
  docserver_id character varying(32) NOT NULL DEFAULT '1'::character varying,
  docserver_type_id character varying(32) NOT NULL,
  device_label character varying(255) DEFAULT NULL::character varying,
  is_readonly character(1) NOT NULL DEFAULT 'N'::bpchar,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  size_limit_number bigint NOT NULL DEFAULT (0)::bigint,
  actual_size_number bigint NOT NULL DEFAULT (0)::bigint,
  path_template character varying(255) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  coll_id character varying(32) NOT NULL DEFAULT 'coll_1'::character varying,
  CONSTRAINT docservers_pkey PRIMARY KEY (docserver_id),
  CONSTRAINT docservers_id_key UNIQUE (id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE doctypes_type_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 500
  CACHE 1;

CREATE TABLE doctypes
(
  coll_id character varying(32) NOT NULL DEFAULT ''::character varying,
  type_id integer NOT NULL DEFAULT nextval('doctypes_type_id_seq'::regclass),
  description character varying(255) NOT NULL DEFAULT ''::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  doctypes_first_level_id integer,
  doctypes_second_level_id integer,
  retention_final_disposition character varying(255) DEFAULT NULL,
  retention_rule character varying(15) DEFAULT NULL,
  duration_current_use integer,
  CONSTRAINT doctypes_pkey PRIMARY KEY (type_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE history_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE history
(
  id bigint NOT NULL DEFAULT nextval('history_id_seq'::regclass),
  table_name character varying(32) DEFAULT NULL::character varying,
  record_id character varying(255) DEFAULT NULL::character varying,
  event_type character varying(32) NOT NULL,
  user_id character varying(128) NOT NULL,
  event_date timestamp without time zone NOT NULL,
  info text,
  id_module character varying(50) NOT NULL DEFAULT 'admin'::character varying,
  remote_ip character varying(32) DEFAULT NULL,
  event_id character varying(50),
  CONSTRAINT history_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE history_batch_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE history_batch
(
  id bigint NOT NULL DEFAULT nextval('history_batch_id_seq'::regclass),
  module_name character varying(32) DEFAULT NULL::character varying,
  batch_id bigint DEFAULT NULL::bigint,
  event_date timestamp without time zone NOT NULL,
  total_processed bigint DEFAULT NULL::bigint,
  total_errors bigint DEFAULT NULL::bigint,
  info text,
  CONSTRAINT history_batch_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE parameters
(
  id character varying(255) NOT NULL,
  description TEXT,
  param_value_string TEXT DEFAULT NULL::character varying,
  param_value_int integer,
  param_value_date timestamp without time zone,
  CONSTRAINT parameters_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE security_security_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 600
  CACHE 1;

CREATE TABLE "security"
(
  security_id bigint NOT NULL DEFAULT nextval('security_security_id_seq'::regclass),
  group_id character varying(32) NOT NULL,
  coll_id character varying(32) NOT NULL,
  where_clause text,
  maarch_comment text,
  CONSTRAINT security_pkey PRIMARY KEY (security_id)
)
WITH (OIDS=FALSE);

CREATE TABLE status
(
  identifier serial,
  id character varying(10) NOT NULL,
  label_status character varying(50) NOT NULL,
  is_system character(1) NOT NULL DEFAULT 'Y'::bpchar,
  img_filename character varying(255),
  maarch_module character varying(255) NOT NULL DEFAULT 'apps'::character varying,
  can_be_searched character(1) NOT NULL DEFAULT 'Y'::bpchar,
  can_be_modified character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT status_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE status_images
(
  id serial,
  image_name character varying(128) NOT NULL,
  CONSTRAINT status_images_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE usergroup_content
(
  user_id character varying(128) NOT NULL,
  group_id character varying(32) NOT NULL,
  primary_group character(1) NOT NULL,
  "role" character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT usergroup_content_pkey PRIMARY KEY (user_id, group_id)
)
WITH (OIDS=FALSE);

CREATE TABLE usergroups
(
  id serial NOT NULL,
  group_id character varying(32) NOT NULL,
  group_desc character varying(255) DEFAULT NULL::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT usergroups_pkey PRIMARY KEY (group_id),
  CONSTRAINT usergroups_id_key UNIQUE (id)
)
WITH (OIDS=FALSE);

CREATE TABLE usergroups_services
(
  group_id character varying NOT NULL,
  service_id character varying NOT NULL,
  CONSTRAINT usergroups_services_pkey PRIMARY KEY (group_id, service_id)
)
WITH (OIDS=FALSE);

CREATE TABLE users
(
  id serial NOT NULL,
  user_id character varying(128) NOT NULL,
  "password" character varying(255) DEFAULT NULL::character varying,
  firstname character varying(255) DEFAULT NULL::character varying,
  lastname character varying(255) DEFAULT NULL::character varying,
  phone character varying(32) DEFAULT NULL::character varying,
  mail character varying(255) DEFAULT NULL::character varying,
  initials character varying(32) DEFAULT NULL::character varying,
  custom_t1 character varying(50) DEFAULT '0'::character varying,
  custom_t2 character varying(50) DEFAULT NULL::character varying,
  custom_t3 character varying(50) DEFAULT NULL::character varying,
  status character varying(10) NOT NULL DEFAULT 'OK'::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  change_password character(1) NOT NULL DEFAULT 'Y'::bpchar,
  password_modification_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
  loginmode character varying(50) DEFAULT NULL::character varying,
  cookie_key character varying(255) DEFAULT NULL::character varying,
  cookie_date timestamp without time zone,
  failed_authentication INTEGER DEFAULT 0,
  locked_until TIMESTAMP without time zone,
  thumbprint text DEFAULT NULL::character varying,
  CONSTRAINT users_pkey PRIMARY KEY (user_id),
  CONSTRAINT users_id_key UNIQUE (id)
)
WITH (OIDS=FALSE);


-- modules/attachments/sql/structure/attachments.postgresql.sql


CREATE SEQUENCE res_attachment_res_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE res_attachments
(
  res_id bigint NOT NULL DEFAULT nextval('res_attachment_res_id_seq'::regclass),
  title character varying(255) DEFAULT NULL::character varying,
  subject text,
  description text,
  type_id bigint ,
  format character varying(50) NOT NULL,
  typist character varying(128) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  author character varying(255) DEFAULT NULL::character varying,
  identifier character varying(255) DEFAULT NULL::character varying,
  source character varying(255) DEFAULT NULL::character varying,
  relation bigint,
  doc_date timestamp without time zone,
  docserver_id character varying(32) NOT NULL,
  folders_system_id bigint,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  status character varying(10) DEFAULT NULL::character varying,
  destination character varying(50) DEFAULT NULL::character varying,
  validation_date timestamp without time zone,
  effective_date timestamp without time zone,
  work_batch bigint,
  origin character varying(50) DEFAULT NULL::character varying,
  priority character varying(16),
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(128) DEFAULT NULL::character varying,
  coll_id character varying(32) NOT NULL,
  res_id_master bigint,
  attachment_type character varying(255) DEFAULT NULL::character varying,
  dest_contact_id bigint,
  dest_address_id bigint,
  updated_by character varying(128) DEFAULT NULL::character varying,
  is_multicontacts character(1),
  is_multi_docservers character(1) NOT NULL DEFAULT 'N'::bpchar,
  tnl_path character varying(255) DEFAULT NULL::character varying,
  tnl_filename character varying(255) DEFAULT NULL::character varying,
  in_signature_book boolean DEFAULT FALSE,
  signatory_user_serial_id int,
  convert_result character varying(10) DEFAULT NULL::character varying,
  convert_attempts integer DEFAULT NULL::integer,
  fulltext_result character varying(10) DEFAULT NULL::character varying,
  fulltext_attempts integer DEFAULT NULL::integer,
  tnl_result character varying(10) DEFAULT NULL::character varying,
  tnl_attempts integer DEFAULT NULL::integer,
  external_id character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT res_attachments_pkey PRIMARY KEY (res_id)
)
WITH (OIDS=FALSE);

CREATE TABLE adr_attachments
(
  id serial NOT NULL,
  res_id bigint NOT NULL,
  type character varying(32) NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT adr_attachments_pkey PRIMARY KEY (id),
  CONSTRAINT adr_attachments_unique_key UNIQUE (res_id, type)
)
WITH (OIDS=FALSE);


-- modules/basket/sql/structure/basket.postgresql.sql

CREATE TABLE actions_groupbaskets
(
  id_action bigint NOT NULL,
  where_clause text,
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  used_in_basketlist character(1) NOT NULL DEFAULT 'Y'::bpchar,
  used_in_action_page character(1) NOT NULL DEFAULT 'Y'::bpchar,
  default_action_list character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT actions_groupbaskets_pkey PRIMARY KEY (id_action, group_id, basket_id)
)
WITH (OIDS=FALSE);

CREATE TABLE baskets
(
  coll_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  basket_name character varying(255) NOT NULL,
  basket_desc character varying(255) NOT NULL,
  basket_clause text NOT NULL,
  is_visible character(1) NOT NULL DEFAULT 'Y'::bpchar,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  basket_order integer,
  color character varying(16),
  basket_res_order character varying(255) NOT NULL DEFAULT 'res_id',
  flag_notif character varying(1),
  CONSTRAINT baskets_pkey PRIMARY KEY (coll_id, basket_id)
)
WITH (OIDS=FALSE);

CREATE TABLE basket_persistent_mode
(
  res_id bigint,
  user_id character varying(128),
  is_persistent character varying(1)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE res_mark_as_read
(
  coll_id character varying(32),
  res_id bigint,
  user_id character varying(128),
  basket_id character varying(32)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE groupbasket
(
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  result_page character varying(255) DEFAULT 'show_list1.php'::character varying,
  CONSTRAINT groupbasket_pkey PRIMARY KEY (group_id, basket_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE user_abs_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE user_abs
(
  system_id bigint NOT NULL DEFAULT nextval('user_abs_seq'::regclass),
  user_abs character varying(128) NOT NULL,
  new_user character varying(128) NOT NULL,
  basket_id character varying(255) NOT NULL,
  basket_owner character varying(255),
  is_virtual character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT user_abs_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);


-- modules/entities/sql/structure/entities.postgresql.sql


CREATE TABLE entities
(
  entity_id character varying(32) NOT NULL,
  entity_label character varying(255),
  short_label character varying(50),
  entity_full_name text,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  adrs_1 character varying(255),
  adrs_2 character varying(255),
  adrs_3 character varying(255),
  zipcode character varying(32),
  city character varying(255),
  country character varying(255),
  email character varying(255),
  business_id character varying(32),
  parent_entity_id character varying(32),
  entity_type character varying(64),
  entity_path character varying(2048),
  ldap_id character varying(255),
  archival_agency character varying(255),
  archival_agreement character varying(255),
  folder_import character varying(64),
  CONSTRAINT entities_pkey PRIMARY KEY (entity_id),
  CONSTRAINT entities_folder_import_unique_key UNIQUE (folder_import)
)
WITH (OIDS=FALSE);


CREATE SEQUENCE listinstance_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE listinstance
(
  listinstance_id BIGINT NOT NULL DEFAULT nextval('listinstance_id_seq'::regclass),
  coll_id character varying(50) NOT NULL,
  res_id bigint NOT NULL,
  listinstance_type character varying(50) DEFAULT 'DOC'::character varying,
  "sequence" bigint NOT NULL,
  item_id character varying(128) NOT NULL,
  item_type character varying(255) NOT NULL,
  item_mode character varying(50) NOT NULL,
  added_by_user character varying(128) NOT NULL,
  added_by_entity character varying(50) NOT NULL,
  visible character varying(1) NOT NULL DEFAULT 'Y'::bpchar,
  viewed bigint,
  difflist_type character varying(50),
  process_date timestamp without time zone,
  process_comment character varying(255),
  signatory boolean default false,
  requested_signature boolean default false,
  CONSTRAINT listinstance_pkey PRIMARY KEY (listinstance_id)
)
WITH (OIDS=FALSE);

CREATE TABLE listmodels
(
  id serial NOT NULL,
  object_id character varying(50) NOT NULL,
  object_type character varying(255) NOT NULL,
  "sequence" bigint NOT NULL,
  item_id character varying(128) NOT NULL,
  item_type character varying(255) NOT NULL,
  item_mode character varying(50) NOT NULL,
  title character varying(255),
  description character varying(255),
  process_comment character varying(255),
  visible character varying(1) NOT NULL DEFAULT 'Y'::bpchar
)
WITH (OIDS=FALSE);

CREATE TABLE difflist_types 
(
  difflist_type_id character varying(50) NOT NULL,
  difflist_type_label character varying(100) NOT NULL,
  difflist_type_roles TEXT,
  allow_entities character varying(1) NOT NULL DEFAULT 'N'::bpchar,
  is_system character varying(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT "difflist_types_pkey" PRIMARY KEY (difflist_type_id)
)
WITH (
    OIDS=FALSE
);

CREATE TABLE users_entities
(
  user_id character varying(128) NOT NULL,
  entity_id character varying(32) NOT NULL,
  user_role character varying(255),
  primary_entity character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT users_entities_pkey PRIMARY KEY (user_id, entity_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE groupbasket_redirect_system_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 600
  CACHE 1;

CREATE TABLE groupbasket_redirect
(
  system_id integer NOT NULL DEFAULT nextval('groupbasket_redirect_system_id_seq'::regclass),
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  action_id int NOT NULL,
  entity_id character varying(32),
  keyword character varying(255),
  redirect_mode character varying(32) NOT NULL,
  CONSTRAINT groupbasket_redirect_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE email_signatures_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 7
  CACHE 1;

CREATE TABLE users_email_signatures
(
  id bigint NOT NULL DEFAULT nextval('email_signatures_id_seq'::regclass),
  user_id character varying(255) NOT NULL,
  html_body text NOT NULL,
  title character varying NOT NULL,
  CONSTRAINT email_signatures_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);

-- modules/folder/sql/structure/folder.postgresql.sql

CREATE SEQUENCE folders_system_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 10000
  CACHE 1;

CREATE TABLE folders
(
  folders_system_id bigint NOT NULL DEFAULT nextval('folders_system_id_seq'::regclass),
  folder_id character varying(255) NOT NULL,
  foldertype_id integer,
  parent_id bigint DEFAULT (0)::bigint,
  folder_name character varying(255) DEFAULT NULL::character varying,
  subject character varying(255) DEFAULT NULL::character varying,
  description character varying(255) DEFAULT NULL::character varying,
  author character varying(255) DEFAULT NULL::character varying,
  typist character varying(255) DEFAULT NULL::character varying,
  status character varying(50) NOT NULL DEFAULT 'FOLDNEW'::character varying,
  folder_level smallint DEFAULT (1)::smallint,
  creation_date timestamp without time zone NOT NULL,
  destination character varying(50) DEFAULT NULL,
  dest_user character varying(128) DEFAULT NULL,
  folder_out_id bigint,
  video_status character varying(10) DEFAULT NULL,
  video_user character varying(128) DEFAULT NULL,
  is_frozen character(1) NOT NULL DEFAULT 'N',
  custom_t1 character varying(255) DEFAULT NULL::character varying,
  custom_n1 bigint,
  custom_f1 numeric,
  custom_d1 timestamp without time zone,
  custom_t2 character varying(255) DEFAULT NULL::character varying,
  custom_n2 bigint,
  custom_f2 numeric,
  custom_d2 timestamp without time zone,
  custom_t3 character varying(255) DEFAULT NULL::character varying,
  custom_n3 bigint,
  custom_f3 numeric,
  custom_d3 timestamp without time zone,
  custom_t4 character varying(255) DEFAULT NULL::character varying,
  custom_n4 bigint,
  custom_f4 numeric,
  custom_d4 timestamp without time zone,
  custom_t5 character varying(255) DEFAULT NULL::character varying,
  custom_n5 bigint,
  custom_f5 numeric,
  custom_d5 timestamp without time zone,
  custom_t6 character varying(255) DEFAULT NULL::character varying,
  custom_d6 timestamp without time zone,
  custom_t7 character varying(255) DEFAULT NULL::character varying,
  custom_d7 timestamp without time zone,
  custom_t8 character varying(255) DEFAULT NULL::character varying,
  custom_d8 timestamp without time zone,
  custom_t9 character varying(255) DEFAULT NULL::character varying,
  custom_d9 timestamp without time zone,
  custom_t10 character varying(255) DEFAULT NULL::character varying,
  custom_d10 timestamp without time zone,
  custom_t11 character varying(255) DEFAULT NULL::character varying,
  custom_d11 timestamp without time zone,
  custom_t12 character varying(255) DEFAULT NULL::character varying,
  custom_d12 timestamp without time zone,
  custom_t13 character varying(255) DEFAULT NULL::character varying,
  custom_d13 timestamp without time zone,
  custom_t14 character varying(255) DEFAULT NULL::character varying,
  custom_d14 timestamp without time zone,
  custom_t15 character varying(255) DEFAULT NULL::character varying,
  is_complete character(1) DEFAULT 'N'::bpchar,
  is_folder_out character(1) DEFAULT 'N'::bpchar,
  last_modified_date timestamp without time zone,
  CONSTRAINT folders_pkey PRIMARY KEY (folders_system_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE foldertype_id_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 200
  CACHE 1;

CREATE TABLE foldertypes
(
  foldertype_id  bigint NOT NULL DEFAULT nextval('foldertype_id_id_seq'::regclass),
  foldertype_label character varying(255) NOT NULL,
  maarch_comment text,
  retention_time character varying(50),
  custom_d1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d6 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t6 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d7 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t7 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d8 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t8 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d9 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t9 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d10 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t10 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t11 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t12 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t13 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t14 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t15 character varying(10) DEFAULT '0000000000'::character varying,
  coll_id character varying(32),
  CONSTRAINT foldertypes_pkey PRIMARY KEY (foldertype_id)
)
WITH (OIDS=FALSE);

CREATE TABLE foldertypes_doctypes
(
  foldertype_id integer NOT NULL,
  doctype_id integer NOT NULL,
  CONSTRAINT foldertypes_doctypes_pkey PRIMARY KEY (foldertype_id, doctype_id)
)
WITH (OIDS=FALSE);

CREATE TABLE foldertypes_doctypes_level1
(
  foldertype_id integer NOT NULL,
  doctypes_first_level_id integer NOT NULL,
  CONSTRAINT foldertypes_doctypes_level1_pkey PRIMARY KEY (foldertype_id, doctypes_first_level_id)
)
WITH (OIDS=FALSE);

CREATE TABLE foldertypes_indexes
(
  foldertype_id bigint NOT NULL,
  field_name character varying(255) NOT NULL,
  mandatory character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT foldertypes_indexes_pkey PRIMARY KEY (foldertype_id, field_name)
)
WITH (OIDS=FALSE);


-- modules/notes/sql/structure/notes.postgresql.sql

CREATE SEQUENCE notes_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;


CREATE TABLE notes
(
  id bigint NOT NULL DEFAULT nextval('notes_seq'::regclass),
  identifier bigint NOT NULL,
  tablename character varying(50),
  user_id character varying(128) NOT NULL,
  date_note timestamp without time zone NOT NULL,
  note_text text NOT NULL,
  coll_id character varying(50),
  CONSTRAINT notes_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


CREATE SEQUENCE notes_entities_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;


CREATE TABLE note_entities
(
  id bigint NOT NULL DEFAULT nextval('notes_entities_id_seq'::regclass),
  note_id bigint NOT NULL,
  item_id character varying(50),
  CONSTRAINT note_entities_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);



-- modules/notes/sql/structure/notifications.postgresql.sql
CREATE SEQUENCE notifications_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE notifications
(
  notification_sid bigint NOT NULL DEFAULT nextval('notifications_seq'::regclass),
  notification_id character varying(50) NOT NULL,
  description character varying(255),
  is_enabled character varying(1) NOT NULL default 'Y'::bpchar,
  event_id character varying(255) NOT NULL,
  notification_mode character varying(30) NOT NULL,
  template_id bigint,
  diffusion_type character varying(50) NOT NULL,
  diffusion_properties text,
  attachfor_type character varying(50),
  attachfor_properties character varying(2048),
  CONSTRAINT notifications_pkey PRIMARY KEY (notification_sid)
)
WITH (
  OIDS=FALSE
);


CREATE SEQUENCE notif_event_stack_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

 -- DROP TABLE notif_event_stack
CREATE TABLE notif_event_stack
(
  event_stack_sid bigint NOT NULL DEFAULT nextval('notif_event_stack_seq'::regclass),
  notification_sid bigint NOT NULL,
  table_name character varying(50) NOT NULL,
  record_id character varying(128) NOT NULL,
  user_id character varying(128) NOT NULL,
  event_info character varying(255) NOT NULL,
  event_date timestamp without time zone NOT NULL,
  exec_date timestamp without time zone,
  exec_result character varying(50),
  CONSTRAINT notif_event_stack_pkey PRIMARY KEY (event_stack_sid)
)
WITH (
  OIDS=FALSE
);

CREATE SEQUENCE notif_email_stack_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

 -- DROP TABLE notif_email_stack
CREATE TABLE notif_email_stack
(
  email_stack_sid bigint NOT NULL DEFAULT nextval('notif_email_stack_seq'::regclass),
  sender character varying(255) NOT NULL,
  reply_to character varying(255),
  recipient character varying(2000) NOT NULL,
  cc character varying(2000),
  bcc character varying(2000),
  subject character varying(255),
  html_body text,
  text_body text,
  charset character varying(50) NOT NULL,
  attachments character varying(2000),
  module character varying(50) NOT NULL,
  exec_date timestamp without time zone,
  exec_result character varying(50),
  CONSTRAINT notif_email_stack_pkey PRIMARY KEY (email_stack_sid)
)
WITH (
  OIDS=FALSE
);


-- modules/postindexing/sql/structure/postindexing.postgresql.sql



-- modules/reports/sql/structure/reports.postgresql.sql

CREATE TABLE usergroups_reports
(
  group_id character varying(32) NOT NULL,
  report_id character varying(50) NOT NULL,
  CONSTRAINT usergroups_reports_pkey PRIMARY KEY (group_id, report_id)
)
WITH (OIDS=FALSE);


-- modules/templates/sql/structure/templates.postgresql.sql


CREATE SEQUENCE templates_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 110
  CACHE 1;

CREATE TABLE templates
(
  template_id bigint NOT NULL DEFAULT nextval('templates_seq'::regclass),
  template_label character varying(255) DEFAULT NULL::character varying,
  template_comment character varying(255) DEFAULT NULL::character varying,
  template_content text,
  template_type character varying(32) NOT NULL DEFAULT 'HTML'::character varying,
  template_path character varying(255),
  template_file_name character varying(255),
  template_style character varying(255),
  template_datasource character varying(32),
  template_target character varying(255),
  template_attachment_type character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT templates_pkey PRIMARY KEY (template_id)
)
WITH (OIDS=FALSE);

CREATE TABLE templates_association
(
  id serial,
  template_id bigint NOT NULL,
  value_field character varying(255) NOT NULL,
  CONSTRAINT templates_association_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);


CREATE TABLE templates_doctype_ext
(
  template_id bigint DEFAULT NULL,
  type_id integer NOT NULL,
  is_generated character(1) NOT NULL DEFAULT 'N'::bpchar
)
WITH (OIDS=FALSE);


CREATE SEQUENCE query_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 10
  CACHE 1;

  -- multicontacts
CREATE TABLE contacts_res
(
  coll_id character varying(32) NOT NULL,
  res_id bigint NOT NULL,
  contact_id character varying(128) NOT NULL,
  address_id bigint NOT NULL,
  mode character varying NOT NULL DEFAULT 'multi'::character varying
 );

-- contacts v2
CREATE SEQUENCE contact_types_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 200
  CACHE 1;

CREATE TABLE contact_types 
(
  id bigint NOT NULL DEFAULT nextval('contact_types_id_seq'::regclass),
  label character varying(255) NOT NULL,
  can_add_contact character varying(1) NOT NULL DEFAULT 'Y'::character varying,
  contact_target character varying(50),
  CONSTRAINT contact_types_pkey PRIMARY KEY  (id)
) WITH (OIDS=FALSE);

CREATE SEQUENCE contact_v2_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contacts_v2 
(
  contact_id bigint NOT NULL DEFAULT nextval('contact_v2_id_seq'::regclass),
  contact_type bigint NOT NULL,
  is_corporate_person character(1) DEFAULT 'Y'::bpchar,
  is_external_contact character(1) DEFAULT 'N'::bpchar,
  society character varying(255),
  society_short character varying(32),
  firstname character varying(255),
  lastname character varying(255),
  title character varying(255),
  function character varying(255),
  other_data text,
  user_id character varying(255) NOT NULL,
  entity_id character varying(32) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  update_date timestamp without time zone,
  enabled character varying(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT contacts_v2_pkey PRIMARY KEY  (contact_id)
) WITH (OIDS=FALSE);

CREATE SEQUENCE contact_purposes_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contact_purposes 
(
  id bigint NOT NULL DEFAULT nextval('contact_purposes_id_seq'::regclass),
  label character varying(255) NOT NULL,
  CONSTRAINT contact_purposes_pkey PRIMARY KEY  (id)
) WITH (OIDS=FALSE);

CREATE SEQUENCE contact_addresses_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contact_addresses 
(
  id bigint NOT NULL DEFAULT nextval('contact_addresses_id_seq'::regclass),
  contact_id bigint NOT NULL,
  contact_purpose_id bigint DEFAULT 1,
  departement character varying(255),
  firstname character varying(255),
  lastname character varying(255),
  title character varying(255),
  function character varying(255),
  occupancy character varying(1024),
  address_num character varying(32)  ,
  address_street character varying(255),
  address_complement character varying(255),
  address_town character varying(255),
  address_postal_code character varying(255),
  address_country character varying(255),
  phone character varying(20),
  email character varying(255),
  website character varying(255),
  salutation_header character varying(255),
  salutation_footer character varying(255),
  other_data character varying(255),
  user_id character varying(255) NOT NULL,
  entity_id character varying(32) NOT NULL,
  is_private character(1) NOT NULL DEFAULT 'N'::bpchar,
  enabled character varying(1) NOT NULL DEFAULT 'Y'::bpchar,
  external_contact_id character varying(128),
  ban_id character varying(128),
  CONSTRAINT contact_addresses_pkey PRIMARY KEY  (id)
) WITH (OIDS=FALSE);

DROP TABLE IF EXISTS contacts_groups;
CREATE TABLE contacts_groups
(
  id serial,
  label character varying(32) NOT NULL,
  description character varying(255) NOT NULL,
  public boolean NOT NULL,
  owner integer NOT NULL,
  entity_owner character varying(32) NOT NULL,
  CONSTRAINT contacts_groups_pkey PRIMARY KEY (id),
  CONSTRAINT contacts_groups_key UNIQUE (label, owner)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS contacts_groups_lists;
CREATE TABLE contacts_groups_lists
(
  id serial,
  contacts_groups_id integer NOT NULL,
  contact_addresses_id integer NOT NULL,
  CONSTRAINT contacts_groups_lists_pkey PRIMARY KEY (id),
  CONSTRAINT contacts_groups_lists_key UNIQUE (contacts_groups_id, contact_addresses_id)
)
WITH (OIDS=FALSE);

CREATE TABLE saved_queries (
  query_id bigint NOT NULL DEFAULT nextval('query_id_seq'::regclass),
  user_id character varying(128)  default NULL,
  query_name character varying(255) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  created_by character varying(128)  NOT NULL,
  query_type character varying(50) NOT NULL,
  query_txt text  NOT NULL,
  last_modification_date timestamp without time zone,
  CONSTRAINT saved_queries_pkey PRIMARY KEY  (query_id)
) WITH (OIDS=FALSE);

CREATE SEQUENCE doctypes_first_level_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 200
  CACHE 1;

CREATE TABLE doctypes_first_level
(
  doctypes_first_level_id integer NOT NULL DEFAULT nextval('doctypes_first_level_id_seq'::regclass),
  doctypes_first_level_label character varying(255) NOT NULL,
  css_style character varying(255),
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT doctypes_first_level_pkey PRIMARY KEY (doctypes_first_level_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE doctypes_second_level_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 200
  CACHE 1;

CREATE TABLE doctypes_second_level
(
  doctypes_second_level_id integer NOT NULL DEFAULT nextval('doctypes_second_level_id_seq'::regclass),
  doctypes_second_level_label character varying(255) NOT NULL,
  doctypes_first_level_id integer NOT NULL,
  css_style character varying(255),
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT doctypes_second_level_pkey PRIMARY KEY (doctypes_second_level_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE tag_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 7
  CACHE 1;

CREATE TABLE tags
(
  tag_id bigint NOT NULL DEFAULT nextval('tag_id_seq'::regclass),
  tag_label character varying(50) NOT NULL,
  coll_id character varying(50) NOT NULL,
  entity_id_owner character varying(32),
  CONSTRAINT tag_id_pkey PRIMARY KEY (tag_id)
)
WITH (OIDS=FALSE);

CREATE TABLE tag_res
(
  res_id bigint NOT NULL,
  tag_id bigint NOT NULL,
  CONSTRAINT tag_res_pkey PRIMARY KEY (res_id,tag_id)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE tags_entities
(
  tag_id bigint,
  entity_id character varying(32),
  CONSTRAINT tags_entities_pkey PRIMARY KEY (tag_id,entity_id)
)
WITH (
  OIDS=FALSE
);

CREATE SEQUENCE res_id_mlb_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE res_letterbox
(
  res_id bigint NOT NULL DEFAULT nextval('res_id_mlb_seq'::regclass),
  title character varying(255) DEFAULT NULL::character varying,
  subject text,
  description text,
  type_id bigint NOT NULL,
  format character varying(50) NOT NULL,
  typist character varying(128) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  modification_date timestamp without time zone DEFAULT NOW(),
  converter_result character varying(10) DEFAULT NULL,
  author character varying(255) DEFAULT NULL::character varying,
  identifier character varying(255) DEFAULT NULL::character varying,
  source character varying(255) DEFAULT NULL::character varying,
  relation bigint,
  doc_date timestamp without time zone,
  docserver_id character varying(32) NOT NULL,
  folders_system_id bigint,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  status character varying(10) NOT NULL,
  destination character varying(50) DEFAULT NULL::character varying,
  validation_date timestamp without time zone,
  work_batch bigint,
  origin character varying(50) DEFAULT NULL::character varying,
  priority character varying(16),
  policy_id character varying(32) DEFAULT NULL::character varying,
  cycle_id character varying(32) DEFAULT NULL::character varying,
  is_multi_docservers character(1) NOT NULL DEFAULT 'N'::bpchar,
  custom_t1 text,
  custom_n1 bigint,
  custom_f1 numeric,
  custom_d1 timestamp without time zone,
  custom_t2 character varying(255) DEFAULT NULL::character varying,
  custom_n2 bigint,
  custom_f2 numeric,
  custom_d2 timestamp without time zone,
  custom_t3 character varying(255) DEFAULT NULL::character varying,
  custom_n3 bigint,
  custom_f3 numeric,
  custom_d3 timestamp without time zone,
  custom_t4 character varying(255) DEFAULT NULL::character varying,
  custom_n4 bigint,
  custom_f4 numeric,
  custom_d4 timestamp without time zone,
  custom_t5 character varying(255) DEFAULT NULL::character varying,
  custom_n5 bigint,
  custom_f5 numeric,
  custom_d5 timestamp without time zone,
  custom_t6 text DEFAULT NULL::character varying,
  custom_d6 timestamp without time zone,
  custom_t7 character varying(255) DEFAULT NULL::character varying,
  custom_d7 timestamp without time zone,
  custom_t8 character varying(255) DEFAULT NULL::character varying,
  custom_d8 timestamp without time zone,
  custom_t9 character varying(255) DEFAULT NULL::character varying,
  custom_d9 timestamp without time zone,
  custom_t10 character varying(255) DEFAULT NULL::character varying,
  custom_d10 timestamp without time zone,
  custom_t11 character varying(255) DEFAULT NULL::character varying,
  custom_t12 character varying(255) DEFAULT NULL::character varying,
  custom_t13 character varying(255) DEFAULT NULL::character varying,
  custom_t14 character varying(255) DEFAULT NULL::character varying,
  custom_t15 character varying(255) DEFAULT NULL::character varying,
  reference_number character varying(255) DEFAULT NULL::character varying,
  tablename character varying(32) DEFAULT 'res_letterbox'::character varying,
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(128) DEFAULT NULL::character varying,
  locker_user_id character varying(255) DEFAULT NULL::character varying,
  locker_time timestamp without time zone,
  confidentiality character(1),
  convert_result character varying(10) DEFAULT NULL::character varying,
  convert_attempts integer DEFAULT NULL::integer,
  fulltext_result character varying(10) DEFAULT NULL::character varying,
  fulltext_attempts integer DEFAULT NULL::integer,
  tnl_result character varying(10) DEFAULT NULL::character varying,
  tnl_attempts integer DEFAULT NULL::integer,
  external_id character varying(255) DEFAULT NULL::character varying,
  external_link character varying(255) DEFAULT NULL::character varying,
  departure_date timestamp without time zone,
  department_number_id text,
  barcode text,
  sve_start_date TIMESTAMP without time zone,
  CONSTRAINT res_letterbox_pkey PRIMARY KEY  (res_id)
)
WITH (OIDS=FALSE);

CREATE TABLE adr_letterbox
(
  id serial NOT NULL,
  res_id bigint NOT NULL,
  type character varying(32) NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT adr_letterbox_pkey PRIMARY KEY (id),
  CONSTRAINT adr_letterbox_unique_key UNIQUE (res_id, type)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE res_linked_mlb_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 171
  CACHE 1;

CREATE TABLE res_linked
(
  id bigint NOT NULL DEFAULT nextval('res_linked_mlb_seq'::regclass),
  res_parent bigint NOT NULL,
  res_child bigint NOT NULL,
  coll_id character varying(50) NOT NULL,
  CONSTRAINT res_linked_primary PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE mlb_coll_ext (
  res_id bigint NOT NULL,
  category_id character varying(50)  NOT NULL,
  exp_contact_id integer default NULL,
  exp_user_id character varying(128) default NULL,
  dest_contact_id integer default NULL,
  dest_user_id character varying(128) default NULL,
  nature_id character varying(50),
  alt_identifier character varying(255)  default NULL,
  admission_date timestamp without time zone,
  process_limit_date timestamp without time zone default NULL,
  recommendation_limit_date timestamp without time zone default NULL,
  closing_date timestamp without time zone default NULL,
  alarm1_date timestamp without time zone default NULL,
  alarm2_date timestamp without time zone default NULL,
  flag_notif char(1)  default 'N'::character varying ,
  flag_alarm1 char(1)  default 'N'::character varying ,
  flag_alarm2 char(1) default 'N'::character varying,
  is_multicontacts char(1),
  address_id bigint
)WITH (OIDS=FALSE);

CREATE TABLE mlb_doctype_ext (
  type_id bigint NOT NULL,
  process_delay bigint NOT NULL DEFAULT '21',
  delay1 bigint NOT NULL DEFAULT '14',
  delay2 bigint NOT NULL DEFAULT '1',
  process_mode character varying(255),
  CONSTRAINT type_id PRIMARY KEY (type_id)
)
WITH (OIDS=FALSE);

CREATE TABLE doctypes_indexes
(
  type_id bigint NOT NULL,
  coll_id character varying(32) NOT NULL,
  field_name character varying(255) NOT NULL,
  mandatory character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT doctypes_indexes_pkey PRIMARY KEY (type_id, coll_id, field_name)
)
WITH (OIDS=FALSE);

CREATE TABLE groupbasket_status
(
  system_id serial NOT NULL,
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  action_id integer NOT NULL,
  status_id character varying(32),
  "order" integer NOT NULL,
  CONSTRAINT groupbasket_status_pkey PRIMARY KEY (system_id)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE user_signatures
(
  id serial,
  user_serial_id integer NOT NULL,
  signature_label character varying(255) DEFAULT NULL::character varying,
  signature_path character varying(255) DEFAULT NULL::character varying,
  signature_file_name character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT user_signatures_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE priorities
(
  id character varying(16) NOT NULL,
  label character varying(128) NOT NULL,
  color character varying(128) NOT NULL,
  working_days boolean NOT NULL,
  delays integer,
  default_priority boolean NOT NULL DEFAULT FALSE,
  "order" integer,
  CONSTRAINT priorities_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

-- sendmail module
CREATE TABLE sendmail
(
  email_id serial NOT NULL,
  coll_id character varying(32) NOT NULL,
  res_id bigint,
  user_id character varying(128) NOT NULL,
  to_list text DEFAULT NULL,
  cc_list text DEFAULT NULL,
  cci_list text DEFAULT NULL,
  email_object character varying(255) DEFAULT NULL,
  email_body text,
  is_res_master_attached character varying(1) NOT NULL DEFAULT 'Y',
  res_version_id_list character varying(255) DEFAULT NULL,
  res_attachment_id_list character varying(255) DEFAULT NULL,
  res_version_att_id_list character varying(255) DEFAULT NULL,
  note_id_list character varying(255) DEFAULT NULL,
  is_html character varying(1) NOT NULL DEFAULT 'Y',
  email_status character varying(1) NOT NULL DEFAULT 'D',
  creation_date timestamp without time zone NOT NULL,
  send_date timestamp without time zone DEFAULT NULL,
  sender_email character varying(255) DEFAULT NULL,
  message_exchange_id text DEFAULT NULL,
  CONSTRAINT sendmail_pkey PRIMARY KEY (email_id )
 );

DROP TABLE IF EXISTS actions_categories;
CREATE TABLE actions_categories
(
  action_id bigint NOT NULL,
  category_id character varying(255) NOT NULL,
  CONSTRAINT actions_categories_pkey PRIMARY KEY (action_id,category_id)
);

DROP SEQUENCE IF EXISTS listinstance_history_id_seq;
CREATE SEQUENCE listinstance_history_id_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

DROP TABLE IF EXISTS listinstance_history;
CREATE TABLE listinstance_history
(
listinstance_history_id bigint NOT NULL DEFAULT nextval('listinstance_history_id_seq'::regclass),
coll_id character varying(50) NOT NULL,
res_id bigint NOT NULL,
updated_by_user character varying(128) NOT NULL,
updated_date timestamp without time zone NOT NULL,
CONSTRAINT listinstance_history_pkey PRIMARY KEY (listinstance_history_id)
)
WITH ( OIDS=FALSE );

DROP SEQUENCE IF EXISTS listinstance_history_details_id_seq;
CREATE SEQUENCE listinstance_history_details_id_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

DROP TABLE IF EXISTS listinstance_history_details;
CREATE TABLE listinstance_history_details
(
listinstance_history_details_id bigint NOT NULL DEFAULT nextval('listinstance_history_details_id_seq'::regclass),
listinstance_history_id bigint NOT NULL,
coll_id character varying(50) NOT NULL,
res_id bigint NOT NULL,
listinstance_type character varying(50) DEFAULT 'DOC'::character varying,
sequence bigint NOT NULL,
item_id character varying(128) NOT NULL,
item_type character varying(255) NOT NULL,
item_mode character varying(50) NOT NULL,
added_by_user character varying(128) NOT NULL,
added_by_entity character varying(50) NOT NULL,
visible character varying(1) NOT NULL DEFAULT 'Y'::bpchar,
viewed bigint,
difflist_type character varying(50),
process_date timestamp without time zone,
process_comment character varying(255),
CONSTRAINT listinstance_history_details_pkey PRIMARY KEY (listinstance_history_details_id)
) WITH ( OIDS=FALSE );


-- View folders
DROP VIEW IF EXISTS view_folders;
CREATE VIEW view_folders AS 
SELECT folders.folders_system_id, folders.folder_id, folders.foldertype_id, foldertypes.foldertype_label, (folders.folder_id || ':') || folders.folder_name AS folder_full_label, folders.parent_id, folders.folder_name, folders.subject, folders.description, folders.author, folders.typist, folders.status, folders.folder_level, 
folders.creation_date, folders.destination, folders.dest_user, 
folders.folder_out_id, folders.custom_t1, folders.custom_n1, folders.custom_f1, folders.custom_d1, folders.custom_t2, folders.custom_n2, folders.custom_f2, folders.custom_d2, folders.custom_t3, folders.custom_n3, folders.custom_f3, folders.custom_d3, folders.custom_t4, folders.custom_n4, folders.custom_f4, folders.custom_d4, folders.custom_t5, folders.custom_n5, folders.custom_f5, folders.custom_d5, folders.custom_t6, folders.custom_d6, folders.custom_t7, folders.custom_d7, folders.custom_t8, folders.custom_d8, folders.custom_t9, folders.custom_d9, folders.custom_t10, folders.custom_d10, folders.custom_t11, folders.custom_d11, folders.custom_t12, folders.custom_d12, folders.custom_t13, folders.custom_d13, folders.custom_t14, folders.custom_d14, folders.custom_t15, folders.is_complete, folders.is_folder_out, folders.last_modified_date, folders.video_status, COALESCE(r.count_document, 0::bigint) AS count_document
   FROM foldertypes, folders
   LEFT JOIN ( SELECT res_letterbox.folders_system_id, count(res_letterbox.folders_system_id) AS count_document
           FROM res_letterbox
          GROUP BY res_letterbox.folders_system_id) r ON r.folders_system_id = folders.folders_system_id
  WHERE folders.foldertype_id = foldertypes.foldertype_id;

--view for contacts_v2
DROP VIEW IF EXISTS view_contacts;
CREATE OR REPLACE VIEW view_contacts AS 
 SELECT c.contact_id, c.contact_type, c.is_corporate_person, c.society, c.society_short, c.firstname AS contact_firstname
, c.lastname AS contact_lastname, c.title AS contact_title, c.function AS contact_function, c.other_data AS contact_other_data
, c.user_id AS contact_user_id, c.entity_id AS contact_entity_id, c.creation_date, c.update_date, c.enabled AS contact_enabled, ca.id AS ca_id
, ca.contact_purpose_id, ca.departement, ca.firstname, ca.lastname, ca.title, ca.function, ca.occupancy
, ca.address_num, ca.address_street, ca.address_complement, ca.address_town, ca.address_postal_code, ca.address_country
, ca.phone, ca.email, ca.website, ca.salutation_header, ca.salutation_footer, ca.other_data, ca.user_id, ca.entity_id, ca.is_private, ca.enabled, ca.external_contact_id
, cp.label as contact_purpose_label, ct.label as contact_type_label
   FROM contacts_v2 c
   RIGHT JOIN contact_addresses ca ON c.contact_id = ca.contact_id
   LEFT JOIN contact_purposes cp ON ca.contact_purpose_id = cp.id
   LEFT JOIN contact_types ct ON c.contact_type = ct.id;

DROP TABLE IF EXISTS res_version_attachments;
DROP SEQUENCE IF EXISTS res_id_version_attachments_seq;

   CREATE SEQUENCE res_id_version_attachments_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE res_version_attachments
(
  res_id bigint NOT NULL DEFAULT nextval('res_id_version_attachments_seq'::regclass),
  title character varying(255) DEFAULT NULL::character varying,
  subject text,
  description text,
  type_id bigint NOT NULL,
  format character varying(50) NOT NULL,
  typist character varying(128) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  converter_result character varying(10) DEFAULT NULL::character varying,
  author character varying(255) DEFAULT NULL::character varying,
  identifier character varying(255) DEFAULT NULL::character varying,
  source character varying(255) DEFAULT NULL::character varying,
  relation bigint,
  doc_date timestamp without time zone,
  docserver_id character varying(32) NOT NULL,
  folders_system_id bigint,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  status character varying(10) NOT NULL,
  destination character varying(50) DEFAULT NULL::character varying,
  validation_date timestamp without time zone,
  effective_date timestamp without time zone,
  work_batch bigint,
  origin character varying(50) DEFAULT NULL::character varying,
  priority character varying(16),
  policy_id character varying(32),
  cycle_id character varying(32),
  is_multi_docservers character(1) NOT NULL DEFAULT 'N'::bpchar,
  custom_t1 text,
  custom_n1 bigint,
  custom_f1 numeric,
  custom_d1 timestamp without time zone,
  custom_t2 character varying(255) DEFAULT NULL::character varying,
  custom_n2 bigint,
  custom_f2 numeric,
  custom_d2 timestamp without time zone,
  custom_t3 character varying(255) DEFAULT NULL::character varying,
  custom_n3 bigint,
  custom_f3 numeric,
  custom_d3 timestamp without time zone,
  custom_t4 character varying(255) DEFAULT NULL::character varying,
  custom_n4 bigint,
  custom_f4 numeric,
  custom_d4 timestamp without time zone,
  custom_t5 character varying(255) DEFAULT NULL::character varying,
  custom_n5 bigint,
  custom_f5 numeric,
  custom_d5 timestamp without time zone,
  custom_t6 character varying(255) DEFAULT NULL::character varying,
  custom_d6 timestamp without time zone,
  custom_t7 character varying(255) DEFAULT NULL::character varying,
  custom_d7 timestamp without time zone,
  custom_t8 character varying(255) DEFAULT NULL::character varying,
  custom_d8 timestamp without time zone,
  custom_t9 character varying(255) DEFAULT NULL::character varying,
  custom_d9 timestamp without time zone,
  custom_t10 character varying(255) DEFAULT NULL::character varying,
  custom_d10 timestamp without time zone,
  custom_t11 character varying(255) DEFAULT NULL::character varying,
  custom_t12 character varying(255) DEFAULT NULL::character varying,
  custom_t13 character varying(255) DEFAULT NULL::character varying,
  custom_t14 character varying(255) DEFAULT NULL::character varying,
  custom_t15 character varying(255) DEFAULT NULL::character varying,
  tablename character varying(32) DEFAULT 'res_version_attachments'::character varying,
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(128) DEFAULT NULL::character varying,
  coll_id character varying(32) NOT NULL,
  attachment_type character varying(255) DEFAULT NULL::character varying,
  dest_contact_id bigint,
  dest_address_id bigint,
  updated_by character varying(128) DEFAULT NULL::character varying,
  is_multicontacts character(1),
  res_id_master bigint,
  attachment_id_master bigint,
  in_signature_book boolean DEFAULT FALSE,
  signatory_user_serial_id int,
  convert_result character varying(10) DEFAULT NULL::character varying,
  convert_attempts integer DEFAULT NULL::integer,
  fulltext_result character varying(10) DEFAULT NULL::character varying,
  fulltext_attempts integer DEFAULT NULL::integer,
  tnl_result character varying(10) DEFAULT NULL::character varying,
  tnl_attempts integer DEFAULT NULL::integer,
  external_id character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT res_version_attachments_pkey PRIMARY KEY (res_id)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE adr_attachments_version
(
  id serial NOT NULL,
  res_id bigint NOT NULL,
  type character varying(32) NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT adr_attachments_version_pkey PRIMARY KEY (id),
  CONSTRAINT adr_attachments_version_unique_key UNIQUE (res_id, type)
)
WITH (OIDS=FALSE);

CREATE FUNCTION order_alphanum(text) RETURNS text AS $$
  SELECT regexp_replace(regexp_replace(regexp_replace(regexp_replace($1,
    E'(^|\\D)(\\d{1,3}($|\\D))', E'\\1000\\2', 'g'),
      E'(^|\\D)(\\d{4,6}($|\\D))', E'\\1000\\2', 'g'),
        E'(^|\\D)(\\d{7}($|\\D))', E'\\100\\2', 'g'),
          E'(^|\\D)(\\d{8}($|\\D))', E'\\10\\2', 'g');
$$ LANGUAGE SQL;

DROP TABLE IF EXISTS users_baskets_preferences;
CREATE TABLE users_baskets_preferences
(
  id serial NOT NULL,
  user_serial_id integer NOT NULL,
  group_serial_id integer NOT NULL,
  basket_id character varying(32) NOT NULL,
  display boolean NOT NULL,
  color character varying(16),
  CONSTRAINT users_baskets_preferences_pkey PRIMARY KEY (id),
  CONSTRAINT users_baskets_preferences_key UNIQUE (user_serial_id, group_serial_id, basket_id)
)
WITH (OIDS=FALSE);


-- convert working table
DROP TABLE IF EXISTS convert_stack;
CREATE TABLE convert_stack
(
  coll_id character varying(32) NOT NULL,
  res_id bigint NOT NULL,
  convert_format character varying(32) NOT NULL DEFAULT 'pdf'::character varying,
  cnt_retry integer,
  status character(1) NOT NULL,
  work_batch bigint,
  regex character varying(32),
  CONSTRAINT convert_stack_pkey PRIMARY KEY (coll_id, res_id, convert_format)
)
WITH (OIDS=FALSE);

CREATE TABLE password_rules
(
  id serial,
  label character varying(64) NOT NULL,
  "value" integer NOT NULL,
  enabled boolean DEFAULT FALSE NOT NULL,
  CONSTRAINT password_rules_pkey PRIMARY KEY (id),
  CONSTRAINT password_rules_label_key UNIQUE (label)
)
WITH (OIDS=FALSE);

CREATE TABLE password_history
(
  id serial,
  user_serial_id INTEGER NOT NULL,
  password character varying(255) NOT NULL,
  CONSTRAINT password_history_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE contacts_filling
(
  id serial NOT NULL,
  enable boolean NOT NULL,
  rating_columns text NOT NULL,
  first_threshold int NOT NULL,
  second_threshold int NOT NULL,
  CONSTRAINT contacts_filling_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
