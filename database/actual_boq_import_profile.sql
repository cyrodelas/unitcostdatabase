ALTER TABLE `boq_import_batch`
  ADD COLUMN `source_sheet` varchar(100) DEFAULT NULL AFTER `file_sha256`,
  ADD COLUMN `source_declared_total` decimal(18,2) DEFAULT NULL AFTER `invalid_rows`,
  ADD COLUMN `parsed_line_total` decimal(18,2) DEFAULT NULL AFTER `source_declared_total`,
  ADD COLUMN `total_variance` decimal(18,2) DEFAULT NULL AFTER `parsed_line_total`,
  ADD COLUMN `unpriced_rows` int(10) unsigned NOT NULL DEFAULT 0 AFTER `total_variance`,
  ADD COLUMN `normalization_notes` text DEFAULT NULL AFTER `unpriced_rows`;
