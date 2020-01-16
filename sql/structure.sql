-- core/sql/structure/core.postgresql.sql

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;
SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;


DROP TABLE IF EXISTS adr_attachments;
CREATE TABLE adr_attachments
(
  id serial NOT NULL,
  attachment_id INTEGER NOT NULL,
  type character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) NOT NULL,
  CONSTRAINT adr_attachments_pkey PRIMARY KEY (id),
  CONSTRAINT adr_attachments_unique_key UNIQUE (attachment_id, type)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS adr_main_documents;
CREATE TABLE adr_main_documents
(
  id serial NOT NULL,
  main_document_id INTEGER NOT NULL,
  type character varying(32) NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) NOT NULL,
  CONSTRAINT adr_main_documents_pkey PRIMARY KEY (id),
  CONSTRAINT adr_main_documents_unique_key UNIQUE (main_document_id, type)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS attachments;
CREATE TABLE attachments
(
  id serial NOT NULL,
  main_document_id INTEGER NOT NULL,
  title text NOT NULL,
  reference CHARACTER VARYING(64),
  creation_date timestamp without time zone NOT NULL DEFAULT NOW(),
  modification_date timestamp without time zone DEFAULT NOW(),
  CONSTRAINT attachments_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS configurations;
CREATE TABLE configurations
(
id serial NOT NULL,
identifier CHARACTER VARYING (64) NOT NULL,
label text NOT NULL,
value jsonb DEFAULT '{}' NOT NULL,
CONSTRAINT configuration_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS docservers;
CREATE TABLE docservers
(
  id serial NOT NULL,
  type character varying(32) NOT NULL,
  label character varying(255),
  is_readonly character(1) NOT NULL DEFAULT 'N'::bpchar,
  size_limit_number bigint NOT NULL DEFAULT (0)::bigint,
  actual_size_number bigint NOT NULL DEFAULT (0)::bigint,
  path character varying(255) NOT NULL,
  CONSTRAINT docservers_pkey PRIMARY KEY (id),
  CONSTRAINT docservers_type_key UNIQUE (type)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS emails;
CREATE TABLE emails
(
  id serial NOT NULL,
  user_id INTEGER NOT NULL,
  sender CHARACTER VARYING(128) NOT NULL,
  recipients json DEFAULT '[]' NOT NULL,
  cc json DEFAULT '[]' NOT NULL,
  cci json DEFAULT '[]' NOT NULL,
  subject CHARACTER VARYING(256),
  body text,
  document json,
  is_html boolean NOT NULL DEFAULT TRUE,
  status CHARACTER VARYING(16) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  send_date timestamp without time zone,
CONSTRAINT emails_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS groups;
CREATE TABLE groups
(
  id serial NOT NULL,
  label character varying(128) NOT NULL,
  CONSTRAINT groups_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS groups_privileges;
CREATE TABLE groups_privileges
(
  id serial NOT NULL,
  group_id INTEGER NOT NULL,
  privilege character varying(128) NOT NULL,
  CONSTRAINT groups_privileges_pkey PRIMARY KEY (id),
  CONSTRAINT groups_privileges_unique_key UNIQUE (group_id, privilege)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS history;
CREATE TABLE history
(
  id serial NOT NULL,
  code CHARACTER VARYING(2) NOT NULL,
  object_type CHARACTER VARYING(128) NOT NULL,
  object_id  CHARACTER VARYING(32) NOT NULL,
  type CHARACTER VARYING(64) NOT NULL,
  user_id INTEGER NOT NULL,
  "user" text NOT NULL,
  date TIMESTAMP without TIME ZONE NOT NULL,
  message text NOT NULL,
  data jsonb NOT NULL DEFAULT '{}',
  ip CHARACTER VARYING(64) NOT NULL,
  CONSTRAINT history_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS main_documents;
CREATE TABLE main_documents
(
  id serial NOT NULL,
  title text NOT NULL,
  reference CHARACTER VARYING(64),
  description text,
  sender text NOT NULL,
  deadline timestamp without time zone,
  notes jsonb,
  link_id text,
  metadata jsonb NOT NULL DEFAULT '{}',
  creation_date timestamp without time zone NOT NULL DEFAULT NOW(),
  modification_date timestamp without time zone DEFAULT NOW(),
  CONSTRAINT main_documents_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS password_history;
CREATE TABLE password_history
(
  id serial,
  user_id INTEGER NOT NULL,
  password character varying(255) NOT NULL,
  CONSTRAINT password_history_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS password_rules;
CREATE TABLE password_rules
(
  id serial,
  label character varying(64) NOT NULL,
  "value" INTEGER NOT NULL,
  enabled boolean DEFAULT FALSE NOT NULL,
  CONSTRAINT password_rules_pkey PRIMARY KEY (id),
  CONSTRAINT password_rules_label_key UNIQUE (label)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS signatures;
CREATE TABLE signatures
(
  id serial NOT NULL,
  user_id INTEGER NOT NULL,
  path character varying(255) NOT NULL,
  filename character varying(255) NOT NULL,
  fingerprint character varying(255) NOT NULL,
  substituted boolean DEFAULT FALSE NOT NULL,
  external_application CHARACTER VARYING(255),
  CONSTRAINT signatures_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS users;
CREATE TABLE users
(
  id serial NOT NULL,
  login character varying(128) NOT NULL,
  email character varying(128) NOT NULL,
  "password" character varying(255) NOT NULL,
  firstname character varying(128) NOT NULL,
  lastname character varying(128) NOT NULL,
  picture text,
  "isRest" boolean DEFAULT FALSE NOT NULL,
  preferences jsonb NOT NULL DEFAULT '{"lang" : "fr", "writingMode" : "direct", "writingSize" : 1, "writingColor" : "#000000", "notifications" : true}',
  substitute INTEGER DEFAULT NULL,
  refresh_token jsonb NOT NULL DEFAULT '[]',
  reset_token text DEFAULT NULL,
  password_modification_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
  failed_authentication INTEGER DEFAULT 0,
  locked_until TIMESTAMP without time zone,
  CONSTRAINT users_pkey PRIMARY KEY (id),
  CONSTRAINT users_login_key UNIQUE (login)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS users_groups;
CREATE TABLE users_groups
(
    id serial NOT NULL,
    group_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    CONSTRAINT users_groups_pkey PRIMARY KEY (id),
    CONSTRAINT users_groups_unique_key UNIQUE (group_id, user_id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS workflows;
CREATE TABLE workflows
(
    id serial NOT NULL,
    user_id INTEGER NOT NULL,
    main_document_id INTEGER NOT NULL,
    mode CHARACTER VARYING(16) NOT NULL,
    "order" INTEGER NOT NULL,
    status CHARACTER VARYING(16) DEFAULT NULL,
    note text DEFAULT NULL,
    process_date timestamp without time zone DEFAULT NULL,
    CONSTRAINT workflow_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
