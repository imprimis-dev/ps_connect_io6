{*
* 2007-2021 PrestaShop
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
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* <div class="panel"> *}
    <p>{l s='Requisti minimi non soddisfatti' mod='importerone6connect'}</p>
    <p>{l s='Continuando con l\'esecuzione del plugin o del cron le procedure potrebbero non funzionare correttamente e si accetta di proseguire a proprio rischio.' mod='importerone6connect'}</p>

    <table class="server-requirements-table">
        <thead>
            <tr>
                <th></th>
                <th>{l s="Richiesto" mod='importerone6connect'}</th>
                <th>{l s="Attuale" mod='importerone6connect'}</th>
            </tr>
        </thead>
        <tbody>
        {if !$serverRequirements.max_execution_time.passed}
            <tr>
                <td>{l s="MAX EXECUTION TIME" mod='importerone6connect'}</td>
                <td>{$serverRequirements.max_execution_time.required}</td>
                <td>{$serverRequirements.max_execution_time.current}</td>
            </tr>
        {/if}
        {if !$serverRequirements.memory_limit.passed}
            <tr>
                <td>{l s="MEMORY LIMIT" mod='importerone6connect'}</td>
                <td>{$serverRequirements.memory_limit.required}</td>
                <td>{$serverRequirements.memory_limit.current}</td>
            </tr>
        {/if}
        {if !$serverRequirements.php_version.passed}
            <tr>
                <td>{l s="PHP VERSION" mod='importerone6connect'}</td>
                <td>{$serverRequirements.php_version.required}</td>
                <td>{$serverRequirements.php_version.current}</td>
            </tr>
        {/if}
        {if !$serverRequirements.ps_version.passed}
            <tr>
                <td>{l s="PS VERSION" mod='importerone6connect'}</td>
                <td>{$serverRequirements.ps_version.required}</td>
                <td>{$serverRequirements.ps_version.current}</td>
            </tr>
        {/if}
        </tbody>
    </table>
{* </div> *}