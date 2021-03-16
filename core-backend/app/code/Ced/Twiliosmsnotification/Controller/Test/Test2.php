<?php
require_once  BP.'/lib/Twilio.php'; // Loads the library
 
$sid = "ACeb9f2c6d612cc1b0b1b47ff832342dc7";

$token = "69de31c30fc60e5341a9e93a59f24bac";

$http = new Services_Twilio_TinyHttp(
			'https://api.twilio.com',
			array('curlopts' => array(
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
			))
		);

$client = new Services_Twilio($sid, $token, "2010-04-01", $http);

try{
$sms = $client->account->sms_messages->create("+18162392717", "+918707835247", "This is another message for testing.", array());
echo $sms ;die('----');
}catch (Exception $e){
	echo $e->getMessage();
}