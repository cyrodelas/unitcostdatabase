-- Standard Cost Item revision-level Project Type / Market Segment classification.
-- Existing rows are classified using governed stable codes, not environment-specific IDs.

ALTER TABLE standard_cost_item_revision
  ADD COLUMN IF NOT EXISTS project_type_id BIGINT UNSIGNED NULL AFTER enterprise_cost_code,
  ADD COLUMN IF NOT EXISTS market_segment_id BIGINT UNSIGNED NULL AFTER project_type_id;

SET @project_type_id := (
  SELECT project_type_id FROM ref_project_type
  WHERE project_type_code = 'RES-SUB' AND is_active = 1 LIMIT 1
);
SET @market_segment_id := (
  SELECT market_segment_id FROM ref_market_segment
  WHERE market_segment_code = 'MKT-004' AND is_active = 1 LIMIT 1
);
SET @pair_is_valid := (
  SELECT COUNT(*) FROM ref_project_type_market_segment
  WHERE project_type_id = @project_type_id
    AND market_segment_id = @market_segment_id
    AND is_active = 1
);
SET @assert_sql := IF(
  @project_type_id IS NOT NULL AND @market_segment_id IS NOT NULL AND @pair_is_valid = 1,
  'SELECT 1',
  "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Required active RES-SUB / MKT-004 hierarchy pair was not found'"
);
PREPARE assert_statement FROM @assert_sql;
EXECUTE assert_statement;
DEALLOCATE PREPARE assert_statement;

UPDATE standard_cost_item_revision
SET project_type_id = @project_type_id,
    market_segment_id = @market_segment_id
WHERE project_type_id IS NULL
  AND market_segment_id IS NULL;

ALTER TABLE standard_cost_item_revision
  MODIFY project_type_id BIGINT UNSIGNED NOT NULL,
  MODIFY market_segment_id BIGINT UNSIGNED NOT NULL;

SET @index_exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'standard_cost_item_revision'
    AND index_name = 'idx_cost_item_revision_project_market'
);
SET @index_sql := IF(
  @index_exists = 0,
  'ALTER TABLE standard_cost_item_revision ADD INDEX idx_cost_item_revision_project_market (project_type_id, market_segment_id)',
  'SELECT 1'
);
PREPARE index_statement FROM @index_sql;
EXECUTE index_statement;
DEALLOCATE PREPARE index_statement;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.table_constraints
  WHERE constraint_schema = DATABASE()
    AND table_name = 'standard_cost_item_revision'
    AND constraint_name = 'fk_cost_item_revision_project_market'
    AND constraint_type = 'FOREIGN KEY'
);
SET @fk_sql := IF(
  @fk_exists = 0,
  'ALTER TABLE standard_cost_item_revision ADD CONSTRAINT fk_cost_item_revision_project_market FOREIGN KEY (project_type_id, market_segment_id) REFERENCES ref_project_type_market_segment (project_type_id, market_segment_id) ON UPDATE CASCADE ON DELETE RESTRICT',
  'SELECT 1'
);
PREPARE fk_statement FROM @fk_sql;
EXECUTE fk_statement;
DEALLOCATE PREPARE fk_statement;

