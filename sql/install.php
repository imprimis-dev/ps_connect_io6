<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'importerone6connect_products` (
    `id_product` int UNSIGNED NOT NULL,
    `io6_id_product` int UNSIGNED NULL,
    `sync_status` int DEFAULT NULL,
    `sync_message` text,
    `lastsync` TIMESTAMP NULL,
    `is_synced` tinyint NOT NULL DEFAULT 0,
    `exclude_sync` tinyint unsigned NOT NULL,
    `manage_title` tinyint unsigned NOT NULL,
    `manage_shortdescription` tinyint unsigned NOT NULL,
    `manage_description` tinyint unsigned NOT NULL,
    `manage_categories` tinyint unsigned NOT NULL,
    `manage_prices` tinyint unsigned NOT NULL,
    `manage_images` tinyint unsigned NOT NULL,
    `manage_features` tinyint unsigned NOT NULL,
    `manage_htmlfeatures` tinyint unsigned NOT NULL,
    `manage_tax_rule` tinyint unsigned NOT NULL,
    `htmlfeatures` mediumtext,
    `lastupdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (`id_product`),
    UNIQUE INDEX `io6_id_product` (`io6_id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'importerone6connect_categories` (
    `id_category` INT UNSIGNED NOT NULL,
    `io6_category_code` VARCHAR (20) NOT NULL,
    `lastupdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (`id_category`),
    UNIQUE KEY `io6_category_code` (`io6_category_code`)
  ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'importerone6connect_manufacturers` (
    `id_manufacturer` INT UNSIGNED NOT NULL,
    `io6_brand_code` VARCHAR (20) NOT NULL,
    `logo` VARCHAR(500) NOT NULL DEFAULT \'\',
    `lastupdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (`id_manufacturer`),
    UNIQUE KEY `io6_brand_code` (`io6_brand_code`)
  ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'importerone6connect_suppliers` (
    `id_supplier` INT UNSIGNED NOT NULL,
    `io6_id_supplier` INT UNSIGNED NOT NULL,
    `lastupdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (`id_supplier`),
    UNIQUE KEY `io6_id_supplier` (`io6_id_supplier`)
  ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'importerone6connect_images` ( 
  `id_product` INT(10) UNSIGNED NOT NULL,
  `image_uri` VARCHAR(255) NOT NULL,
  `id_image` INT(10) UNSIGNED NOT NULL,
  `orderindex` int(4) NOT NULL DEFAULT 0,
  `lastupdate` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id_product`, `image_uri`)
  ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8; ';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'importerone6connect_specific_price` ( 
  `id_product` INT(10) UNSIGNED NOT NULL,
  `id_specific_price` INT(10) UNSIGNED NOT NULL,
  `lastupdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`id_product`, `id_specific_price`)
  ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8; ';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
