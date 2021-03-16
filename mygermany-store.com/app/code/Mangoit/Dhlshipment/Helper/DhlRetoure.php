<?php
namespace Mangoit\Dhlshipment\Helper;

/**
* DO NOT DELETE
* User: Sebastian Viereck
* www.sebastianviereck.de
* Date: 10.06.13
*/

class DhlRetoure
{
	protected $trackingId = null;
	private $username = "webmygermany";
	private $password = "myGermAnyWEB123#_";
	private $portalId = "mygermany";
	private $deliveryName = "myGermanyStore_V3";
	private $end_point = "https://amsel.dpwn.net/abholportal/gw/lp/SoapConnector";

	public function getRetourePdf($surname, $familyname, $street, $streetNumber, $zip, $city)
	{
		$xmlRequest = $this->getRequestXml($surname, $familyname, $street, $streetNumber, $zip, $city);
		$response = $this->curlSoapRequest($xmlRequest);

		if($response){
			$labelData = $this->getPdfFromResponse($response);
			$pdf = $labelData['pdf'];
			$trackingId = $labelData['trackingId'];
		}

	   

		/*return $pdf;*/
		return  ['pdf'=> $pdf, 'trackingId'=> $trackingId];;
	}
	
	public function displayPdf($pdf){
		header("Content-type: application/pdf");
		echo $pdf;
	}
	
	public function getPdfContent($pdf){
		header("Content-type: application/pdf");
		return $pdf;
	}

	private function getRequestXml($surname, $familyname, $street, $streetNumber, $zip, $city)
	{
		$request ="<?xml version='1.0' encoding='UTF-8' ?>
		<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:var='https://amsel.dpwn.net/abholportal/gw/lp/schema/1.0/var3bl'>
		<soapenv:Header>
		<wsse:Security soapenv:mustUnderstand='1' xmlns:wsse='http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'>
		<wsse:UsernameToken>
		<wsse:Username>".$this->username."</wsse:Username>
		<wsse:Password Type='http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText'>
		".$this->password."
		</wsse:Password>
		</wsse:UsernameToken>
		</wsse:Security>
		</soapenv:Header>
		<soapenv:Body>
		<var:BookLabelRequest
		portalId='".$this->portalId."'
		deliveryName='".$this->deliveryName."'
		shipmentReference='Shipment Reference'
		customerReference='Customer Reference'
		labelFormat='PDF'
		senderName1='$surname'
		senderName2='$familyname'
		senderCareOfName='CareofName'
		senderContactPhone=''
		senderStreet='$street'
		senderStreetNumber='$streetNumber'
		senderBoxNumber=''
		senderPostalCode='$zip'
		senderCity='$city' />
		</soapenv:Body>
		</soapenv:Envelope>";
		//echo $request;
		
		return $request;
	}

	/**
	* @param $soap_request
	* @return mixed
	*/
	private function curlSoapRequest($xmlRequest)
	{
		$header = array(
		"Content-type: text/xml;charset=\"utf-8\"",
		"Accept: text/xml",
		"Cache-Control: no-cache",
		"Pragma: no-cache",
		"Content-length: " . strlen($xmlRequest),
		);

		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $this->end_point);
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST, true);
		curl_setopt($soap_do, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
		$response = curl_exec($soap_do);

		if (!$response) {
		$err = 'Curl error: ' . curl_error($soap_do);
		}
		else {
		// var_dump(htmlentities($response));
		}
		curl_close($soap_do);
		return $response;
	}

	/**
	* @param $response
	* @return string
	*/
	private function getPdfFromResponse($response)
	{
		/* For Tracking ID */
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dhl_lbl.log');
	    $logger = new \Zend\Log\Logger();
	    $logger->addWriter($writer);
	    $logger->info('##### getPdfFromResponse ######');

	    $xmlparser = xml_parser_create();
	    xml_parse_into_struct($xmlparser,$response,$values);
		xml_parser_free($xmlparser);
		$logger->info($values[4]);
		$logger->info($values[4]['attributes']['IDC']);
		$trackingId = $values[4]['attributes']['IDC'];
		$this->trackingId = $trackingId;

		$logger->info("#####################################");
		/* For Tracking ID Ends*/

		$xml = simplexml_load_string($response);
		$ns = $xml->getNamespaces(true);
		/*print_r($ns);*/
		$soap = $xml->children($ns['env']);
	   
		$pdf = $soap->Body->children($ns['var3bl'])->BookLabelResponse->label;
		//print_r($pdf);
		$pdf = base64_decode($pdf);
		return ['pdf'=> $pdf, 'trackingId'=> $trackingId];
	}
}
