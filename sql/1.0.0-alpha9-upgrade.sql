ALTER TABLE `admin_group` ADD `is_deleted` TINYINT(1) NOT NULl DEFAULT '0' ;

ALTER TABLE `cms_cat` ADD `is_deleted` TINYINT(1) NOT NULl DEFAULT '0' ;
ALTER TABLE `cms_block_group` ADD `is_deleted` TINYINT(1) NOT NULl DEFAULT '0' ;