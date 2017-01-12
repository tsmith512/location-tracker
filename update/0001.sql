/* Drop an old table backup that is not needed */
DROP TABLE `location_history_old`;

/* Make a table to store some application information */
CREATE TABLE `variables` (
	`id` INT(20) NOT NULL AUTO_INCREMENT,
	`key` VARCHAR(255) NOT NULL,
	`value` TEXT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

/* I'll be doing lots of database work, keep a tally of the current update */
INSERT INTO `maps`.`variables` (`key`, `value`) VALUES ('db_update', '0001');
