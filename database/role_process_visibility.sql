-- Restrict governed reference/resource master catalogs to UCD and System administrators.
-- Permission contracts remain intact; only non-administrator role grants are removed.

DELETE rp
FROM app_role_permission rp
JOIN app_role r ON r.role_id = rp.role_id
JOIN app_permission p ON p.permission_id = rp.permission_id
WHERE r.role_code NOT IN ('SYS_ADMIN', 'UCD_ADMIN')
  AND p.permission_code IN (
    'materials.view', 'materials.manage',
    'equipment.view', 'equipment.manage',
    'labor.view', 'labor.manage',
    'crews.view', 'crews.manage',
    'references.view', 'references.manage'
  );

