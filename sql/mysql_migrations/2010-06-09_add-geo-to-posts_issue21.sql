ALTER TABLE `tt_posts` ADD `location` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `source` ;
ALTER TABLE `tt_posts` ADD `place` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `location` ;
ALTER TABLE `tt_posts` ADD `geo` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `place` ;
ALTER TABLE `tt_posts` ADD `flag` INT( 1 ) NOT NULL DEFAULT '0' AFTER `network` ;