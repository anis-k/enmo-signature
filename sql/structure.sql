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

CREATE TABLE docservers
(
  id serial NOT NULL,
  type character varying(32) NOT NULL,
  label character varying(255),
  is_readonly character(1) NOT NULL DEFAULT 'N'::bpchar,
  size_limit_number bigint NOT NULL DEFAULT (0)::bigint,
  actual_size_number bigint NOT NULL DEFAULT (0)::bigint,
  path character varying(255) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  CONSTRAINT docservers_pkey PRIMARY KEY (id),
  CONSTRAINT docservers_type_key UNIQUE (type)
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
  id serial,
  status_id character varying(10) NOT NULL,
  label character varying(50) NOT NULL,
  CONSTRAINT status_pkey PRIMARY KEY (id),
  CONSTRAINT status_status_id_key UNIQUE (status_id)
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

CREATE TABLE main_documents
(
  id serial NOT NULL,
  external_id character varying(255),
  reference character varying(255),
  subject text,
  doc_date timestamp without time zone,
  status integer NOT NULL,
  priority character varying(255),
  sender text NOT NULL,
  sender_entity text,
  processing_user integer NOT NULL,
  recipient text,
  creation_date timestamp without time zone NOT NULL DEFAULT NOW(),
  modification_date timestamp without time zone DEFAULT NOW(),
  CONSTRAINT main_documents_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE attachments
(
  id serial NOT NULL,
  subject text,
  format character varying(50) NOT NULL,
  typist character varying(128) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  identifier character varying(255) DEFAULT NULL::character varying,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  main_document_id bigint,
  CONSTRAINT attachments_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE baskets
(
  id serial NOT NULL,
  name character varying(255) NOT NULL,
  description character varying(255) NOT NULL,
  clause text NOT NULL,
  documents_order character varying(255) NOT NULL DEFAULT 'creation_date DESC',
  CONSTRAINT baskets_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE adr_main_documents
(
  id serial NOT NULL,
  main_document_id bigint NOT NULL,
  type character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT adr_letterbox_pkey PRIMARY KEY (id),
  CONSTRAINT adr_letterbox_unique_key UNIQUE (main_document_id, type)
)
WITH (OIDS=FALSE);

CREATE TABLE adr_attachments
(
  id serial NOT NULL,
  attachment_id bigint NOT NULL,
  type character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT adr_attachments_pkey PRIMARY KEY (id),
  CONSTRAINT adr_attachments_unique_key UNIQUE (attachment_id, type)
)
WITH (OIDS=FALSE);

CREATE TABLE user_signatures
(
  id serial NOT NULL,
  user_serial_id integer NOT NULL,
  label character varying(255) DEFAULT NULL::character varying,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT user_signatures_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
