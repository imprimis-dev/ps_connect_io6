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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function () {

    $("#io6-test-api").unbind();
	$("#io6-test-api").on('click', async function(e) {
		e.preventDefault();
		var endPoint = $('#IMPORTERONE6CONNECT_API_ENDPOINT').val();
		var token = $('#IMPORTERONE6CONNECT_API_TOKEN').val();
		var href = e.target.href;

		if(endPoint == "" || token == "") {
			alert("Parametri Connessione ImporterONE non impostati.");
			return;
		}

		$('#api-settings_form .form-wrapper > .module_confirmation').remove();

		await $.ajax({
			method: "get",
			async: true,
			dataType: 'json',
			url: href + "&ep=" + endPoint + "&t=" + token,
			success: function (data) {
				$('#api-settings_form .form-wrapper').append('<div class="module_confirmation alert alert-' + (data.response.passed ? data.response.iswarning ? 'warning' : 'success' : 'danger') + '"><h4 style="margin-bottom:0;">'+ data.response.message + '</h4></div>');
			},
			error: function (error) {
				console.log("ERROR " + error.toString());		
			},
			complete: function() {
			}
		});
    });    

});
