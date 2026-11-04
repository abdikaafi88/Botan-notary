-- OPTIONAL DESTRUCTIVE CLEANUP
-- Take a backup (phpMyAdmin export) before running cleanup.
-- Run only after the client confirms that legacy archive fields/files are no longer needed.
USE notary_db;
ALTER TABLE archives
  DROP COLUMN seller_name,
  DROP COLUMN seller_phone,
  DROP COLUMN seller_image,
  DROP COLUMN buyer_name,
  DROP COLUMN buyer_phone,
  DROP COLUMN buyer_image,
  DROP COLUMN id_card_file,
  DROP COLUMN moj_document_file,
  DROP COLUMN moj_document_number;
DROP TABLE IF EXISTS witnesses;