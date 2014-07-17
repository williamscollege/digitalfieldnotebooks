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

CREATE TABLE IF NOT EXISTS `user_role_links` (
  `user_role_link_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`  INT NOT NULL,
  `role_id` INT NOT NULL
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='determines allowable actions within the digitalfieldnotebooks system';
/* FK: users.user_id */
/* FK: roles.role_id */

CREATE TABLE IF NOT EXISTS `actions` (
  `action_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='actions that users can take - together with roles are used to define permissions';

CREATE TABLE IF NOT EXISTS `role_action_target_links` (
  `role_action_target_link_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `action_id` INT NOT NULL,
  `target_type` VARCHAR(255) NOT NULL, /* target_type: global_notebook, global_metadata, global_plants, notebook, metadata_category, metadata_subcategory, plant NOTE: the first two are used for overall managers/admins (and in those cases the target_id is 0) */
  `target_id` INT NOT NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* This is single inheritance table, meaning it is a linking table that links dependant upon the value of target_type */
/* FK: roles.role_id */
/* FK: actions.action_id */
/* FK: target_id: this is the FK that will link this roles record with objects to which permissions are being granted (value is 0 for global permissions) */
/* NOTE: action permissions are hardcoded into the application - a fully fleshed out action control system is outside the scope of this project */

# ----------------------------
# metadata structure

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
  `type` VARCHAR(24) NOT NULL, /* text, image, audio, etc. */
  `external_reference` VARCHAR(255) NULL, /* URL or file path */
  `description` VARCHAR(255) NULL,
  `ordering` DECIMAL NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='example/reference info pertaining to metadata';
/* FK: metadata_structures.metadata_structure_id */

# ----------------------------
# pre-loaded data & global reference/lookup data

CREATE TABLE IF NOT EXISTS `reference_plants` (
  `reference_plant_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `class` VARCHAR(255) NULL,
  `order` VARCHAR(255) NULL,
  `family` VARCHAR(255) NULL,
  `genus` VARCHAR(255) NULL,
  `species` VARCHAR(255) NULL,
  `variety` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';

CREATE TABLE IF NOT EXISTS `reference_plant_extras` (
  `reference_plant_extra_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `reference_plant_id` INT NOT NULL,
  `type` VARCHAR(255) NULL, /* common name, description, image */
  `value` VARCHAR(255) NULL, /* either the direct info (for common name and description) or a URL or file path */
  `ordering` DECIMAL NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: plants.plant_id */

# ----------------------------
# notebooks - the actual meat of the application

CREATE TABLE IF NOT EXISTS `notebooks` (
  `notebook_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: users.user_id */

CREATE TABLE IF NOT EXISTS `notebook_pages` (
  `notebook_page_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `notebook_id` INT NOT NULL,
  `reference_plant_id` INT NOT NULL,
  `notes` TEXT NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: notebooks.notebook_id */
/* FK: plants.plant_id */

CREATE TABLE IF NOT EXISTS `notebook_page_fields` (
  `notebook_page_field_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `notebook_page_id` INT NOT NULL,
  `label_metadata_structure_id` INT NOT NULL,
  `value_metadata_structure_id` INT NOT NULL,
  `value_open` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: notebook_pages.notebook_page_id */
/* FK: label_metadata_structure_id: metadata_structures.metadata_structure_id - link to a category or sub-category record */
/* FK: value_metadata_structure_id: metadata_structures.metadata_structure_id - link to a term record */

# ----------------------------
# specimen data - used both for main reference and for notebook pages - these are particular plants that are example of given plant in the plant table or a notebook_page

CREATE TABLE IF NOT EXISTS `specimens` (
  `specimen_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `link_to_type` INT NOT NULL, /* reference_plant or notebook_page */
  `link_to_id` INT NOT NULL,
  `name` VARCHAR(255) NULL, /* brief identification of the specimen - e.g. 'science quad elm' */
  `gps_x` DECIMAL NULL,
  `gps_y` DECIMAL NULL,
  `notes` TEXT NULL,
  `ordering` DECIMAL NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: if link_to_type == reference_plant : link_to_id -> reference_plants.reference_plant_id */
/* FK: if link_to_type == notebook_page : link_to_id -> notebook_pages.notebook_page_id */

CREATE TABLE IF NOT EXISTS `specimen_images` (
  `specimen_image_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `specimen_id` INT NOT NULL,
  `image_reference` VARCHAR(255) NULL, /* URL or file path */
  `ordering` DECIMAL NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: specimens.specimen_id */

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
