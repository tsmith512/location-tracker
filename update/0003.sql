/* Crete a place to hold some trip ranges */
CREATE TABLE `trips` (
       `id` INT NOT NULL AUTO_INCREMENT,
       `machine_name` VARCHAR(50) NOT NULL DEFAULT '0',
       `starttime` INT NOT NULL DEFAULT '0',
       `endtime` INT NOT NULL DEFAULT '0',
       `label` TEXT NULL,
       PRIMARY KEY (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

INSERT INTO `maps`.`trips` (`machine_name`, `starttime`, `endtime`, `label`)
VALUES ('pacific_coast_roadtrip', 1440783004, 1442329222, 'Pacific Coast Roadtrip'), ('scottish_roadtrip', 1460910603, 1461357013, 'Scottish Roadtrip');
