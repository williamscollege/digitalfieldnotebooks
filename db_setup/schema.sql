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
	DROP TABLE `users`;
	DROP TABLE `roles`;
	DROP TABLE `user_role_links`;
	DROP TABLE `actions`;
	DROP TABLE `role_action_target_links`;
	DROP TABLE `metadata_structures`;
	DROP TABLE `metadata_term_sets`;
	DROP TABLE `metadata_term_values`;
	DROP TABLE `metadata_references`;
	DROP TABLE `authoritative_plants`;
	DROP TABLE `authoritative_plant_extras`;
	DROP TABLE `notebooks`;
	DROP TABLE `notebook_pages`;
	DROP TABLE `notebook_page_fields`;
	DROP TABLE `specimens`;
	DROP TABLE `specimen_images`;
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
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `last_user_id` INT NOT NULL, /* id of the user that created/updated this record */
  `user_id`  INT NOT NULL,
  `role_id` INT NOT NULL
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='determines allowable actions within the digitalfieldnotebooks system';
/* FK: users.user_id */
/* FK: roles.role_id */

CREATE TABLE IF NOT EXISTS `actions` (
  `action_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NULL,
  `ordering` DECIMAL (10,5),
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='actions that users can take - together with roles are used to define permissions';

CREATE TABLE IF NOT EXISTS `role_action_target_links` (
  `role_action_target_link_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `last_user_id` INT NOT NULL, /* id of the user that created/updated this record */
  `role_id` INT NOT NULL,
  `action_id` INT NOT NULL,
  `target_type` VARCHAR(255) NOT NULL, /* target_type: global_notebook, global_metadata, global_plants, global_specimens, notebook, metadata_category, metadata_subcategory, plant, specimen NOTE: the first two are used for overall managers/admins (and in those cases the target_id is 0) */
  `target_id` INT NOT NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* This is single inheritance table, meaning it is a linking table that links dependant upon the value of target_type */
/* FK: roles.role_id */
/* FK: actions.action_id */
/* FK: target_id: this is the FK that will link this roles record with objects to which permissions are being granted (value is 0 for global permissions) */
/* NOTE: action permissions are hardcoded into the application - a fully fleshed out action control system is outside the scope of this project */

# ----------------------------
# metadata stuff

CREATE TABLE IF NOT EXISTS `metadata_structures` (
  `metadata_structure_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `parent_metadata_structure_id` INT NULL, /* if null or <= 0 then this record is a top-level category */
  `name` VARCHAR(255) NULL,
  `ordering` DECIMAL (10,5),
  `description` VARCHAR(255) NULL,
  `details` TEXT NULL,
  `metadata_term_set_id` INT NULL, /* if null or <= 0 then this element has no controlled vocab */
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='defining the metadata available for notebook pages';
/* FK:parent_metadata_structure_id - metadata_structures.metadata_structure_id */
/* FK: metadata_term_sets.metadata_term_set_id */

CREATE TABLE IF NOT EXISTS `metadata_term_sets` (
  `metadata_term_set_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `name` VARCHAR(255) NULL,
  `ordering` DECIMAL(10,5)NOT NULL DEFAULT 0, /* NOTE: within a given ordering value the term sets are ordered by name */
  `description` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='defining the sets of controlled vocab';

CREATE TABLE IF NOT EXISTS `metadata_term_values` (
  `metadata_term_value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `metadata_term_set_id` INT NOT NULL,
  `name` VARCHAR(255) NULL,
  `ordering` DECIMAL(10,5)NOT NULL DEFAULT 0, /* NOTE: within a given ordering value the term values are ordered by name */
  `description` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='defining the actual values of controlled vocab';
/* FK:metadata_term_set_id -  metadata_term_sets.metadata_term_set_id */

CREATE TABLE IF NOT EXISTS `metadata_references` (
  `metadata_reference_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `metadata_type` VARCHAR(24) NULL, /* structure, term_set, term_value */
  `metadata_id` INT NOT NULL,
  `type` VARCHAR(24) NOT NULL, /* text, image, audio, etc. */
  `external_reference` VARCHAR(255) NULL, /* URL or file path */
  `description` VARCHAR(255) NULL,
  `ordering` DECIMAL(10,5)NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='example/reference info pertaining to metadata';
/* FK:metadata_id - single value inheritance pattern: metadata_structures.metadata_structure_id, or metadata_term_sets.metadata_term_set_id or metadata_term_values.metadata_term_value_id*/

# ----------------------------
# pre-loaded data & global reference/lookup data

CREATE TABLE IF NOT EXISTS `authoritative_plants` (
  `authoritative_plant_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `class` VARCHAR(255) NULL,
  `order` VARCHAR(255) NULL,
  `family` VARCHAR(255) NULL,
  `genus` VARCHAR(255) NULL,
  `species` VARCHAR(255) NULL,
  `variety` VARCHAR(255) NULL,
  `catalog_identifier` VARCHAR(255) NULL,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';

CREATE TABLE IF NOT EXISTS `authoritative_plant_extras` (
  `authoritative_plant_extra_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `authoritative_plant_id` INT NOT NULL,
  `type` VARCHAR(255) NULL, /* common name, description, image */
  `value` VARCHAR(255) NULL, /* either the direct info (for common name and description) or a URL or file path */
  `ordering` DECIMAL(10,5)NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: plants.plant_id */

# ----------------------------
# notebooks - the actual meat of the application

CREATE TABLE IF NOT EXISTS `notebooks` (
  `notebook_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `flag_workflow_published` BIT(1) NOT NULL DEFAULT 0,
  `flag_workflow_validated` BIT(1) NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: users.user_id */

CREATE TABLE IF NOT EXISTS `notebook_pages` (
  `notebook_page_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `notebook_id` INT NOT NULL,
  `authoritative_plant_id` INT NOT NULL,
  `notes` TEXT NULL,
  `flag_workflow_published` BIT(1) NOT NULL DEFAULT 0,
  `flag_workflow_validated` BIT(1) NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: notebooks.notebook_id */
/* FK: plants.plant_id */

CREATE TABLE IF NOT EXISTS `notebook_page_fields` (
  `notebook_page_field_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `notebook_page_id` INT NOT NULL,
  `label_metadata_structure_id` INT NOT NULL,
  `value_metadata_term_value_id` INT NOT NULL,
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
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `user_id` INT NOT NULL,
  `link_to_type` VARCHAR(255) NULL, /* authoritative_plant or notebook_page */
  `link_to_id` INT NOT NULL,
  `name` VARCHAR(255) NULL, /* brief identification of the specimen - e.g. 'science quad elm' */
  `gps_x` DECIMAL(10,7)NULL,
  `gps_y` DECIMAL(10,7)NULL,
  `notes` TEXT NULL,
  `ordering` DECIMAL(10,5)NOT NULL DEFAULT 0,
  `catalog_identifier` VARCHAR(255) NULL,
  `flag_workflow_published` BIT(1) NOT NULL DEFAULT 0,
  `flag_workflow_validated` BIT(1) NOT NULL DEFAULT 0,
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: if link_to_type == authoritative_plant : link_to_id -> authoritative_plants.authoritative_plant_id */
/* FK: if link_to_type == notebook_page : link_to_id -> notebook_pages.notebook_page_id */

CREATE TABLE IF NOT EXISTS `specimen_images` (
  `specimen_image_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `specimen_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `image_reference` VARCHAR(255) NULL, /* URL or file path */
  `ordering` DECIMAL(10,5)NOT NULL DEFAULT 0,
  `flag_workflow_published` BIT(1) NOT NULL DEFAULT 0,
  `flag_workflow_validated` BIT(1) NOT NULL DEFAULT 0,
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
(1,10,'manager',0),
(2,15,'assistant',0),
(3,20,'field user',0),
(4,30,'public',0);

# Required constant values for actions table
INSERT INTO
actions
VALUES
(1,'view',1,0),
(2,'edit',2,0),
(3,'create',3,0),
(4,'delete',4,0),
(5,'publish',5,0),
(6,'verify',6,0);

# Required constant values for role_action_target_links table (managers can do everything)
INSERT INTO
  role_action_target_links
  VALUES
  (1,NOW(),NOW(),0,1,1,'global_notebook',0,0),
  (2,NOW(),NOW(),0,1,2,'global_notebook',0,0),
  (3,NOW(),NOW(),0,1,3,'global_notebook',0,0),
  (4,NOW(),NOW(),0,1,4,'global_notebook',0,0),
  (5,NOW(),NOW(),0,1,5,'global_notebook',0,0),
  (6,NOW(),NOW(),0,1,6,'global_notebook',0,0),
  (7,NOW(),NOW(),0,1,1,'global_metadata',0,0),
  (8,NOW(),NOW(),0,1,2,'global_metadata',0,0),
  (9,NOW(),NOW(),0,1,3,'global_metadata',0,0),
  (10,NOW(),NOW(),0,1,4,'global_metadata',0,0),
  (11,NOW(),NOW(),0,1,5,'global_metadata',0,0),
  (12,NOW(),NOW(),0,1,6,'global_metadata',0,0),
  (13,NOW(),NOW(),0,1,1,'global_plant',0,0),
  (14,NOW(),NOW(),0,1,2,'global_plant',0,0),
  (15,NOW(),NOW(),0,1,3,'global_plant',0,0),
  (16,NOW(),NOW(),0,1,4,'global_plant',0,0),
  (17,NOW(),NOW(),0,1,5,'global_plant',0,0),
  (18,NOW(),NOW(),0,1,6,'global_plant',0,0),
  (19,NOW(),NOW(),0,1,1,'global_specimen',0,0),
  (20,NOW(),NOW(),0,1,2,'global_specimen',0,0),
  (21,NOW(),NOW(),0,1,3,'global_specimen',0,0),
  (22,NOW(),NOW(),0,1,4,'global_specimen',0,0),
  (23,NOW(),NOW(),0,1,5,'global_specimen',0,0),
  (24,NOW(),NOW(),0,1,6,'global_specimen',0,0);

