-- Correct Level 3-only elemental lines so they are not multiplied by Level 4 children.
-- This replaces reporting views only and does not mutate elemental business rows.

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_scope_hierarchy AS
SELECT pt.project_type_id,pt.project_type_code,pt.project_type_name,
       ms.market_segment_id,ms.market_segment_code,ms.market_segment_name,
       ese.elemental_scope_element_id,
       l1.uniformat_level1_id,l1.level1_code,l1.level1_name,
       l2.uniformat_level2_id,l2.level2_code,l2.level2_name,
       l3.uniformat_level3_id,l3.level3_code,l3.level3_name,
       l4.uniformat_level4_id,l4.level4_code,l4.level4_name,
       ese.is_required,ese.is_default,ese.display_order,ese.applicability_status
FROM ref_elemental_scope_element ese
JOIN ref_project_type_market_segment ptms ON ptms.project_type_market_segment_id=ese.project_type_market_segment_id
JOIN ref_project_type pt ON pt.project_type_id=ptms.project_type_id
JOIN ref_market_segment ms ON ms.market_segment_id=ptms.market_segment_id
JOIN ref_uniformat_level3 l3 ON l3.uniformat_level3_id=ese.uniformat_level3_id
JOIN ref_uniformat_level2 l2 ON l2.uniformat_level2_id=l3.uniformat_level2_id
JOIN ref_uniformat_level1 l1 ON l1.uniformat_level1_id=l2.uniformat_level1_id
LEFT JOIN ref_uniformat_level4 l4 ON l4.uniformat_level4_id=ese.uniformat_level4_id
WHERE ptms.is_active=1 AND pt.is_active=1 AND ms.is_active=1
  AND l1.is_active=1 AND l2.is_active=1 AND l3.is_active=1
  AND (l4.uniformat_level4_id IS NULL OR l4.is_active=1)
  AND ese.applicability_status='ACTIVE';

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_cost_plan_detail AS
SELECT p.elemental_cost_plan_id,p.cost_plan_code,p.cost_plan_name,p.revision_no,p.project_id,
       pt.project_type_code,pt.project_type_name,ms.market_segment_code,ms.market_segment_name,
       p.basis_date,p.currency_code,p.plan_status,e.elemental_cost_plan_element_id,e.line_no,
       l1.level1_code,l1.level1_name,l2.level2_code,l2.level2_name,
       l3.level3_code,l3.level3_name,l4.level4_code,l4.level4_name,
       b.basis_code,b.basis_name,e.element_quantity,u.uom_code,u.uom_name,
       e.elemental_rate,e.direct_element_amount,
       CASE WHEN b.basis_code='DIRECT_AMOUNT' THEN COALESCE(e.direct_element_amount,0)
            ELSE COALESCE(e.element_quantity,0)*COALESCE(e.elemental_rate,0) END AS element_cost,
       e.material_cost_snapshot,e.labor_cost_snapshot,e.equipment_cost_snapshot,e.other_cost_snapshot,
       e.source_reference,e.notes
FROM elemental_cost_plan_element e
JOIN elemental_cost_plan p ON p.elemental_cost_plan_id=e.elemental_cost_plan_id
JOIN ref_project_type_market_segment ptms ON ptms.project_type_market_segment_id=p.project_type_market_segment_id
JOIN ref_project_type pt ON pt.project_type_id=ptms.project_type_id
JOIN ref_market_segment ms ON ms.market_segment_id=ptms.market_segment_id
JOIN ref_uniformat_level3 l3 ON l3.uniformat_level3_id=e.uniformat_level3_id
JOIN ref_uniformat_level2 l2 ON l2.uniformat_level2_id=l3.uniformat_level2_id
JOIN ref_uniformat_level1 l1 ON l1.uniformat_level1_id=l2.uniformat_level1_id
LEFT JOIN ref_uniformat_level4 l4 ON l4.uniformat_level4_id=e.uniformat_level4_id
JOIN ref_elemental_cost_basis b ON b.elemental_cost_basis_id=e.elemental_cost_basis_id
LEFT JOIN ref_uom u ON u.uom_id=e.uom_id
WHERE e.is_active=1;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_cost_summary_level3 AS
SELECT d.elemental_cost_plan_id,d.cost_plan_code,d.cost_plan_name,d.revision_no,
       d.project_type_code,d.project_type_name,d.market_segment_code,d.market_segment_name,
       d.level1_code,d.level1_name,d.level2_code,d.level2_name,d.level3_code,d.level3_name,
       ROUND(SUM(d.element_cost),2) AS element_cost,
       ROUND(SUM(COALESCE(d.material_cost_snapshot,0)),2) AS material_cost,
       ROUND(SUM(COALESCE(d.labor_cost_snapshot,0)),2) AS labor_cost,
       ROUND(SUM(COALESCE(d.equipment_cost_snapshot,0)),2) AS equipment_cost,
       ROUND(SUM(COALESCE(d.other_cost_snapshot,0)),2) AS other_cost
