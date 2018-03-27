ALTER TABLE `erp_permissions` ADD `reports-daily_purchases` TINYINT(1) NULL DEFAULT '0',
    ADD `reports-monthly_purchases` TINYINT(1) NULL DEFAULT '0' ;
ALTER TABLE `erp_expenses` ADD `warehouse_id` INT NULL ;
ALTER TABLE `erp_companies` ADD `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ;
ALTER TABLE erp_calendar DROP PRIMARY KEY;
ALTER TABLE `erp_calendar` CHANGE `date` `start` DATETIME NOT NULL,
    CHANGE `data` `title` VARCHAR(55) NOT NULL,
    ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ADD `end` DATETIME NULL, 
    ADD `description` VARCHAR(255) NULL, 
    ADD `color` VARCHAR(7) NOT NULL;
ALTER TABLE `erp_companies` ADD `credit_limited` DECIMAL(25,2) NULL ;
ALTER TABLE `erp_customer_groups` ADD `makeup_cost` tinyint(3) NOT NULL DEFAULT '0' ;
ALTER TABLE `erp_gl_sections` ADD `sectionname_kh` TEXT NULL ;
ALTER TABLE `erp_purchase_items` ADD `container_id` INT NOT NULL AFTER `supplier_id`;

ALTER TABLE `erp_adjustments`
ADD `cost` DECIMAL (19, 4) NULL DEFAULT '0',
ADD `total_cost` DECIMAL (19, 4) NULL DEFAULT '0',
ADD `biller_id` INT (11) NULL DEFAULT '0';

ALTER TABLE `erp_suspended`
ADD `note` VARCHAR(255) NULL,
ADD `customer_id` INT (11) NULL DEFAULT '0',
ADD `inactive` TINYINT (1) NULL DEFAULT '0';

ALTER TABLE `erp_deliveries`
ADD `delivery_id` INT (11) NULL DEFAULT '0';

ALTER TABLE `erp_purchases`
ADD `reference_no_tax` VARCHAR(100) NULL,
ADD `tax_status` VARCHAR(255) NULL,
ADD `opening_ap` TINYINT (1) NULL DEFAULT '0';

ALTER TABLE `erp_sales`
ADD `opening_ar` TINYINT (1) NULL DEFAULT '0',
ADD `other_cur_paid1` DECIMAL (25, 4) NULL DEFAULT '0',
ADD `other_cur_paid_rate1` DECIMAL (25, 4) NULL DEFAULT '0';

ALTER TABLE `erp_suspended_items`
ADD `status` TINYINT (1) NULL DEFAULT '0';

ALTER TABLE `erp_payments`
ADD `extra_paid` DECIMAL (25, 4) NULL DEFAULT '0',
ADD `return_deposit_id` INT (11) NULL,
ADD `deposit_quote_id` INT (11) NULL;

ALTER TABLE `erp_gl_trans`
ADD `gov_tax` TINYINT (1) NULL DEFAULT '0',
ADD `reference_no_tax` VARCHAR(55) NULL,
ADD `people_id` INT (11) NULL;

ALTER TABLE `erp_companies`
 ADD `business_activity` varchar(255) DEFAULT NULL,
 ADD `group` varchar(255) DEFAULT NULL,
 ADD `village` varchar(255) DEFAULT NULL,
 ADD `street` varchar(255) DEFAULT NULL,
 ADD `sangkat` varchar(255) DEFAULT NULL,
 ADD `district` varchar(255) DEFAULT NULL;
 
ALTER TABLE `erp_users`
 ADD `pack_id` varchar(50) DEFAULT NULL;
 
ALTER TABLE `erp_return_tax_front`
 ADD `filed_in_kh` varchar(100) DEFAULT NULL,
 ADD `filed_in_en` varchar(100) DEFAULT NULL;
 
 ALTER TABLE `erp_return_value_added_tax`
  ADD `field_in_kh` varchar(100) DEFAULT NULL,
  ADD `field_in_en` varchar(100) DEFAULT NULL;

 ALTER TABLE `erp_purchase_tax`
  CHANGE `warehouse_id` `group_id` varchar(100),
  ADD `description` varchar(255) DEFAULT NULL,
  ADD `vatin` varchar(100) DEFAULT NULL,
  ADD `qty` double(25,8) DEFAULT NULL,
  ADD `non_tax_pur` double(25,4) DEFAULT NULL,
  ADD `tax_value` double(25,4) DEFAULT NULL,
  ADD `vat` double(25,4) DEFAULT NULL;
  
 ALTER TABLE `erp_sale_tax`
  CHANGE `warehouse_id` `group_id` varchar(100),
  ADD `vatin` varchar(100) DEFAULT NULL,
  ADD `description` varchar(255) DEFAULT NULL,
  ADD `qty` double(8,4) DEFAULT NULL,
  ADD `non_tax_sale` double(8,4) DEFAULT NULL,
  ADD `value_export` double(8,4) DEFAULT NULL,
  ADD `tax_value` double(8,4) DEFAULT NULL,
  ADD `vat` double(8,4) DEFAULT NULL;


ALTER TABLE `erp_settings` ADD `purchase_serial` TINYINT(4) NULL DEFAULT '0' ;					0	0

