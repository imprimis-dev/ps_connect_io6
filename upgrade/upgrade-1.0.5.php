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
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_5($module)
{
    $module->uninstallOverrides();
    $module->installOverrides();

    $result = Db::getInstance()->execute(
        'ALTER TABLE ' . _DB_PREFIX_ . 'importerone6connect_products
            ADD COLUMN manage_categories tinyint unsigned NOT NULL DEFAULT 2 AFTER manage_description ');

    if (!$module->isRegisteredInHook('displayProductFooter'))
        $result &= $module->registerHook('displayProductFooter');
    if (!$module->isRegisteredInHook('actionFrontControllerSetMedia'))
        $result &= $module->registerHook('actionFrontControllerSetMedia');
    

    return $result;
}
