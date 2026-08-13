ALTER TABLE `material_master`
  ADD COLUMN `attribute_subject_class_id` bigint(20) unsigned DEFAULT NULL AFTER `material_category_id`,
  ADD KEY `idx_material_master_attribute_class` (`attribute_subject_class_id`),
  ADD CONSTRAINT `fk_material_master_attribute_class` FOREIGN KEY (`attribute_subject_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `material_variant`
  ADD COLUMN `attribute_subject_class_id` bigint(20) unsigned DEFAULT NULL AFTER `material_id`,
  ADD KEY `idx_material_variant_attribute_class` (`attribute_subject_class_id`),
  ADD CONSTRAINT `fk_material_variant_attribute_class` FOREIGN KEY (`attribute_subject_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `standard_cost_item_revision`
  ADD COLUMN `attribute_subject_class_id` bigint(20) unsigned DEFAULT NULL AFTER `material_group_id`,
  ADD COLUMN `standard_item_name_id` bigint(20) unsigned DEFAULT NULL AFTER `uom_id`,
  ADD KEY `idx_cost_item_revision_attribute_class` (`attribute_subject_class_id`),
  ADD KEY `idx_standard_cost_item_name_id` (`standard_item_name_id`),
  ADD CONSTRAINT `fk_cost_item_revision_attribute_class` FOREIGN KEY (`attribute_subject_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `ref_trade`
  ADD COLUMN `division_id` bigint(20) unsigned DEFAULT NULL AFTER `trade_id`,
  ADD KEY `idx_ref_trade_division` (`division_id`),
  ADD CONSTRAINT `fk_ref_trade_division` FOREIGN KEY (`division_id`) REFERENCES `ref_division` (`division_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `project_master`
  ADD COLUMN `project_type_id` bigint(20) unsigned DEFAULT NULL AFTER `project_name`,
  ADD COLUMN `location_id` bigint(20) unsigned DEFAULT NULL AFTER `country`,
  ADD KEY `idx_project_master_project_type` (`project_type_id`),
  ADD KEY `idx_project_master_location_id` (`location_id`),
  ADD CONSTRAINT `fk_project_master_project_type` FOREIGN KEY (`project_type_id`) REFERENCES `ref_project_type` (`project_type_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_project_master_location` FOREIGN KEY (`location_id`) REFERENCES `ref_location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `material_rate_schedule`
  ADD COLUMN `location_id` bigint(20) unsigned DEFAULT NULL AFTER `currency_code`,
  ADD KEY `idx_material_rate_schedule_location` (`location_id`),
  ADD CONSTRAINT `fk_material_rate_schedule_location` FOREIGN KEY (`location_id`) REFERENCES `ref_location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `labor_rate_schedule`
  ADD COLUMN `location_id` bigint(20) unsigned DEFAULT NULL AFTER `currency_code`,
  ADD KEY `idx_labor_rate_schedule_location` (`location_id`),
  ADD CONSTRAINT `fk_labor_rate_schedule_location` FOREIGN KEY (`location_id`) REFERENCES `ref_location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `cost_item_rate_history`
  ADD COLUMN `rate_source_type_id` bigint(20) unsigned DEFAULT NULL AFTER `rate_type`,
  ADD COLUMN `rate_basis_id` bigint(20) unsigned DEFAULT NULL AFTER `rate_source_type_id`,
  ADD COLUMN `price_period_id` bigint(20) unsigned DEFAULT NULL AFTER `rate_date`,
  ADD COLUMN `location_id` bigint(20) unsigned DEFAULT NULL AFTER `price_period_id`,
  ADD COLUMN `validation_status_id` bigint(20) unsigned DEFAULT NULL AFTER `validation_status`,
  ADD KEY `idx_rate_history_source_type` (`rate_source_type_id`),
  ADD KEY `idx_rate_history_basis` (`rate_basis_id`),
  ADD KEY `idx_rate_history_price_period` (`price_period_id`),
  ADD KEY `idx_rate_history_location` (`location_id`),
  ADD KEY `idx_rate_history_validation_status_id` (`validation_status_id`),
  ADD CONSTRAINT `fk_rate_history_source_type` FOREIGN KEY (`rate_source_type_id`) REFERENCES `ref_rate_source_type` (`rate_source_type_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rate_history_basis` FOREIGN KEY (`rate_basis_id`) REFERENCES `ref_rate_basis` (`rate_basis_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rate_history_price_period` FOREIGN KEY (`price_period_id`) REFERENCES `ref_price_period` (`price_period_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cost_item_rate_history_location` FOREIGN KEY (`location_id`) REFERENCES `ref_location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rate_history_validation_status_id` FOREIGN KEY (`validation_status_id`) REFERENCES `ref_validation_status` (`validation_status_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- The updated dump references ref_standard_item_name and ref_uniformat_assembly,
-- but defines neither table. Their nullable ID columns are retained without adding
-- new invalid foreign keys or inventing missing reference structures.
