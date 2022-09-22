{*
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
*}

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='ImporterONE Cloud Connector' mod='importerone6connect'}</h3>
	<p>
		{l s='Configura in questa pagina i parametri per la connessione con ImporterONE Cloud' mod='importerone6connect'}
	</p>
	<p>
		{l s='Accedi alla pagina [1]Integrazioni CMS[/1] nel portale [2]app.importerone.it[/2] per abilitare la connessione dal tuo Prestashop' mod='importerone6connect' tags=['<strong>', '<a href="https://app.importerone.it/">']}
	</p>
	<p>
		{l s='Crea un Token indicando il dominio del tuo sito: [1]current_domain[/1]' mod='importerone6connect' sprintf=['current_domain' => $current_domain] tags=['<strong>'] }
	</p>
	<p>
		{l s='Copia il Token generato e l\'indirizzo dell\'API Endpoint che trovi in quella stessa pagina per valorizzare i dati qui sotto.' mod='importerone6connect'}<br />
	</p>
</div>

{* <div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Documentation' mod='importerone6connect'}</h3>
	<p>
		&raquo; {l s='You can get a PDF documentation to configure this module' mod='importerone6connect'} :
		<ul>
			<li><a href="#" target="_blank">{l s='English' mod='importerone6connect'}</a></li>
			<li><a href="#" target="_blank">{l s='French' mod='importerone6connect'}</a></li>
		</ul>
	</p>
</div> *}
