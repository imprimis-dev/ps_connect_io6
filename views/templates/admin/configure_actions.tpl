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

<script type="text/javascript">

$(document).ready(function () {
    $cancel = false;
    
	$("#io6-exec-sync").unbind();
	$("#io6-exec-cancel-sync").on('click', function() {
		$cancel = true;
		$('#io6-exec-sync-info').html("Annullamento in corso...");
	});
	
	
	$("#io6-exec-sync").unbind();
	$("#io6-exec-sync").on('click', async function() {
		$cancel = false;
		$(this).prop('disabled', true);
		$("#io6-exec-cancel-sync").removeClass("display-none");
		var resumeSync = $("#io6-resume-sync").is(':checked') ? 1 : 0;
		var fastSync = $("#io6-fast-sync").is(':checked') ? 1 : 0;
	
		var currentPage = 1;
		var totalPages = 1;
		
		
		$('#io6-exec-sync-info').html('Inizio sincronizzazione...');			
		$('#io6-exec-sync-info').show();

		$('#io6-exec-sync-status').hide();
		$('#io6-exec-sync-status').html('');

		
		while (currentPage <= totalPages && !$cancel) {
			await $.ajax({
				method: "get",
				async: true,
				dataType: 'json',
				//url: window.location.protocol + '//' + window.location.hostname + '/wp-admin/admin-ajax.php?action=io6-sync&page=' + currentPage,
				url: '{$executeUrl}&page=' + currentPage + '&fast=' + fastSync + '&resume=' + resumeSync,
				
				success: function (data) {
					totalPages = data.pages;
					
					$('#io6-exec-sync-info').html('Totale prodotti: ' + data.elementsFounds + ". Pagine: " + currentPage + " di " + data.pages);
					$('#io6-exec-sync-status').show();
					data.products.forEach(element => {
						$('#io6-exec-sync-status').prepend("<p class='status-message " + element.status + "' >Prodotto: " + element.io6_id + " - EAN: " + element.ean + " - PARTNUMBER: " + element.partnumber + " - Status: " + element.status_message +  "</p>");
					});
				},
				error: function (error) {
					$('#io6-exec-sync-info').html(error.statusText + "<br/>" + error.responseText);							
				},
				complete: function() {
				}
			});
			currentPage++;
		}

		if($cancel)
			$('#io6-exec-sync-info').append('<br/>Sincronizzazione interrotta.');
		else
			$('#io6-exec-sync-info').append('<br/>Sincronizzazione terminata.');			

		$(this).prop('disabled', false);
		$("#io6-exec-cancel-sync").addClass("display-none");
			      
    });    

	$("#io6-test-api").unbind();
	$("#io6-test-api").on('click', async function(e) {
		e.preventDefault();
		$cancel = false;
		var href = e.target.href;
		$(this).prop('disabled', true);

			await $.ajax({
				method: "get",
				async: true,
				dataType: 'json',
				url: href,
				
				success: function (data) {

					if(data.response.catalogs.passed && data.response.products.passed) {
						$('#fieldset_0 .form-wrapper').append('<div class="module_confirmation conf confirm alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>Connessione ImporterONE avvenuta correttamente.</div>');
					}
					else {
						$('#fieldset_0 .form-wrapper').append('<div class="module_error alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>C\'è stato un problema di connessione con ImporterONE.<br/>Controllare i parametri immessi o contattare il supporto tecnico.</div>');
					}
				},
				error: function (error) {
					console.log("ERROR " + error.toString());		
				},
				complete: function() {
				}
			});
    });    

	
  });

</script>

<div class="panel">
	<h3><i class="icon icon-cogs"></i> {l s='Actions' mod='importerone6connect'}</h3>
	<p>{l s='Avvio della procedura di sincronizzazione del catalogo da ImporterONE Cloud' mod='importerone6connect'}</p>
	

	<div class="form-group">
		<br/>
		<label>
			<input type="checkbox" class="" name="io6-resume-sync" id="io6-resume-sync" value="1"/>
			{l s='Riprendere sincronizzazione precedente' mod='importerone6connect'}
			<br />
			<small>({l s='Se la precedente sincronizzazione non è stata completata, i prodotti già importati verranno ignorati durante questa sincronizzazione' mod='importerone6connect'})</small>
		</label>
		<br/>
		<label>
			<input type="checkbox" class="" name="io6-fast-sync" id="io6-fast-sync" value="1"/>
			{l s='Esegui sincronizzazione veloce' mod='importerone6connect'}
			<br />
			<small>({l s='Verranno aggiornati solamente prezzo e quantità dei prodotti esistenti, i prodotti nuovi verranno scartati' mod='importerone6connect'})</small>
		</label>
		<br/>
		<button class="btn btn-default" id="io6-exec-sync">
			{l s='Aggiorna catalogo da ImporterONE ...' mod='importerone6connect'}
		</button>		
		<button class="btn btn-cancel display-none" id="io6-exec-cancel-sync" >
			{l s='Annulla' mod='importerone6connect'}
		</button>		
	</div>
	<div class="wrap">
		<div id="io6-exec-sync-info" class="sync-info"></div>
		<div id="io6-exec-sync-status" class="sync-status"></div>	
	</div>
	<div class="form-group">
		<br/>		
		<strong>{l s='Comando CRON sincronizzazione normale' mod='importerone6connect'}</strong>
		<div class="" style="border: none;border-left: 3px solid #fcc94f;padding: 10px;position: relative;background-color: #fff3d7;color: #d2a63c;">{$cronCommand}</div>
		<p class="help-block">{l s='Puoi configurare un CRON per l\'esecuzione del comando PHP con i parametri sopra indicati per eseguire l\'aggiornamento automatico del catalogo' mod='importerone6connect'}</p>
		<br/>		
		<strong>{l s='Comando CRON sincronizzazione veloce' mod='importerone6connect'}</strong>
		<div class="" style="border: none;border-left: 3px solid #fcc94f;padding: 10px;position: relative;background-color: #fff3d7;color: #d2a63c;">{$cronCommandFast}</div>
		<p class="help-block">{l s='Puoi configurare un CRON Fast per l\'esecuzione del comando PHP con i parametri sopra indicati per eseguire l\'aggiornamento automatico del catalogo, verranno aggiornati solamente prezzo e quantità dei prodotti esistenti, i prodotti nuovi verranno scartati' mod='importerone6connect'}</p>
	</div>
</div>

{* <div class="modal" tabindex="-1" id="importerone6connectModalExecute" data-backdrop="static" data-keyboard="false" >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{l s="Aggiorna catalogo da ImporterONE" mod='importerone6connect'}</h5>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-close" data-dismiss="modal">{l s="Chiudi" mod='importerone6connect'}</button>
        <button type="button" class="btn btn-secondary btn-cancel" onclick="cancelExecution()>{l s="Annulla" mod='importerone6connect'}</button>
      </div>
    </div>
  </div>
</div> *}