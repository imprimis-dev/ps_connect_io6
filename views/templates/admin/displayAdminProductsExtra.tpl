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

<br>
<div class="panel product-tab bootstrap" id="importerone6connect"> 
{if isset($id_product) }
    <div class="row">
        <div class="col-lg-12">
            <h2>{l s='Impostazioni di ImporterONE Cloud Connector specifiche per il prodotto' mod='importerone6connect'}</h2>
        </div>
        <div class="col-lg-12">
            <div class="alert alert-info">
                <p>{l s='In questa pagina è possibile personalizzare i Flag utilizzati dalla procedura di sincronizzazione di ImporterONE Cloud Connector solo per il prodotto corrente.' mod='importerone6connect'}</p>
            </div>
        </div>
        <div class="col-lg-12">
          <div class="row form-group">
            <div class="col-md-4">
              <div class="checkbox">                          
                <label><input type="checkbox" id="importerone6connect_exclude_sync" name="importerone6connect[importerone6connect_exclude_sync]" value="1" {if !isset($importerone6connect_exclude_sync) || $importerone6connect_exclude_sync} checked="checked"{/if} >
                    {l s='Escludi sincro da ImporterONE Cloud' mod='importerone6connect'}</label>
              </div>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna il titolo' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_title" name="importerone6connect[importerone6connect_manage_title]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_TITLE']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_title == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_title == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna il riepilogo' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_shortdescription" name="importerone6connect[importerone6connect_manage_shortdescription]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_SHORTDESCRIPTION']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_shortdescription == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_shortdescription == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna la descrizione' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_description" name="importerone6connect[importerone6connect_manage_description]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_DESCRIPTION']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_description == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_description == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna la descrizione' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_categories" name="importerone6connect[importerone6connect_manage_categories]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_CATEGORIES']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_categories == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_categories == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna il prezzo' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_prices" name="importerone6connect[importerone6connect_manage_prices]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_PRICES']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_prices == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_prices == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna le immagini' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_images" name="importerone6connect[importerone6connect_manage_images]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_IMAGES']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_images == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_images == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna le caratteristiche' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_features" name="importerone6connect[importerone6connect_manage_features]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_FEATURES']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_features == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_features == '1'} selected="selected"{/if} >Si</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Aggiorna la scheda HTML' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_htmlfeatures" name="importerone6connect[importerone6connect_manage_htmlfeatures]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_HTMLFEATURES']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_htmlfeatures == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_htmlfeatures == '1'} selected="selected"{/if} >Si</option>
             </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label class="form-control-label">{l s='Applica aliquota IVA di default' mod='importerone6connect'}</label>
              <select id="importerone6connect_manage_tax_rule" name="importerone6connect[importerone6connect_manage_tax_rule]" data-toggle="select2" data-minimumresultsforsearch="7" class="custom-select select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                <option value="2" >Default [{if $default_values['IMPORTERONE6CONNECT_MANAGE_TAX_RULE_DEFAULT']}Si{else}No{/if}]</option>
                <option value="0" {if $importerone6connect_manage_tax_rule == '0'} selected="selected"{/if} >No</option>
                <option value="1" {if $importerone6connect_manage_tax_rule == '1'} selected="selected"{/if} >Si</option>
             </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-12">
              <label class="form-control-label">{l s='Scheda HTML' mod='importerone6connect'}</label>
              <div style="border: 1px solid #bbcdd2;">
                {Tools::htmlentitiesDecodeUTF8($importerone6connect_htmlfeatures) }
              </div>
            </div>
          </div>
        </div>
    </div>
{else}
  <div class="alert alert-warning">{l s='Warning: To add additional data to your product you have to Save it First.' mod='importerone6connect'}</div>
{/if} 
</div>




