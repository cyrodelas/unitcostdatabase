-- Phase 24: additive ML dataset, label, feedback, job, and registry foundation.
-- No model execution, inference, activation, or authoritative business mutation is included.

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`ml_dataset` (
  `dataset_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dataset_code` VARCHAR(50) NOT NULL,
  `dataset_name` VARCHAR(200) NOT NULL,
  `capability` VARCHAR(30) NOT NULL,
  `description` VARCHAR(1000) NULL,
  `dataset_status` VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dataset_id`),
  UNIQUE KEY `uq_ml_dataset_code` (`dataset_code`),
  KEY `idx_ml_dataset_capability_status` (`capability`,`dataset_status`),
  KEY `fk_ml_dataset_creator` (`created_by`),
  CONSTRAINT `fk_ml_dataset_creator` FOREIGN KEY (`created_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_ml_dataset_capability` CHECK (`capability` IN ('EXTRACTION','MAPPING','RESOURCE_TEMPLATE')),
  CONSTRAINT `chk_ml_dataset_status` CHECK (`dataset_status` IN ('ACTIVE','INACTIVE'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`ml_dataset_version` (
  `dataset_version_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dataset_id` BIGINT UNSIGNED NOT NULL,
  `version_no` INT UNSIGNED NOT NULL,
  `version_status` VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
  `source_cutoff_at` DATETIME NOT NULL,
  `record_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approved_label_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `manifest_sha256` CHAR(64) NULL,
  `artifact_file_name` VARCHAR(255) NULL,
  `artifact_sha256` CHAR(64) NULL,
  `notes` VARCHAR(1000) NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `frozen_by` BIGINT UNSIGNED NULL,
  `frozen_at` DATETIME NULL,
  `approved_by` BIGINT UNSIGNED NULL,
  `approved_at` DATETIME NULL,
  PRIMARY KEY (`dataset_version_id`),
  UNIQUE KEY `uq_ml_dataset_version` (`dataset_id`,`version_no`),
  KEY `idx_ml_dataset_version_status` (`version_status`,`created_at`),
  KEY `fk_ml_dataset_version_creator` (`created_by`),
  KEY `fk_ml_dataset_version_frozen_by` (`frozen_by`),
  KEY `fk_ml_dataset_version_approved_by` (`approved_by`),
  CONSTRAINT `fk_ml_dataset_version_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `ml_dataset` (`dataset_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_dataset_version_creator` FOREIGN KEY (`created_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_dataset_version_frozen_by` FOREIGN KEY (`frozen_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_dataset_version_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_ml_dataset_version_status` CHECK (`version_status` IN ('DRAFT','FROZEN','APPROVED','RETIRED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`ml_dataset_record` (
  `dataset_record_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dataset_version_id` BIGINT UNSIGNED NOT NULL,
  `record_type` VARCHAR(30) NOT NULL,
  `source_identity` VARCHAR(100) NOT NULL,
  `boq_import_batch_id` BIGINT UNSIGNED NULL,
  `boq_import_staging_id` BIGINT UNSIGNED NULL,
  `boq_item_mapping_id` BIGINT UNSIGNED NULL,
  `cost_item_revision_id` BIGINT UNSIGNED NULL,
  `split_group_key` VARCHAR(150) NOT NULL,
  `payload_json` LONGTEXT NOT NULL,
  `label_json` LONGTEXT NOT NULL,
  `label_status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  `record_sha256` CHAR(64) NOT NULL,
  `review_notes` VARCHAR(1000) NULL,
  `reviewed_by` BIGINT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dataset_record_id`),
  UNIQUE KEY `uq_ml_dataset_record_source` (`dataset_version_id`,`source_identity`),
  KEY `idx_ml_dataset_record_review` (`dataset_version_id`,`label_status`),
  KEY `idx_ml_dataset_record_batch` (`boq_import_batch_id`),
  KEY `idx_ml_dataset_record_mapping` (`boq_item_mapping_id`),
  KEY `idx_ml_dataset_record_revision` (`cost_item_revision_id`),
  KEY `fk_ml_dataset_record_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_ml_dataset_record_version` FOREIGN KEY (`dataset_version_id`) REFERENCES `ml_dataset_version` (`dataset_version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_dataset_record_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_ml_dataset_record_type` CHECK (`record_type` IN ('EXTRACTION_ROW','MAPPING_LABEL','RESOURCE_TEMPLATE')),
  CONSTRAINT `chk_ml_dataset_record_label` CHECK (`label_status` IN ('PENDING','APPROVED','REJECTED')),
  CONSTRAINT `chk_ml_dataset_record_payload_json` CHECK (JSON_VALID(`payload_json`)),
  CONSTRAINT `chk_ml_dataset_record_label_json` CHECK (JSON_VALID(`label_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`ml_feedback` (
  `feedback_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dataset_record_id` BIGINT UNSIGNED NOT NULL,
  `feedback_event` VARCHAR(30) NOT NULL,
  `previous_label_status` VARCHAR(20) NOT NULL,
  `new_label_status` VARCHAR(20) NOT NULL,
  `comments` VARCHAR(1000) NULL,
  `feedback_by` BIGINT UNSIGNED NULL,
  `feedback_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  KEY `idx_ml_feedback_record_date` (`dataset_record_id`,`feedback_at`),
  KEY `fk_ml_feedback_actor` (`feedback_by`),
  CONSTRAINT `fk_ml_feedback_record` FOREIGN KEY (`dataset_record_id`) REFERENCES `ml_dataset_record` (`dataset_record_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_feedback_actor` FOREIGN KEY (`feedback_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_ml_feedback_event` CHECK (`feedback_event` IN ('LABEL_APPROVED','LABEL_REJECTED','LABEL_REOPENED')),
  CONSTRAINT `chk_ml_feedback_previous` CHECK (`previous_label_status` IN ('PENDING','APPROVED','REJECTED')),
  CONSTRAINT `chk_ml_feedback_new` CHECK (`new_label_status` IN ('PENDING','APPROVED','REJECTED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`ml_model_version` (
  `model_version_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_code` VARCHAR(80) NOT NULL,
  `version_tag` VARCHAR(50) NOT NULL,
  `capability` VARCHAR(30) NOT NULL,
  `environment_name` VARCHAR(30) NOT NULL DEFAULT 'OFFLINE',
  `model_status` VARCHAR(20) NOT NULL DEFAULT 'REGISTERED',
  `trained_dataset_version_id` BIGINT UNSIGNED NULL,
  `artifact_file_name` VARCHAR(255) NULL,
  `artifact_sha256` CHAR(64) NULL,
  `feature_schema_json` LONGTEXT NULL,
  `metrics_json` LONGTEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` BIGINT UNSIGNED NULL,
  `approved_at` DATETIME NULL,
  PRIMARY KEY (`model_version_id`),
  UNIQUE KEY `uq_ml_model_version` (`model_code`,`version_tag`,`environment_name`),
  KEY `idx_ml_model_capability_status` (`capability`,`environment_name`,`model_status`),
  KEY `fk_ml_model_dataset_version` (`trained_dataset_version_id`),
  KEY `fk_ml_model_creator` (`created_by`),
  KEY `fk_ml_model_approver` (`approved_by`),
  CONSTRAINT `fk_ml_model_dataset_version` FOREIGN KEY (`trained_dataset_version_id`) REFERENCES `ml_dataset_version` (`dataset_version_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_model_creator` FOREIGN KEY (`created_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_model_approver` FOREIGN KEY (`approved_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_ml_model_capability` CHECK (`capability` IN ('EXTRACTION','MAPPING','RESOURCE_TEMPLATE')),
  CONSTRAINT `chk_ml_model_status` CHECK (`model_status` IN ('REGISTERED','EVALUATED','APPROVED','ACTIVE','RETIRED','REJECTED')),
  CONSTRAINT `chk_ml_model_feature_json` CHECK (`feature_schema_json` IS NULL OR JSON_VALID(`feature_schema_json`)),
  CONSTRAINT `chk_ml_model_metrics_json` CHECK (`metrics_json` IS NULL OR JSON_VALID(`metrics_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`ml_job` (
  `ml_job_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_type` VARCHAR(30) NOT NULL,
  `capability` VARCHAR(30) NOT NULL,
  `dataset_version_id` BIGINT UNSIGNED NULL,
  `model_version_id` BIGINT UNSIGNED NULL,
  `job_status` VARCHAR(20) NOT NULL DEFAULT 'QUEUED',
  `idempotency_key` VARCHAR(100) NOT NULL,
  `payload_json` LONGTEXT NOT NULL,
  `attempt_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `requested_by` BIGINT UNSIGNED NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `worker_reference` VARCHAR(100) NULL,
  `result_json` LONGTEXT NULL,
  `error_message` VARCHAR(2000) NULL,
  PRIMARY KEY (`ml_job_id`),
  UNIQUE KEY `uq_ml_job_idempotency` (`idempotency_key`),
  KEY `idx_ml_job_queue` (`job_status`,`requested_at`),
  KEY `fk_ml_job_dataset_version` (`dataset_version_id`),
  KEY `fk_ml_job_model_version` (`model_version_id`),
  KEY `fk_ml_job_requester` (`requested_by`),
  CONSTRAINT `fk_ml_job_dataset_version` FOREIGN KEY (`dataset_version_id`) REFERENCES `ml_dataset_version` (`dataset_version_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_job_model_version` FOREIGN KEY (`model_version_id`) REFERENCES `ml_model_version` (`model_version_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ml_job_requester` FOREIGN KEY (`requested_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_ml_job_type` CHECK (`job_type` IN ('DATASET_EXPORT','TRAINING','EVALUATION')),
  CONSTRAINT `chk_ml_job_capability` CHECK (`capability` IN ('EXTRACTION','MAPPING','RESOURCE_TEMPLATE')),
  CONSTRAINT `chk_ml_job_status` CHECK (`job_status` IN ('QUEUED','RUNNING','SUCCEEDED','FAILED','CANCELLED')),
  CONSTRAINT `chk_ml_job_payload_json` CHECK (JSON_VALID(`payload_json`)),
  CONSTRAINT `chk_ml_job_result_json` CHECK (`result_json` IS NULL OR JSON_VALID(`result_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nexus_ucd`.`app_permission` (`permission_code`,`permission_name`,`module_name`,`description`,`is_active`) VALUES
('ml.view','View ML Governance','ML Governance','View dataset versions, label state, export jobs, and model registry metadata.',1),
('ml.review','Review ML Labels','ML Governance','Approve, reject, or reopen immutable dataset labels.',1),
('ml.train','Prepare ML Datasets','ML Governance','Create datasets and immutable versions and queue reproducible exports.',1),
('ml.deploy','Manage ML Deployment','ML Governance','Reserved for governed model approval, activation, and rollback in Phase 27.',1)
ON DUPLICATE KEY UPDATE `permission_name`=VALUES(`permission_name`),`module_name`=VALUES(`module_name`),`description`=VALUES(`description`),`is_active`=1;

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`,`permission_id`)
SELECT r.role_id,p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE (p.permission_code='ml.view' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN','COST_ENGINEER','REVIEWER','APPROVER','DATA_ANALYST'))
   OR (p.permission_code='ml.review' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN','COST_ENGINEER'))
   OR (p.permission_code='ml.train' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN','DATA_ANALYST'))
   OR (p.permission_code='ml.deploy' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN'));
