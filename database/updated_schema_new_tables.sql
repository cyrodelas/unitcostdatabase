
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_item_attribute_value` (
  `cost_item_attribute_value_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cost_item_revision_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_option_id` bigint(20) unsigned DEFAULT NULL,
  `value_text` varchar(1000) DEFAULT NULL,
  `value_decimal` decimal(24,2) DEFAULT NULL,
  `value_integer` bigint(20) DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_date` date DEFAULT NULL,
  `value_min_decimal` decimal(24,2) DEFAULT NULL,
  `value_max_decimal` decimal(24,2) DEFAULT NULL,
  `uom_id` bigint(20) unsigned DEFAULT NULL,
  `value_source` varchar(255) DEFAULT NULL,
  `value_status` varchar(30) DEFAULT 'DRAFT',
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`cost_item_attribute_value_id`),
  UNIQUE KEY `uq_cost_item_attribute_value` (`cost_item_revision_id`,`attribute_id`),
  KEY `idx_cost_item_attribute_attribute` (`attribute_id`),
  KEY `fk_cost_item_attribute_option` (`attribute_option_id`),
  KEY `fk_cost_item_attribute_uom` (`uom_id`),
  CONSTRAINT `fk_cost_item_attribute_definition` FOREIGN KEY (`attribute_id`) REFERENCES `ref_attribute_definition` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cost_item_attribute_option` FOREIGN KEY (`attribute_option_id`) REFERENCES `ref_attribute_option` (`attribute_option_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cost_item_attribute_revision` FOREIGN KEY (`cost_item_revision_id`) REFERENCES `standard_cost_item_revision` (`cost_item_revision_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cost_item_attribute_uom` FOREIGN KEY (`uom_id`) REFERENCES `ref_uom` (`uom_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1039 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_item_rate_adjustment` (
  `rate_adjustment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_history_id` bigint(20) unsigned DEFAULT NULL,
  `rate_adjustment_type_id` bigint(20) unsigned DEFAULT NULL,
  `adjustment_factor` decimal(12,2) DEFAULT NULL,
  `adjustment_percentage` decimal(10,2) DEFAULT NULL,
  `adjustment_amount` decimal(18,2) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `adjustment_basis` varchar(500) DEFAULT NULL,
  `sequence_no` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_adjustment_id`),
  UNIQUE KEY `uq_cost_item_rate_adjustment` (`rate_history_id`,`rate_adjustment_type_id`,`sequence_no`),
  KEY `fk_rate_adjustment_type` (`rate_adjustment_type_id`),
  CONSTRAINT `fk_rate_adjustment_history` FOREIGN KEY (`rate_history_id`) REFERENCES `cost_item_rate_history` (`rate_history_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_adjustment_type` FOREIGN KEY (`rate_adjustment_type_id`) REFERENCES `ref_rate_adjustment_type` (`rate_adjustment_type_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_item_rate_component` (
  `rate_component_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_history_id` bigint(20) unsigned DEFAULT NULL,
  `rate_cost_component_id` bigint(20) unsigned DEFAULT NULL,
  `amount_per_unit` decimal(18,2) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_component_id`),
  UNIQUE KEY `uq_cost_item_rate_component` (`rate_history_id`,`rate_cost_component_id`),
  KEY `fk_rate_component_type` (`rate_cost_component_id`),
  CONSTRAINT `fk_rate_component_history` FOREIGN KEY (`rate_history_id`) REFERENCES `cost_item_rate_history` (`rate_history_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_component_type` FOREIGN KEY (`rate_cost_component_id`) REFERENCES `ref_rate_cost_component` (`rate_cost_component_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2556 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_item_rate_confidence` (
  `rate_confidence_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_history_id` bigint(20) unsigned DEFAULT NULL,
  `confidence_band_id` bigint(20) unsigned DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `source_quality_score` decimal(5,2) DEFAULT NULL,
  `recency_score` decimal(5,2) DEFAULT NULL,
  `location_match_score` decimal(5,2) DEFAULT NULL,
  `project_type_match_score` decimal(5,2) DEFAULT NULL,
  `validation_score` decimal(5,2) DEFAULT NULL,
  `dispersion_score` decimal(5,2) DEFAULT NULL,
  `sample_adequacy_score` decimal(5,2) DEFAULT NULL,
  `sample_count` int(11) DEFAULT NULL,
  `calculation_version` varchar(30) DEFAULT NULL,
  `confidence_basis` varchar(1000) DEFAULT NULL,
  `calculated_at` datetime DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_confidence_id`),
  KEY `idx_rate_confidence_history` (`rate_history_id`,`is_current`),
  KEY `fk_rate_confidence_band` (`confidence_band_id`),
  CONSTRAINT `fk_rate_confidence_band` FOREIGN KEY (`confidence_band_id`) REFERENCES `ref_confidence_band` (`confidence_band_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_confidence_history` FOREIGN KEY (`rate_history_id`) REFERENCES `cost_item_rate_history` (`rate_history_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_item_rate_markup` (
  `rate_markup_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_history_id` bigint(20) unsigned DEFAULT NULL,
  `markup_type_id` bigint(20) unsigned DEFAULT NULL,
  `markup_calculation_method_id` bigint(20) unsigned DEFAULT NULL,
  `markup_percentage` decimal(10,2) DEFAULT NULL,
  `markup_amount` decimal(18,2) DEFAULT NULL,
  `calculation_base_amount` decimal(18,2) DEFAULT NULL,
  `sequence_no` int(11) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_markup_id`),
  UNIQUE KEY `uq_cost_item_rate_markup` (`rate_history_id`,`markup_type_id`,`sequence_no`),
  KEY `idx_rate_markup_rate_history` (`rate_history_id`),
  KEY `fk_rate_markup_type` (`markup_type_id`),
  KEY `fk_rate_markup_method` (`markup_calculation_method_id`),
  CONSTRAINT `fk_rate_markup_method` FOREIGN KEY (`markup_calculation_method_id`) REFERENCES `ref_markup_calculation_method` (`markup_calculation_method_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_markup_rate_history` FOREIGN KEY (`rate_history_id`) REFERENCES `cost_item_rate_history` (`rate_history_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rate_markup_type` FOREIGN KEY (`markup_type_id`) REFERENCES `ref_cost_markup_type` (`markup_type_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_attribute_value` (
  `equipment_attribute_value_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_option_id` bigint(20) unsigned DEFAULT NULL,
  `value_text` varchar(1000) DEFAULT NULL,
  `value_decimal` decimal(24,2) DEFAULT NULL,
  `value_integer` bigint(20) DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_date` date DEFAULT NULL,
  `value_min_decimal` decimal(24,2) DEFAULT NULL,
  `value_max_decimal` decimal(24,2) DEFAULT NULL,
  `uom_id` bigint(20) unsigned DEFAULT NULL,
  `value_source` varchar(255) DEFAULT NULL,
  `value_status` varchar(30) DEFAULT 'DRAFT',
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`equipment_attribute_value_id`),
  UNIQUE KEY `uq_equipment_attribute_value` (`equipment_id`,`attribute_id`),
  KEY `fk_equipment_attribute_definition` (`attribute_id`),
  KEY `fk_equipment_attribute_option` (`attribute_option_id`),
  KEY `fk_equipment_attribute_uom` (`uom_id`),
  CONSTRAINT `fk_equipment_attribute_definition` FOREIGN KEY (`attribute_id`) REFERENCES `ref_attribute_definition` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_equipment_attribute_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment_master` (`equipment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_equipment_attribute_option` FOREIGN KEY (`attribute_option_id`) REFERENCES `ref_attribute_option` (`attribute_option_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_equipment_attribute_uom` FOREIGN KEY (`uom_id`) REFERENCES `ref_uom` (`uom_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material_attribute_value` (
  `material_attribute_value_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_option_id` bigint(20) unsigned DEFAULT NULL,
  `value_text` varchar(1000) DEFAULT NULL,
  `value_decimal` decimal(24,2) DEFAULT NULL,
  `value_integer` bigint(20) DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_date` date DEFAULT NULL,
  `value_min_decimal` decimal(24,2) DEFAULT NULL,
  `value_max_decimal` decimal(24,2) DEFAULT NULL,
  `uom_id` bigint(20) unsigned DEFAULT NULL,
  `value_source` varchar(255) DEFAULT NULL,
  `value_status` varchar(30) DEFAULT 'DRAFT',
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`material_attribute_value_id`),
  UNIQUE KEY `uq_material_attribute_value` (`material_id`,`attribute_id`),
  KEY `idx_material_attribute_attribute` (`attribute_id`),
  KEY `fk_material_attribute_option` (`attribute_option_id`),
  KEY `fk_material_attribute_uom` (`uom_id`),
  CONSTRAINT `fk_material_attribute_definition` FOREIGN KEY (`attribute_id`) REFERENCES `ref_attribute_definition` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_material_attribute_material` FOREIGN KEY (`material_id`) REFERENCES `material_master` (`material_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_material_attribute_option` FOREIGN KEY (`attribute_option_id`) REFERENCES `ref_attribute_option` (`attribute_option_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_material_attribute_uom` FOREIGN KEY (`uom_id`) REFERENCES `ref_uom` (`uom_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material_variant_attribute_value` (
  `material_variant_attribute_value_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_variant_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_option_id` bigint(20) unsigned DEFAULT NULL,
  `value_text` varchar(1000) DEFAULT NULL,
  `value_decimal` decimal(24,2) DEFAULT NULL,
  `value_integer` bigint(20) DEFAULT NULL,
  `value_boolean` tinyint(1) DEFAULT NULL,
  `value_date` date DEFAULT NULL,
  `value_min_decimal` decimal(24,2) DEFAULT NULL,
  `value_max_decimal` decimal(24,2) DEFAULT NULL,
  `uom_id` bigint(20) unsigned DEFAULT NULL,
  `value_source` varchar(255) DEFAULT NULL,
  `value_status` varchar(30) DEFAULT 'DRAFT',
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`material_variant_attribute_value_id`),
  UNIQUE KEY `uq_material_variant_attribute_value` (`material_variant_id`,`attribute_id`),
  KEY `idx_material_variant_attribute_attribute` (`attribute_id`),
  KEY `fk_material_variant_attribute_option` (`attribute_option_id`),
  KEY `fk_material_variant_attribute_uom` (`uom_id`),
  CONSTRAINT `fk_material_variant_attribute_definition` FOREIGN KEY (`attribute_id`) REFERENCES `ref_attribute_definition` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_material_variant_attribute_option` FOREIGN KEY (`attribute_option_id`) REFERENCES `ref_attribute_option` (`attribute_option_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_material_variant_attribute_uom` FOREIGN KEY (`uom_id`) REFERENCES `ref_uom` (`uom_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_material_variant_attribute_variant` FOREIGN KEY (`material_variant_id`) REFERENCES `material_variant` (`material_variant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_applicability` (
  `attribute_subject_class_id` bigint(20) unsigned NOT NULL,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `requirement_level` varchar(20) DEFAULT NULL,
  `applicability_source` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`attribute_subject_class_id`,`attribute_id`),
  KEY `fk_ref_attribute_applicability_attribute` (`attribute_id`),
  CONSTRAINT `fk_ref_attribute_applicability_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `ref_attribute_definition` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_attribute_applicability_class` FOREIGN KEY (`attribute_subject_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_class_closure` (
  `ancestor_class_id` bigint(20) unsigned NOT NULL,
  `descendant_class_id` bigint(20) unsigned NOT NULL,
  `hierarchy_depth` int(11) DEFAULT NULL,
  PRIMARY KEY (`ancestor_class_id`,`descendant_class_id`),
  KEY `idx_attr_closure_descendant` (`descendant_class_id`),
  CONSTRAINT `fk_attr_closure_ancestor` FOREIGN KEY (`ancestor_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_attr_closure_descendant` FOREIGN KEY (`descendant_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_data_type` (
  `attribute_data_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data_type_code` varchar(20) DEFAULT NULL,
  `data_type_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`attribute_data_type_id`),
  UNIQUE KEY `uq_ref_attribute_data_type_code` (`data_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_definition` (
  `attribute_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_code` varchar(80) DEFAULT NULL,
  `attribute_name` varchar(200) DEFAULT NULL,
  `attribute_group_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_data_type_id` bigint(20) unsigned DEFAULT NULL,
  `attribute_scope` varchar(30) DEFAULT NULL,
  `default_uom_id` bigint(20) unsigned DEFAULT NULL,
  `default_unit_symbol` varchar(40) DEFAULT NULL,
  `decimal_places` int(11) DEFAULT 2,
  `description` varchar(1000) DEFAULT NULL,
  `source_basis` varchar(50) DEFAULT NULL,
  `external_property_set` varchar(150) DEFAULT NULL,
  `external_property_name` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attribute_id`),
  UNIQUE KEY `uq_ref_attribute_definition_code` (`attribute_code`),
  KEY `idx_ref_attribute_group` (`attribute_group_id`),
  KEY `idx_ref_attribute_data_type` (`attribute_data_type_id`),
  KEY `idx_ref_attribute_default_uom` (`default_uom_id`),
  CONSTRAINT `fk_ref_attribute_definition_data_type` FOREIGN KEY (`attribute_data_type_id`) REFERENCES `ref_attribute_data_type` (`attribute_data_type_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_attribute_definition_group` FOREIGN KEY (`attribute_group_id`) REFERENCES `ref_attribute_group` (`attribute_group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_attribute_definition_uom` FOREIGN KEY (`default_uom_id`) REFERENCES `ref_uom` (`uom_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=551 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_group` (
  `attribute_group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_code` varchar(20) DEFAULT NULL,
  `group_name` varchar(150) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attribute_group_id`),
  UNIQUE KEY `uq_ref_attribute_group_code` (`group_code`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_option` (
  `attribute_option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint(20) unsigned DEFAULT NULL,
  `option_code` varchar(80) DEFAULT NULL,
  `option_label` varchar(200) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`attribute_option_id`),
  UNIQUE KEY `uq_ref_attribute_option` (`attribute_id`,`option_code`),
  CONSTRAINT `fk_ref_attribute_option_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `ref_attribute_definition` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_attribute_subject_class` (
  `attribute_subject_class_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` varchar(50) DEFAULT NULL,
  `class_name` varchar(200) DEFAULT NULL,
  `parent_class_id` bigint(20) unsigned DEFAULT NULL,
  `subject_scope` varchar(30) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attribute_subject_class_id`),
  UNIQUE KEY `uq_ref_attribute_subject_class_code` (`class_code`),
  KEY `idx_ref_attribute_subject_parent` (`parent_class_id`),
  CONSTRAINT `fk_ref_attribute_subject_parent` FOREIGN KEY (`parent_class_id`) REFERENCES `ref_attribute_subject_class` (`attribute_subject_class_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_city_class` (
  `city_class_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `city_class_code` varchar(20) DEFAULT NULL,
  `city_class_name` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`city_class_id`),
  UNIQUE KEY `uq_ref_city_class_code` (`city_class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_confidence_band` (
  `confidence_band_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `confidence_code` varchar(20) DEFAULT NULL,
  `confidence_name` varchar(50) DEFAULT NULL,
  `minimum_score` decimal(5,2) DEFAULT NULL,
  `maximum_score` decimal(5,2) DEFAULT NULL,
  `is_recommendable` tinyint(1) DEFAULT 0,
  `definition_origin` varchar(30) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`confidence_band_id`),
  UNIQUE KEY `uq_ref_confidence_band_code` (`confidence_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_cost_markup_type` (
  `markup_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `markup_type_code` varchar(30) DEFAULT NULL,
  `markup_type_name` varchar(150) DEFAULT NULL,
  `markup_category` varchar(50) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `calculation_sequence` int(11) DEFAULT NULL,
  `definition_origin` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`markup_type_id`),
  UNIQUE KEY `uq_ref_cost_markup_type_code` (`markup_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_country` (
  `country_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `iso_alpha2` char(2) DEFAULT NULL,
  `iso_alpha3` char(3) DEFAULT NULL,
  `country_name` varchar(150) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`country_id`),
  UNIQUE KEY `uq_ref_country_iso2` (`iso_alpha2`),
  UNIQUE KEY `uq_ref_country_iso3` (`iso_alpha3`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_division` (
  `division_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `division_code` varchar(20) DEFAULT NULL,
  `division_name` varchar(150) DEFAULT NULL,
  `division_description` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`division_id`),
  UNIQUE KEY `uq_ref_division_code` (`division_code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_income_classification` (
  `income_classification_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `income_classification_code` varchar(50) DEFAULT NULL,
  `income_classification_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`income_classification_id`),
  UNIQUE KEY `uq_ref_income_classification_code` (`income_classification_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_income_classification_rule` (
  `income_classification_rule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_level_code` varchar(30) DEFAULT NULL,
  `income_classification_id` bigint(20) unsigned DEFAULT NULL,
  `minimum_average_annual_income` decimal(18,2) DEFAULT NULL,
  `maximum_average_annual_income` decimal(18,2) DEFAULT NULL,
  `currency_code` char(3) DEFAULT 'PHP',
  `source_basis` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`income_classification_rule_id`),
  UNIQUE KEY `uq_income_rule` (`location_level_code`,`income_classification_id`),
  KEY `idx_income_rule_class` (`income_classification_id`),
  CONSTRAINT `fk_income_rule_class` FOREIGN KEY (`income_classification_id`) REFERENCES `ref_income_classification` (`income_classification_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_island_group` (
  `island_group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `island_group_code` varchar(20) DEFAULT NULL,
  `island_group_name` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`island_group_id`),
  UNIQUE KEY `uq_ref_island_group_code` (`island_group_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_location` (
  `location_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `parent_location_id` bigint(20) unsigned DEFAULT NULL,
  `location_level_id` bigint(20) unsigned DEFAULT NULL,
  `location_release_id` bigint(20) unsigned DEFAULT NULL,
  `island_group_id` bigint(20) unsigned DEFAULT NULL,
  `psgc_code` char(10) DEFAULT NULL,
  `correspondence_code` char(9) DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `old_name` varchar(255) DEFAULT NULL,
  `city_class_id` bigint(20) unsigned DEFAULT NULL,
  `income_classification_id` bigint(20) unsigned DEFAULT NULL,
  `urban_rural_id` bigint(20) unsigned DEFAULT NULL,
  `source_geographic_level` varchar(30) DEFAULT NULL,
  `source_status` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `income_class_retained_flag` tinyint(1) DEFAULT 0,
  `source_income_classification` varchar(50) DEFAULT NULL,
  `source_urban_rural` varchar(20) DEFAULT NULL,
  `population_2024` bigint(20) DEFAULT NULL,
  `population_2024_source_value` varchar(50) DEFAULT NULL,
  `location_status_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `uq_ref_location_psgc_code` (`psgc_code`),
  KEY `idx_ref_location_parent` (`parent_location_id`),
  KEY `idx_ref_location_level` (`location_level_id`),
  KEY `idx_ref_location_name` (`location_name`),
  KEY `idx_ref_location_country` (`country_id`),
  KEY `idx_ref_location_release` (`location_release_id`),
  KEY `fk_ref_location_island_group` (`island_group_id`),
  KEY `fk_ref_location_city_class` (`city_class_id`),
  KEY `fk_ref_location_income_classification` (`income_classification_id`),
  KEY `fk_ref_location_urban_rural` (`urban_rural_id`),
  CONSTRAINT `fk_ref_location_city_class` FOREIGN KEY (`city_class_id`) REFERENCES `ref_city_class` (`city_class_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_country` FOREIGN KEY (`country_id`) REFERENCES `ref_country` (`country_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_income_classification` FOREIGN KEY (`income_classification_id`) REFERENCES `ref_income_classification` (`income_classification_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_island_group` FOREIGN KEY (`island_group_id`) REFERENCES `ref_island_group` (`island_group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_level` FOREIGN KEY (`location_level_id`) REFERENCES `ref_location_level` (`location_level_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_parent` FOREIGN KEY (`parent_location_id`) REFERENCES `ref_location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_release` FOREIGN KEY (`location_release_id`) REFERENCES `ref_location_release` (`location_release_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_location_urban_rural` FOREIGN KEY (`urban_rural_id`) REFERENCES `ref_urban_rural_classification` (`urban_rural_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65567 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_location_alias` (
  `location_alias_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `alias_name` varchar(255) DEFAULT NULL,
  `alias_type` varchar(30) DEFAULT 'OLD_NAME',
  `normalized_alias` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`location_alias_id`),
  UNIQUE KEY `uq_ref_location_alias` (`location_id`,`alias_name`,`alias_type`),
  KEY `idx_ref_location_alias_normalized` (`normalized_alias`),
  CONSTRAINT `fk_ref_location_alias_location` FOREIGN KEY (`location_id`) REFERENCES `ref_location` (`location_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2048 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_location_level` (
  `location_level_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `level_code` varchar(30) DEFAULT NULL,
  `level_name` varchar(100) DEFAULT NULL,
  `source_level_code` varchar(30) DEFAULT NULL,
  `hierarchy_order` int(11) DEFAULT NULL,
  `is_administrative` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`location_level_id`),
  UNIQUE KEY `uq_ref_location_level_code` (`level_code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_location_release` (
  `location_release_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `release_code` varchar(50) DEFAULT NULL,
  `release_name` varchar(255) DEFAULT NULL,
  `as_of_date` date DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `source_authority` varchar(150) DEFAULT NULL,
  `source_status` varchar(50) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`location_release_id`),
  UNIQUE KEY `uq_ref_location_release_code` (`release_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_location_status` (
  `location_status_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_status_code` varchar(30) DEFAULT NULL,
  `location_status_name` varchar(100) DEFAULT NULL,
  `source_value` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`location_status_id`),
  UNIQUE KEY `uq_ref_location_status_code` (`location_status_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_markup_calculation_method` (
  `markup_calculation_method_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `calculation_method_code` varchar(30) DEFAULT NULL,
  `calculation_method_name` varchar(100) DEFAULT NULL,
  `method_category` varchar(30) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`markup_calculation_method_id`),
  UNIQUE KEY `uq_ref_markup_method_code` (`calculation_method_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_price_period` (
  `price_period_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `period_code` varchar(20) DEFAULT NULL,
  `price_period_type_id` bigint(20) unsigned DEFAULT NULL,
  `period_year` int(11) DEFAULT NULL,
  `period_quarter` int(11) DEFAULT NULL,
  `period_month` int(11) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `period_label` varchar(50) DEFAULT NULL,
  `sort_key` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`price_period_id`),
  UNIQUE KEY `uq_ref_price_period_code` (`period_code`),
  KEY `idx_ref_price_period_dates` (`period_start`,`period_end`),
  KEY `fk_ref_price_period_type` (`price_period_type_id`),
  CONSTRAINT `fk_ref_price_period_type` FOREIGN KEY (`price_period_type_id`) REFERENCES `ref_price_period_type` (`price_period_type_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=868 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_price_period_type` (
  `price_period_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `period_type_code` varchar(20) DEFAULT NULL,
  `period_type_name` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`price_period_type_id`),
  UNIQUE KEY `uq_ref_price_period_type_code` (`period_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_project_category` (
  `project_category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_category_code` varchar(20) DEFAULT NULL,
  `project_category_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`project_category_id`),
  UNIQUE KEY `uq_ref_project_category_code` (`project_category_code`),
  UNIQUE KEY `uq_ref_project_category_name` (`project_category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_project_group` (
  `project_group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_group_code` varchar(30) DEFAULT NULL,
  `project_group_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`project_group_id`),
  UNIQUE KEY `uq_ref_project_group_code` (`project_group_code`),
  UNIQUE KEY `uq_ref_project_group_name` (`project_group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_project_type` (
  `project_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_type_code` varchar(50) DEFAULT NULL,
  `project_type_name` varchar(255) DEFAULT NULL,
  `project_type_short` varchar(100) DEFAULT NULL,
  `abbreviation` varchar(30) DEFAULT NULL,
  `project_category_id` bigint(20) unsigned DEFAULT NULL,
  `project_group_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`project_type_id`),
  UNIQUE KEY `uq_ref_project_type_code` (`project_type_code`),
  KEY `idx_ref_project_type_category` (`project_category_id`),
  KEY `idx_ref_project_type_group` (`project_group_id`),
  CONSTRAINT `fk_ref_project_type_category` FOREIGN KEY (`project_category_id`) REFERENCES `ref_project_category` (`project_category_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ref_project_type_group` FOREIGN KEY (`project_group_id`) REFERENCES `ref_project_group` (`project_group_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_rate_adjustment_type` (
  `rate_adjustment_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_type_code` varchar(30) DEFAULT NULL,
  `adjustment_type_name` varchar(150) DEFAULT NULL,
  `adjustment_category` varchar(50) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `definition_origin` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`rate_adjustment_type_id`),
  UNIQUE KEY `uq_ref_rate_adjustment_type_code` (`adjustment_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_rate_basis` (
  `rate_basis_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_basis_code` varchar(30) DEFAULT NULL,
  `rate_basis_name` varchar(100) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`rate_basis_id`),
  UNIQUE KEY `uq_ref_rate_basis_code` (`rate_basis_code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_rate_cost_component` (
  `rate_cost_component_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `component_code` varchar(40) DEFAULT NULL,
  `component_name` varchar(150) DEFAULT NULL,
  `component_category` varchar(50) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_direct_cost` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`rate_cost_component_id`),
  UNIQUE KEY `uq_ref_rate_cost_component_code` (`component_code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_rate_source_type` (
  `rate_source_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_type_code` varchar(50) DEFAULT NULL,
  `source_type_name` varchar(150) DEFAULT NULL,
  `source_category` varchar(50) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_rate_evidence` tinyint(1) DEFAULT 1,
  `definition_origin` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_source_type_id`),
  UNIQUE KEY `uq_ref_rate_source_type_code` (`source_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_urban_rural_classification` (
  `urban_rural_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `urban_rural_code` varchar(20) DEFAULT NULL,
  `urban_rural_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `source_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`urban_rural_id`),
  UNIQUE KEY `uq_ref_urban_rural_code` (`urban_rural_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_validation_status` (
  `validation_status_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `validation_status_code` varchar(30) DEFAULT NULL,
  `validation_status_name` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_valid_evidence` tinyint(1) DEFAULT 0,
  `description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`validation_status_id`),
  UNIQUE KEY `uq_ref_validation_status_code` (`validation_status_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stg_psgc_location` (
  `staging_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `psgc_code` varchar(20) DEFAULT NULL,
  `area_name` varchar(255) DEFAULT NULL,
  `correspondence_code` varchar(20) DEFAULT NULL,
  `geographic_level` varchar(30) DEFAULT NULL,
  `reg` varchar(10) DEFAULT NULL,
  `prv` varchar(10) DEFAULT NULL,
  `mun` varchar(10) DEFAULT NULL,
  `bgy` varchar(10) DEFAULT NULL,
  `old_name` varchar(255) DEFAULT NULL,
  `city_class` varchar(50) DEFAULT NULL,
  `income_classification` varchar(100) DEFAULT NULL,
  `urban_rural` varchar(50) DEFAULT NULL,
  `island_region` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`staging_id`),
  KEY `idx_stg_psgc_code` (`psgc_code`),
  KEY `idx_stg_psgc_level` (`geographic_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

