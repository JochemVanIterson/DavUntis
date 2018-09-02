<?php
class UntisCurl{
	public $BaseURL = "https://mese.webuntis.com/WebUntis/";
	private $JSESSIONID = "";
	private $schoolname = "";

	function __construct($COOKIE, $ini_array){
		if(isset($COOKIE["JSESSIONID"]))$this->JSESSIONID = $COOKIE["JSESSIONID"];
		if(isset($COOKIE["schoolname"]))$this->schoolname = $COOKIE["schoolname"];
	}

	function GetDataCurl($URL, $AppendHEADER, $POST, $RequireHeader = false){
		$ch = curl_init();
		$HEADER = array(
			'Origin:https://mese.webuntis.com',
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:55.0) Gecko/20100101 Firefox/55.0',
			'Accept-Language: nl,en-US;q=0.7,en;q=0.3',
			'Connection: keep-alive',
			'Upgrade-Insecure-Requests: 1'
		);
		$HEADER = array_merge($HEADER, $AppendHEADER);
		curl_setopt($ch, CURLOPT_URL, $this->BaseURL.$URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $HEADER);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($RequireHeader){
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);
		return array("response" => $response, "info" => $info);
	}
}
?>
