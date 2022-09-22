<?php 

//echo("OK...");
//die();

include(dirname(__FILE__) . '/../../config/config.inc.php');
//include(dirname(__FILE__) . '/../../init.php');

if (!Module::isEnabled("ps_connect_io6")) {
    die("Modulo non attivo ps_connect_io6");
}

$ps_connect_io6 = Module::getInstanceByName("ps_connect_io6");
if(!$ps_connect_io6->checkServerRequirementsCron() && Tools::getValue('accettoAvvisoRequisiti',0) == 0){
    die("Errore all'avvio della sincronizzazione. Controlla i requisiti minimi o accetta di proseguire ignorandoli modificando i parametri della chiamata al cron.");
}

if (Tools::isPHPCLI()) {
    //disabilito eventualmente i notice
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

$currentPage = 1;
$totalPages = 1;

$totalRows = -1;

$callUrl = isset($argv) && count($argv) > 1 ? $argv[1] : '';


if($callUrl != '') {
  $contents = '';

  while ($currentPage <= $totalPages) {
    try { 
			$callUrl .= '&page=' . $currentPage;
			$results = get_web_page($callUrl);    
			
			$totalPages = $results['pages'];
			
			echo sprintf('Totale prodotti: %s. Pagine: %s di %s'. PHP_EOL, $results['elementsFounds'], $currentPage , $totalPages);
			$currentPage++;
			
		}
		catch(Exception $e) {
			$totalPages=-1;
            echo($e->getMessage() . PHP_EOL);
		}
  } 
	die('Update from ImporterONE Cloud terminata');
}
else
  die('Parametro Url plugin non impostato!');


function get_web_page($url) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "ImporterONE", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 300,    // time-out on connect
        CURLOPT_TIMEOUT        => 300,    // time-out on response
        CURLOPT_URL, $url 
    );

    $ch = curl_init($url);

    curl_setopt_array($ch, $options);

    // $output contains the output string
    $content = curl_exec($ch);
    if (curl_errno($ch))
        throw new Exception("Errore in esecuzione url: " . $url . " - Error: " . curl_error($ch), curl_errno($ch));
    else
        $output =	json_decode($content, true);

    curl_close($ch);

    return $output;
}
