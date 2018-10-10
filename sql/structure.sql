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

CREATE TABLE history
(
  id serial NOT NULL,
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

CREATE TABLE status
(
  identifier serial,
  id character varying(10) NOT NULL,
  label_status character varying(50) NOT NULL,
  CONSTRAINT status_pkey PRIMARY KEY (id)
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
  status character varying(10) NOT NULL DEFAULT 'OK'::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  password_modification_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
  loginmode character varying(50) DEFAULT NULL::character varying,
  cookie_key character varying(255) DEFAULT NULL::character varying,
  cookie_date timestamp without time zone,
  CONSTRAINT users_pkey PRIMARY KEY (user_id),
  CONSTRAINT users_id_key UNIQUE (id)
)
WITH (OIDS=FALSE);


CREATE TABLE res_attachments
(
  res_id serial NOT NULL,
  subject text,
  format character varying(50) NOT NULL,
  typist character varying(128) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  identifier character varying(255) DEFAULT NULL::character varying,
  doc_date timestamp without time zone,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  res_id_master bigint,
  CONSTRAINT res_attachments_pkey PRIMARY KEY (res_id)
)
WITH (OIDS=FALSE);

CREATE TABLE baskets
(
  id serial NOT NULL,
  basket_id character varying(32) NOT NULL,
  basket_name character varying(255) NOT NULL,
  basket_desc character varying(255) NOT NULL,
  basket_clause text NOT NULL,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  basket_res_order character varying(255) NOT NULL DEFAULT 'res_id',
  CONSTRAINT baskets_pkey PRIMARY KEY (basket_id)
)
WITH (OIDS=FALSE);

CREATE TABLE res_letterbox
(
  res_id serial NOT NULL,
  external_id bigint NOT NULL,
  subject text,
  type_label character varying(255) NOT NULL,
  format character varying(50) NOT NULL,
  typist text NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  modification_date timestamp without time zone DEFAULT NOW(),
  identifier character varying(255) DEFAULT NULL::character varying,
  doc_date timestamp without time zone,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  status character varying(10) NOT NULL,
  priority character varying(255),
  initiator text,
  dest_user character varying(128) DEFAULT NULL::character varying,
  dest_contacts text,
  confidentiality character(1),
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

CREATE TABLE user_signatures
(
  id serial NOT NULL,
  user_serial_id integer NOT NULL,
  signature_label character varying(255) DEFAULT NULL::character varying,
  signature_path character varying(255) DEFAULT NULL::character varying,
  signature_file_name character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT user_signatures_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
