/* I'm shifting how I want to record times over to the client and have
   a way for entries to be posted after they are recorded (like, being
   outside cell service). To make this all a bit easier, I'm switching
   over to Unix timestamps, at least for a little while. */

/* Add the `time` column */
ALTER TABLE `location_history`
      ADD COLUMN `time` INT NOT NULL COMMENT 'Unix timestamp' AFTER `timestamp`;

/* Update old rows */
UPDATE `location_history` SET time = UNIX_TIMESTAMP(timestamp);

/* Now declare time as a unique index. I won't be in two places at once
   and this will help make sure that duplicate rows aren't entered. */
ALTER TABLE `location_history`
      ADD UNIQUE INDEX `time` (`time`);
