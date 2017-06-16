ALTER TABLE domain ADD COLUMN purge_trash integer NOT NULL DEFAULT 0;
ALTER TABLE domain ADD COLUMN purge_junk integer NOT NULL DEFAULT 0;
ALTER TABLE mailbox ADD COLUMN purge_trash integer NULL DEFAULT NULL;
ALTER TABLE mailbox ADD COLUMN purge_junk integer NULL DEFAULT NULL;