FROM vw_elemental_cost_plan_detail d
GROUP BY d.elemental_cost_plan_id,d.cost_plan_code,d.cost_plan_name,d.revision_no,
         d.project_type_code,d.project_type_name,d.market_segment_code,d.market_segment_name,
         d.level1_code,d.level1_name,d.level2_code,d.level2_name,d.level3_code,d.level3_name;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_cost_plan_total AS
SELECT p.elemental_cost_plan_id,p.cost_plan_code,p.cost_plan_name,p.revision_no,
       ROUND(COALESCE(SUM(d.element_cost),0),2) AS total_elemental_cost,
       ROUND(COALESCE(SUM(d.element_cost),0)/NULLIF(p.gross_floor_area,0),2) AS total_cost_per_m2_gfa,
       ROUND(COALESCE(SUM(d.element_cost),0)/NULLIF(p.site_area,0),2) AS total_cost_per_m2_site_area,
       ROUND(COALESCE(SUM(d.element_cost),0)/NULLIF(p.saleable_area,0),2) AS total_cost_per_m2_saleable_area,
       ROUND(COALESCE(SUM(d.element_cost),0)/NULLIF(p.unit_count,0),2) AS total_cost_per_residential_unit
FROM elemental_cost_plan p
LEFT JOIN vw_elemental_cost_plan_detail d ON d.elemental_cost_plan_id=p.elemental_cost_plan_id
GROUP BY p.elemental_cost_plan_id,p.cost_plan_code,p.cost_plan_name,p.revision_no,
         p.gross_floor_area,p.site_area,p.saleable_area,p.unit_count;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_cost_summary AS
SELECT s.*,t.total_elemental_cost,
       ROUND(s.element_cost/NULLIF(t.total_elemental_cost,0)*100,2) AS percent_of_total_cost,
       ROUND(s.element_cost/NULLIF(p.gross_floor_area,0),2) AS cost_per_m2_gfa,
       ROUND(s.element_cost/NULLIF(p.site_area,0),2) AS cost_per_m2_site_area,
       ROUND(s.element_cost/NULLIF(p.saleable_area,0),2) AS cost_per_m2_saleable_area,
       ROUND(s.element_cost/NULLIF(p.unit_count,0),2) AS cost_per_residential_unit
FROM vw_elemental_cost_summary_level3 s
JOIN elemental_cost_plan p ON p.elemental_cost_plan_id=s.elemental_cost_plan_id
JOIN vw_elemental_cost_plan_total t ON t.elemental_cost_plan_id=s.elemental_cost_plan_id;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_rate_history AS
SELECT erh.elemental_rate_history_id,pt.project_type_code,pt.project_type_name,
       ms.market_segment_code,ms.market_segment_name,
       l1.level1_code,l1.level1_name,l2.level2_code,l2.level2_name,
       l3.level3_code,l3.level3_name,l4.level4_code,l4.level4_name,
       b.basis_code,b.basis_name,erh.basis_quantity,erh.element_cost,erh.elemental_rate,
       erh.currency_code,erh.rate_date,erh.project_id,erh.location_id,erh.source_type,
       erh.source_reference,erh.validation_status,erh.confidence_score,erh.is_current
FROM elemental_rate_history erh
JOIN ref_project_type_market_segment ptms ON ptms.project_type_market_segment_id=erh.project_type_market_segment_id
JOIN ref_project_type pt ON pt.project_type_id=ptms.project_type_id
JOIN ref_market_segment ms ON ms.market_segment_id=ptms.market_segment_id
JOIN ref_uniformat_level3 l3 ON l3.uniformat_level3_id=erh.uniformat_level3_id
JOIN ref_uniformat_level2 l2 ON l2.uniformat_level2_id=l3.uniformat_level2_id
JOIN ref_uniformat_level1 l1 ON l1.uniformat_level1_id=l2.uniformat_level1_id
LEFT JOIN ref_uniformat_level4 l4 ON l4.uniformat_level4_id=erh.uniformat_level4_id
JOIN ref_elemental_cost_basis b ON b.elemental_cost_basis_id=erh.elemental_cost_basis_id;

CREATE OR REPLACE SQL SECURITY INVOKER VIEW vw_elemental_cost_benchmark AS
SELECT project_type_code,project_type_name,market_segment_code,market_segment_name,
       level3_code,level3_name,basis_code,basis_name,COUNT(*) AS observation_count,
       ROUND(AVG(element_cost),2) AS avg_element_cost,ROUND(MIN(element_cost),2) AS min_element_cost,
       ROUND(MAX(element_cost),2) AS max_element_cost,ROUND(STDDEV_POP(element_cost),2) AS stddev_element_cost,
       ROUND(AVG(elemental_rate),2) AS avg_elemental_rate,ROUND(MIN(elemental_rate),2) AS min_elemental_rate,
       ROUND(MAX(elemental_rate),2) AS max_elemental_rate
FROM vw_elemental_rate_history WHERE validation_status='VALID'
GROUP BY project_type_code,project_type_name,market_segment_code,market_segment_name,
         level3_code,level3_name,basis_code,basis_name;
