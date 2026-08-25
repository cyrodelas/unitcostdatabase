-- Application permission contracts for the corrected SCI-independent elemental module.
INSERT INTO app_permission (permission_code,permission_name,module_name,description,is_active)
VALUES
('elemental_costs.view','View elemental costing','Elemental Costing','View elemental cost plans, summaries, and historical elemental rates.',1),
('elemental_costs.manage','Manage elemental costing','Elemental Costing','Create and maintain Draft elemental plans, element lines, and elemental rate evidence.',1),
('elemental_costs.approve','Approve elemental costing','Elemental Costing','Approve and publish reviewed elemental cost plans.',1)
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name),module_name=VALUES(module_name),description=VALUES(description),is_active=1;

INSERT IGNORE INTO app_role_permission (role_id,permission_id)
SELECT r.role_id,p.permission_id FROM app_role r JOIN app_permission p
WHERE (p.permission_code='elemental_costs.view' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN','COST_ENGINEER','REVIEWER','APPROVER','PROJECT_USER','DATA_ANALYST','EXEC_VIEWER'))
   OR (p.permission_code='elemental_costs.manage' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN','COST_ENGINEER'))
   OR (p.permission_code='elemental_costs.approve' AND r.role_code IN ('SYS_ADMIN','UCD_ADMIN','APPROVER'));

