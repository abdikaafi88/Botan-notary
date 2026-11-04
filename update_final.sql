-- Final upgrade for an existing installation.
-- Back up in phpMyAdmin first. Before adding the unique payment constraint, find archives without a payment:
-- SELECT a.id,a.ref_number FROM archives a LEFT JOIN payments p ON p.archive_id=a.id WHERE p.id IS NULL;
USE notary_db;
ALTER TABLE archives MODIFY updated_by INT UNSIGNED NULL;
ALTER TABLE payments ADD UNIQUE KEY uq_payments_archive (archive_id);
CREATE TABLE IF NOT EXISTS activity_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL,
 user_name_snapshot VARCHAR(150) NOT NULL, action VARCHAR(60) NOT NULL,
 entity_type VARCHAR(60) NULL, entity_id BIGINT UNSIGNED NULL, entity_ref VARCHAR(150) NULL,
 details TEXT NULL, ip_address VARCHAR(45) NULL, user_agent VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX(user_id), INDEX(action), INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- If your legacy schema still contains removed archive columns, make them nullable before using the final application:
-- ALTER TABLE archives MODIFY seller_name VARCHAR(150) NULL, MODIFY seller_phone VARCHAR(50) NULL,
-- MODIFY buyer_name VARCHAR(150) NULL, MODIFY buyer_phone VARCHAR(50) NULL,
-- MODIFY id_card_file VARCHAR(255) NULL, MODIFY moj_document_file VARCHAR(255) NULL,
-- MODIFY moj_document_number VARCHAR(120) NULL;
