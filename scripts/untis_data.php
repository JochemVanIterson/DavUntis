<?php
class UntisData{
  public $JSESSIONID = "";
  public $UntisURL = "";
	private $schoolname = "";
	private $UntisCurl;

	function __construct($UntisURL, $COOKIE, $ini_array){
		$this->UntisCurl = new UntisCurl($UntisURL, $COOKIE, $ini_array);
    $this->UntisURL = $UntisURL;
		if(isset($COOKIE["JSESSIONID"]))$this->JSESSIONID = $COOKIE["JSESSIONID"];
		if(isset($COOKIE["schoolname"]))$this->schoolname = $COOKIE["schoolname"];
	}

  function PageConfig($typeID){
    $SessionIDHeader = array(
			'Host: '.$this->UntisURL,
			'Referer: https://'.$this->UntisURL.'/WebUntis/index.do;jsessionid='.$this->JSESSIONID,
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
			'X-Requested-With: XMLHttpRequest',
			'DNT:1',
			'Cookie: schoolname='.$this->schoolname.'; JSESSIONID='.$this->JSESSIONID
		);
		$CurlResponse = $this->UntisCurl->GetDataCurl("/WebUntis/api/public/timetable/weekly/pageconfig?type=".$typeID, $SessionIDHeader, "");
		return json_decode($CurlResponse['response'], true);
	}

  function TTData($ClassID, $date){
	   $SessionIDHeader = array(
			'Host: '.$this->UntisURL,
			'Referer: https://'.$this->UntisURL.'/WebUntis/index.do;jsessionid='.$this->JSESSIONID,
			'Accept: application/json',
			'X-Requested-With: XMLHttpRequest',
			'DNT:1',
			'Cookie: schoolname='.$this->schoolname.'; JSESSIONID='.$this->JSESSIONID
		);

		$CurlResponse = $this->UntisCurl->GetDataCurl("/WebUntis/api/public/timetable/weekly/data?elementType=1&elementId=".$ClassID."&date=".$date, $SessionIDHeader, "");
		return json_decode($CurlResponse['response'], true);
	}

  function jsonDepartmentService($UntisRetreiver, $SQL){
    $ServerDepartments = $this->Departments();
    $responseArray['departments'] = $UntisRetreiver->insertDepartments($ServerDepartments);

    $allowed_departments = array();
    $dis_departments = json_decode($SQL->getSetting('dis_departments'));
    $departments = $UntisRetreiver->getDepartmentsSQL();
    foreach ($dis_departments as $key => $dis_department) {
      unset($departments[$dis_department]);
    }
    foreach ($departments as $key => $department) {
      array_push($allowed_departments, intval($department['id']));
    }
    $data = array("id"=>2, "method"=>"setGlobalDepartment", "params"=>$allowed_departments, "jsonrpc"=>"2.0");
		$SessionIDHeader = array(
			'Host: '.$this->UntisURL,
			'Referer: https://'.$this->UntisURL.'/WebUntis/index.do;jsessionid='.$this->JSESSIONID,
			'Accept: application/json',
			'Content-Type: application/json',
      'Content-Length: ' . strlen(json_encode($data)),
			'X-Requested-With: XMLHttpRequest',
			'DNT:1',
			'Cookie: schoolname='.$this->schoolname.'; JSESSIONID='.$this->JSESSIONID
		);
		$CurlResponse = $this->UntisCurl->GetDataCurl("/WebUntis/jsonrpc_web/jsonDepartmentService", $SessionIDHeader, json_encode($data));
		return json_decode($CurlResponse['response'], true);
	}

  public function Periods($ClassID, $date){
    $json = $this->TTData($ClassID, $date);
		return $json['data']['result']['data']['elementPeriods'][$ClassID];
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

  public function SchoolClasses(){
    $json = $this->PageConfig(1);
		return $json['data']['elements'];
	}

  public function Teachers(){
    $json = $this->PageConfig(2);
		return $json['data']['elements'];
	}

	public function Subjects(){
    $json = $this->PageConfig(3);
		return $json['data']['elements'];
	}

	public function Rooms(){
    $json = $this->PageConfig(4);
		return $json['data']['elements'];
	}

  public function Students(){
    $json = $this->PageConfig(5);
		return $json['data']['elements'];
	}

	public function Resources(){
    $json = $this->PageConfig(6);
		return $json['data']['elements'];
	}
}
?>
