USE notary_db;
ALTER TABLE archives ADD COLUMN moj_document_file VARCHAR(255) NULL AFTER id_card_file;
ALTER TABLE archives ADD COLUMN moj_document_number VARCHAR(120) NULL AFTER moj_document_file;
ALTER TABLE archives ADD COLUMN updated_by INT UNSIGNED NULL AFTER created_by;
ALTER TABLE archives ADD CONSTRAINT fk_archives_updated_by FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL;
CREATE TABLE IF NOT EXISTS activity_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, user_name_snapshot VARCHAR(150) NOT NULL, action VARCHAR(60) NOT NULL, entity_type VARCHAR(60) NULL, entity_id BIGINT UNSIGNED NULL, entity_ref VARCHAR(150) NULL, details TEXT NULL, ip_address VARCHAR(45) NULL, user_agent VARCHAR(500) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL, INDEX(user_id), INDEX(action), INDEX(created_at), INDEX(entity_type,entity_id)) ENGINE=InnoDB;
-- Existing archives must be reviewed and assigned a Ministry of Justice document before making moj_document_file NOT NULL.
