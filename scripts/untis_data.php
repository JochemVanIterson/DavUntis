<?php
class UntisData{
  public $JSESSIONID = "";
  public $UntisURL = "";
	private $schoolname = "";
	private $UntisCurl;

	function __construct($UntisURL, $COOKIE, $ini_array){
		$this->UntisCurl = new UntisCurl($UntisURL, $COOKIE, $ini_array);
		if(isset($COOKIE["JSESSIONID"]))$this->JSESSIONID = $COOKIE["JSESSIONID"];
		if(isset($COOKIE["schoolname"]))$this->schoolname = $COOKIE["schoolname"];
    $this->UntisURL = $UntisURL;
	}

  function PageConfig($typeID){
		$SessionIDHeader = array(
			'Host: mese.webuntis.com',
			'Referer: https://mese.webuntis.com/WebUntis/index.do;jsessionid='.$this->JSESSIONID,
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
			'X-Requested-With: XMLHttpRequest',
			'DNT:1',
			'Cookie: schoolname='.$this->schoolname.'; JSESSIONID='.$this->JSESSIONID
		);
		$CurlResponse = $this->UntisCurl->GetDataCurl("api/public/timetable/weekly/pageconfig?type=".$typeID, $SessionIDHeader, "");
		return json_decode($CurlResponse['response'], true);
	}

  public function Departments(){
    $json = $this->PageConfig(1);
    foreach($json['data']['filters'] as $filter){
      if($filter['typeLabel']=="IDC_ABTEILUNG"){
        return $filter['elements'];
      }
    }
	}

  public function Building(){
    $json = $this->PageConfig(1);
    foreach($json['data']['filters'] as $filter){
      if($filter['typeLabel']=="IDC_GEBAEUDE"){
        return $filter['elements'];
      }
    }
	}

  public function Classes(){
    $json = $this->PageConfig(1);
		return $json['data']['elements'];
	}

  public function Teachers($json){
    $json = $this->PageConfig(2);
		return $json['data']['elements'];
	}

	public function Subjects($json){
    $json = $this->PageConfig(3);
		return $json['data']['elements'];
	}

	public function Rooms($json){
    $json = $this->PageConfig(4);
		return $json['data']['elements'];
	}

  public function Students($json){
    $json = $this->PageConfig(5);
		return $json['data']['elements'];
	}

	public function Resources($json){
    $json = $this->PageConfig(6);
		return $json['data']['elements'];
	}
}
?>
