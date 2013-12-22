ALTER TABLE `{prefix}comment` ADD `comment_rating` INT( 12 ) NOT NULL DEFAULT '0';
ALTER TABLE `{prefix}identifier` ADD `identifier_rating_value` INT( 12 ) NOT NULL DEFAULT '0';
ALTER TABLE `{prefix}identifier` ADD `identifier_rating_number` INT( 12 ) NOT NULL DEFAULT '0';