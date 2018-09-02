<?php
// require_once($ini_array['BasePath']."scripts/untis_curl.php");

class UntisLogin{
	public $JSESSIONID = "";
	private $schoolname = "";
	private $UntisCurl;

	function __construct($COOKIE, $ini_array){
		$this->UntisCurl = new UntisCurl($COOKIE, $ini_array);
		if(isset($COOKIE["JSESSIONID"]))$this->JSESSIONID = $COOKIE["JSESSIONID"];
		if(isset($COOKIE["schoolname"]))$this->schoolname = $COOKIE["schoolname"];
	}

	function GetSessionIDData($School){
		// echo "<br>".$School."<br>";
		$SessionIDHeader = array(
			'Host: mese.webuntis.com',
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:56.0) Gecko/20100101 Firefox/56.0',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: nl,en-US;q=0.7,en;q=0.3',
			'Accept-Encoding: gzip, deflate, br',
			'Referer: https://mese.webuntis.com/WebUntis/login.do?error=nomandant',
			'Content-Type: application/x-www-form-urlencoded',
			'Connection: keep-alive',
			'Upgrade-Insecure-Requests: 1',
			'Pragma: no-cache',
			'Cache-Control: no-cache'
		);
		$CurlResponse = $this->UntisCurl->GetDataCurl("j_spring_security_check", $SessionIDHeader, "login_url=%2Flogin.do&school=".$School, true);
		preg_match('/Set-Cookie: JSESSIONID=(.*?); /', $CurlResponse['response'], $JSESSIONID_match);
		$this->JSESSIONID = $JSESSIONID_match[1];
		try {
			setrawcookie("JSESSIONID", $this->JSESSIONID, time()+3600);
		} catch (Exception $e) {
		}

		preg_match('/Set-Cookie: schoolname=(.*?); /', $CurlResponse['response'], $schoolname_match);
		$this->schoolname = $schoolname_match[1];

		global $JSESSIONID;
		global $schoolname;

		$JSESSIONID = $this->JSESSIONID;
		$schoolname = $this->schoolname;

		try {
			setrawcookie("schoolname", $this->schoolname, time()+3600);
		} catch (Exception $e) {
			//echo 'Caught exception: ',  $e->getMessage(), "\n";
		}

		return array("JSESSIONID" => $this->JSESSIONID, "schoolname" => $this->schoolname);
	}

	function Login($School, $j_username, $j_password) {
		//$SessionIDHeader = array(
		//	'POST /WebUntis/j_spring_security_check HTTP/1.1',
		//	'Host: mese.webuntis.com',
		//	'Content-Length: 58',
		//	'Pragma: no-cache',
		//	'Cache-Control: no-cache',
		//	'Accept: application/json',
		//	'Origin: https://mese.webuntis.com',
		//	'X-Requested-With: XMLHttpRequest',
		//	'Content-Type: application/x-www-form-urlencoded',
		//	'Accept-Encoding: gzip, deflate, br',
		//	'Referer: https://mese.webuntis.com/WebUntis/index.do;jsessionid='.$this->JSESSIONID,
		//	'Cookie: JSESSIONID='.$this->JSESSIONID.'; schoolname='.$this->schoolname
		//);
		$SessionIDHeader = array(
			'Accept:application/json',
			'Accept-Encoding:gzip, deflate, br',
			'Accept-Language:nl-NL,nl;q=0.8,en-US;q=0.6,en;q=0.4,fr;q=0.2',
			'Cache-Control:no-cache',
			'Connection:keep-alive',
			'Cookie: JSESSIONID='.$this->JSESSIONID.'; schoolname='.$this->schoolname,
			'DNT:1',
			'Host:mese.webuntis.com',
			'Origin:https://mese.webuntis.com',
			'Pragma:no-cache',
			'Referer:https://mese.webuntis.com/WebUntis/index.do;jsessionid='.$this->JSESSIONID,
			'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
			'X-Requested-With:XMLHttpRequest'
		);
		$Post = "school=".$School."&j_username=".$j_username."&j_password=".$j_password."&token=";

		$CurlResponse = $this->UntisCurl->GetDataCurl("j_spring_security_check", $SessionIDHeader, $Post);
		//var_dump($CurlResponse);

		$Success = $CurlResponse['response'];
		//
		($Success);
		if($CurlResponse['response'] == false){
			return array("status"=>"failed", "message"=>"Lw");
		} else if(json_decode($Success, true)["state"]=="SUCCESS"){
			return array("status"=>"success", "data"=>array("schoolname"=>$this->schoolname, "JSESSIONID"=>$this->JSESSIONID));
		}
	}
}
?>
