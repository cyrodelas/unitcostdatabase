-- Phase 4: application role-based access control.

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`app_role` (
  `role_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_code` VARCHAR(50) NOT NULL,
  `role_name` VARCHAR(150) NOT NULL,
  `description` VARCHAR(500) NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uq_app_role_code` (`role_code`),
  KEY `idx_app_role_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`app_permission` (
  `permission_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_code` VARCHAR(100) NOT NULL,
  `permission_name` VARCHAR(150) NOT NULL,
  `module_name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(500) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `uq_app_permission_code` (`permission_code`),
  KEY `idx_app_permission_module` (`module_name`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`app_role_permission` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `granted_by` BIGINT UNSIGNED NULL,
  `granted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `fk_app_role_permission_permission` (`permission_id`),
  KEY `fk_app_role_permission_granted_by` (`granted_by`),
  CONSTRAINT `fk_app_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `app_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_app_role_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `app_permission` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_app_role_permission_granted_by` FOREIGN KEY (`granted_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nexus_ucd`.`app_user_role` (
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `assigned_by` BIGINT UNSIGNED NULL,
  `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `role_id`),
  KEY `fk_app_user_role_role` (`role_id`),
  KEY `fk_app_user_role_assigned_by` (`assigned_by`),
  CONSTRAINT `fk_app_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `app_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_app_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `app_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_app_user_role_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `app_user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `nexus_ucd`.`app_role`
  (`role_code`, `role_name`, `description`, `is_system`, `is_active`)
VALUES
  ('SYS_ADMIN', 'System Administrator', 'Full application and security administration.', 1, 1),
  ('UCD_ADMIN', 'UCD Administrator', 'Administers governed UCD master and rate data.', 1, 1),
  ('COST_ENGINEER', 'Cost Engineer / QS', 'Builds cost items, rates, and project mappings.', 1, 1),
  ('REVIEWER', 'Reviewer', 'Reviews governed cost information.', 1, 1),
  ('APPROVER', 'Approver', 'Approves and publishes governed cost information.', 1, 1),
  ('PROJECT_USER', 'Project User', 'Works with project and BOQ information.', 1, 1),
  ('DATA_ANALYST', 'Data Analyst', 'Analyzes costs, rates, and historical trends.', 1, 1),
  ('EXEC_VIEWER', 'Executive / Viewer', 'Read-only executive access.', 1, 1);

INSERT IGNORE INTO `nexus_ucd`.`app_permission`
  (`permission_code`, `permission_name`, `module_name`, `description`, `is_active`)
VALUES
  ('dashboard.view', 'View dashboard', 'Dashboard', 'Access the application home and dashboard.', 1),
  ('standard_cost_items.view', 'View standard cost items', 'Standard Cost Items', 'Read enterprise standard cost items.', 1),
  ('standard_cost_items.manage', 'Manage standard cost items', 'Standard Cost Items', 'Create and edit draft standard cost items.', 1),
  ('standard_cost_items.review', 'Review standard cost items', 'Standard Cost Items', 'Perform technical review.', 1),
  ('standard_cost_items.approve', 'Approve standard cost items', 'Standard Cost Items', 'Approve governed standard cost items.', 1),
  ('standard_cost_items.publish', 'Publish standard cost items', 'Standard Cost Items', 'Publish approved standard cost items.', 1),
  ('materials.view', 'View materials', 'Materials', 'Read material master and rates.', 1),
  ('materials.manage', 'Manage materials', 'Materials', 'Maintain material master and rates.', 1),
  ('equipment.view', 'View equipment', 'Equipment', 'Read equipment master and rates.', 1),
  ('equipment.manage', 'Manage equipment', 'Equipment', 'Maintain equipment master and rates.', 1),
  ('labor.view', 'View labor', 'Labor', 'Read labor master and rates.', 1),
  ('labor.manage', 'Manage labor', 'Labor', 'Maintain labor master and rates.', 1),
  ('crews.view', 'View crews', 'Crews', 'Read crew definitions.', 1),
  ('crews.manage', 'Manage crews', 'Crews', 'Maintain crew definitions.', 1),
  ('unit_rates.view', 'View unit-rate build-ups', 'Unit Rates', 'Read resource build-ups.', 1),
  ('unit_rates.manage', 'Manage unit-rate build-ups', 'Unit Rates', 'Maintain resource build-ups.', 1),
  ('rates.view', 'View rate history', 'Rates', 'Read governed rate history.', 1),
  ('rates.manage', 'Manage rates', 'Rates', 'Maintain current and historical rates.', 1),
  ('references.view', 'View reference data', 'Reference Data', 'Read governed reference tables.', 1),
  ('references.manage', 'Manage reference data', 'Reference Data', 'Maintain governed reference tables.', 1),
  ('projects.view', 'View projects', 'Projects', 'Read project master data.', 1),
  ('projects.manage', 'Manage projects', 'Projects', 'Maintain project master data.', 1),
  ('boq.view', 'View BOQ', 'BOQ', 'Read project bills of quantities.', 1),
  ('boq.manage', 'Manage BOQ', 'BOQ', 'Import and maintain bills of quantities.', 1),
  ('boq.map', 'Map BOQ to UCD', 'BOQ', 'Map BOQ items to standard cost items.', 1),
  ('governance.review', 'Perform governance review', 'Governance', 'Review submitted governed records.', 1),
  ('governance.approve', 'Perform governance approval', 'Governance', 'Approve governed records.', 1),
  ('governance.audit', 'View audit trail', 'Governance', 'Read governance and audit history.', 1),
  ('benchmarking.view', 'View cost benchmarking', 'Cost Intelligence', 'Read benchmarking and comparisons.', 1),
  ('cost_intelligence.view', 'View cost intelligence', 'Cost Intelligence', 'Read trends and explainable suggestions.', 1),
  ('users.view', 'View users', 'Administration', 'Read application user accounts.', 1),
  ('users.manage', 'Manage user roles', 'Administration', 'Assign roles to application users.', 1),
  ('roles.view', 'View roles and permissions', 'Administration', 'Read roles and permission assignments.', 1),
  ('roles.manage', 'Manage roles and permissions', 'Administration', 'Maintain roles and permission assignments.', 1),
  ('system_settings.manage', 'Manage system settings', 'Administration', 'Maintain application-level settings.', 1);

-- System administrators receive every active permission.
INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id
FROM `nexus_ucd`.`app_role` r
CROSS JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'SYS_ADMIN' AND p.is_active = 1;

-- UCD administrators manage governed library data, workflows, users, and role visibility.
INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'UCD_ADMIN' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','standard_cost_items.manage','standard_cost_items.review','standard_cost_items.approve','standard_cost_items.publish',
  'materials.view','materials.manage','equipment.view','equipment.manage','labor.view','labor.manage','crews.view','crews.manage',
  'unit_rates.view','unit_rates.manage','rates.view','rates.manage','references.view','references.manage','projects.view','projects.manage',
  'boq.view','boq.manage','boq.map','governance.review','governance.approve','governance.audit','benchmarking.view','cost_intelligence.view',
  'users.view','users.manage','roles.view'
);

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'COST_ENGINEER' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','standard_cost_items.manage','materials.view','materials.manage','equipment.view','equipment.manage',
  'labor.view','labor.manage','crews.view','crews.manage','unit_rates.view','unit_rates.manage','rates.view','rates.manage','references.view',
  'projects.view','projects.manage','boq.view','boq.manage','boq.map','benchmarking.view'
);

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'REVIEWER' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','standard_cost_items.review','materials.view','equipment.view','labor.view','crews.view',
  'unit_rates.view','rates.view','references.view','projects.view','boq.view','governance.review','governance.audit','benchmarking.view'
);

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'APPROVER' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','standard_cost_items.approve','standard_cost_items.publish','materials.view','equipment.view',
  'labor.view','crews.view','unit_rates.view','rates.view','references.view','projects.view','boq.view','governance.approve','governance.audit','benchmarking.view'
);

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'PROJECT_USER' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','materials.view','equipment.view','labor.view','crews.view','unit_rates.view','rates.view',
  'projects.view','boq.view','boq.manage','boq.map'
);

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'DATA_ANALYST' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','materials.view','equipment.view','labor.view','crews.view','unit_rates.view','rates.view',
  'references.view','projects.view','boq.view','governance.audit','benchmarking.view','cost_intelligence.view'
);

INSERT IGNORE INTO `nexus_ucd`.`app_role_permission` (`role_id`, `permission_id`)
SELECT r.role_id, p.permission_id FROM `nexus_ucd`.`app_role` r JOIN `nexus_ucd`.`app_permission` p
WHERE r.role_code = 'EXEC_VIEWER' AND p.permission_code IN (
  'dashboard.view','standard_cost_items.view','materials.view','equipment.view','labor.view','crews.view','unit_rates.view','rates.view',
  'projects.view','benchmarking.view','cost_intelligence.view'
);

-- Bootstrap the existing local administrator as System Administrator.
INSERT IGNORE INTO `nexus_ucd`.`app_user_role` (`user_id`, `role_id`)
SELECT u.user_id, r.role_id
FROM `nexus_ucd`.`app_user` u
JOIN `nexus_ucd`.`app_role` r ON r.role_code = 'SYS_ADMIN'
WHERE u.username = 'admin';
