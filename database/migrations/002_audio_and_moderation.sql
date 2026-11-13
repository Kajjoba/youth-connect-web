-- Run once on a database created before audio lessons and discussion moderation existed.
-- phpMyAdmin: select the database, open the SQL tab, paste this, press Go.
ALTER TABLE lessons MODIFY type ENUM('reading','video','audio','discussion') NOT NULL DEFAULT 'reading',
  ADD COLUMN audio_path VARCHAR(255) NULL AFTER video_url,
  ADD COLUMN audio_name VARCHAR(190) NULL AFTER audio_path;
ALTER TABLE discussion_posts
  ADD COLUMN pinned TINYINT(1) NOT NULL DEFAULT 0 AFTER body,
  ADD COLUMN reported TINYINT(1) NOT NULL DEFAULT 0 AFTER pinned;
