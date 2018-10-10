
Select setval('res_id_mlb_seq', (select max(res_id)+1 from res_letterbox), false);
select setval('res_attachment_res_id_seq', (select max(res_id)+1 from res_attachments), false);
