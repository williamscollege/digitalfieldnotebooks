/* 
SAVE:
	DB Creation and Maintanence Script

PROJECT:
	Digital Field Notebooks (digitalfieldnotebooks)

TODO:
	schedules
	all TODO items

NOTES:
	reservations are for items, not groups. if a manager reserves a group, she is really reserving all items (i.e. to take the group offline for a period of time)

FOR TESTING ONLY:
	DROP TABLE `eq_groups`;
	DROP TABLE `eq_subgroups`;
	DROP TABLE `eq_items`;
	DROP TABLE `users`;
	DROP TABLE `inst_groups`;
	DROP TABLE `inst_memberships`;
	DROP TABLE `comm_prefs`;
	DROP TABLE `roles`;
	DROP TABLE `permissions`;
	DROP TABLE `schedules`;
	DROP TABLE `reservations`;
	DROP TABLE `time_blocks`;
	DROP TABLE `queued_messages`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to run this script against
# ----------------------------
-- USE digitalfieldnotebooks;
USE digitalfieldnotebookstest;


# ----------------------------
# basic application infrastructure

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `username` VARCHAR(255) NOT NULL,
  `screen_name` VARCHAR(255) NULL,
  `flag_is_system_admin` BIT(1) NOT NULL DEFAULT 0,
  `flag_is_banned` BIT(1) NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`priority` INT NOT NULL,    
	`name` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='determines allowable actions within the digitalfieldnotebooks system';
/* priority: Highest admin role is priority = 1; lowest anonymous/guest priority is > 1 */

CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`  INT NOT NULL,
  `role_id` INT NOT NULL,
  `target_type` VARCHAR(255) NOT NULL,
  `target_id` INT NOT NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* This is single inheritance table, meaning it is a linking table that links dependant upon the value of target_type */
/* FK: target_id: this is the FK that will link this roles record with objects to which permissions are being granted */
/* target_type: global_notebook, global_metadata, notebook, metadata_category, metadata_subcategory NOTE: the first two are used for overall managers/admins */
/* FK: roles.role_id */
/* FK: users.user_id */
/* NOTE: action permissions are hardcoded into the application - a fully fleshed out action control system is outside the scope of this project */

# ----------------------------
# metadata

CREATE TABLE IF NOT EXISTS `metadata_structures` (
  `metadata_structure_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(24) NOT NULL, /* category, sub-category, term */
  `name` VARCHAR(255) NULL,
  `ordering` DECIMAL NOT NULL DEFAULT 0, /* NOTE: the main ordering is by category, then sub-category, then term; this sorts within those levels, not across them */
  `description` VARCHAR(255) NULL,
  `details` TEXT NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='defining the metadata available for notebook pages';

CREATE TABLE IF NOT EXISTS `metadata_references` (
  `metadata_reference_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `metadata_structure_id` INT NOT NULL,
  `type` VARCHAR(24) NOT NULL, /* text, image, audio */
  `external_reference` VARCHAR(255) NULL, /* URL or file location */
  `description` VARCHAR(255) NULL,
  `ordering` DECIMAL NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='example/reference info pertaining to metadata';
/* FK: metadata_structure.metadata_structure_id */

# ----------------------------
# pre-loaded data

CREATE TABLE IF NOT EXISTS `plants` (
  `plant_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `class` VARCHAR(255) NULL,
  `order` VARCHAR(255) NULL,
  `family` VARCHAR(255) NULL,
  `genus` VARCHAR(255) NULL,
  `species` VARCHAR(255) NULL,
  `variety` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';

CREATE TABLE IF NOT EXISTS `plant_extras` (
  `plant_extra_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `plant_id` INT NOT NULL,
  `type` VARCHAR(255) NULL, /* common name, description, image */
  `value` VARCHAR(255) NULL, /* either the direct info (for common name and description) or a URL or file path */
  `ordering` DECIMAL NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';


#####################
# Required: The Absolute Minimalist Approach to Initial Data Population
#####################

# Required constant values for roles table
INSERT INTO 
	roles
VALUES
(1,10,'Manager',0),
(2,20,'Field User',0),
(3,30,'Public',0)
