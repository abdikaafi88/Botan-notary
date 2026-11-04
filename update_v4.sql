USE notary_db;

-- Safe migration: preserve legacy archive data but stop requiring it.
ALTER TABLE archives
  MODIFY seller_name VARCHAR(150) NULL,
  MODIFY seller_phone VARCHAR(50) NULL,
  MODIFY seller_image VARCHAR(255) NULL,
  MODIFY buyer_name VARCHAR(150) NULL,
  MODIFY buyer_phone VARCHAR(50) NULL,
  MODIFY buyer_image VARCHAR(255) NULL,
  MODIFY id_card_file VARCHAR(255) NULL,
  MODIFY moj_document_file VARCHAR(255) NULL,
  MODIFY moj_document_number VARCHAR(120) NULL;

-- Existing installations may already have these indexes; IF NOT EXISTS keeps this rerunnable.
ALTER TABLE payments
  ADD INDEX IF NOT EXISTS idx_payments_sender_name (sender_name),
  ADD INDEX IF NOT EXISTS idx_payments_sender_phone (sender_phone);
