-- Run once on a database created before course cover images existed.
-- phpMyAdmin: select the database, open the SQL tab, paste this, press Go.
ALTER TABLE courses ADD COLUMN cover_path VARCHAR(255) NULL AFTER color;
