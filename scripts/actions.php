<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  require_once("../config.php");
  require_once($ini_array['BasePath']."scripts/sql.php");
  require_once($ini_array['BasePath']."scripts/untis_retreiver.php");
  require_once($ini_array['BasePath']."scripts/helpers/encryption.php");

  require_once($ini_array["BasePath"]."scripts/untis_curl.php");
  require_once($ini_array["BasePath"]."scripts/untis_login.php");
  require_once($ini_array["BasePath"]."scripts/untis_data.php");

  $SQL = new SQL($ini_array);
  $UntisRetreiver = new untisReceiver($ini_array);

  $UntisURL = $SQL->getSetting('untis_url');
  $UntisSchool = $SQL->getSetting('untis_school');

  $UntisLogin = new UntisLogin($UntisURL, $_COOKIE, $ini_array);
  $UntisLogin->GetSessionIDData((isset($_COOKIE["school"])) ? $_COOKIE["school"] : $UntisSchool);
  // ---------------------------------------------------------------- UNTIS ---------------------------------------------------------------- //
  if($_POST['action']=='untis_login'){
    $data = $_POST['data'];
    $LoginDone = $UntisLogin->Login($UntisSchool, $data['username'], $data['password']);
    die(json_encode($LoginDone));
  }
  $PrivateKey = $ini_array['PrivateKey'];
  $DummyUsers = $SQL->getDummyUsers(true);
  $RandomDummyUser = $DummyUsers[rand(0, sizeof($DummyUsers)-1)];
  $RandomDummyUser['password'] = Encryption::decrypt($PrivateKey, $RandomDummyUser['iv'], $RandomDummyUser['password']);
  $LoginDone = $UntisLogin->Login($UntisSchool, $RandomDummyUser['username'], $RandomDummyUser['password']);

  $sessionData = $LoginDone['data'];
  $UntisData = new UntisData($UntisURL, $sessionData, $ini_array);

  // ---------------------------------------------------------------- DATA ----------------------------------------------------------------- //
  if($_POST['action']=='untis_departments'){
    $fresh = isset($_POST['fresh']) && $_POST['fresh']=='true';
    $data = $UntisRetreiver->getDepartments($fresh, $UntisData);
    die($data);
  }
  if($_POST['action']=='untis_school_classes'){
    $fresh = isset($_POST['fresh']) && $_POST['fresh']=='true';
    $data = $UntisRetreiver->getSchoolClasses($fresh, $UntisData);
    die($data);
  }

  // ---------------------------------------------------------------- USER ----------------------------------------------------------------- //
  if($_POST['action']=='pagedata'){
    if($_POST['page']=='roosterlist'){
      $returnArray = array();
      $returnArray['rooster'] = $SQL->getRoosterUser($_COOKIE['username']);
      if($returnArray['rooster']==null)die(json_encode(array("page"=>$_POST['page'], "error"=>'empty')));

      $returnArray['departments'] = array();
      $returnArray['schoolclasses'] = array();

      foreach($returnArray['rooster'] as $key=>$roosteritm){
        $roosteritm['departments'] = json_decode($roosteritm['departments']);
        foreach($roosteritm['departments'] as $depID){
          if(!isset($returnArray['departments'][$depID])){
            $returnArray['departments'][$depID] = $UntisRetreiver->getSingleDepartment($depID);
          }
        }

        $roosteritm['schoolclasses'] = json_decode($roosteritm['schoolclasses']);
        foreach($roosteritm['schoolclasses'] as $classID){
          if(!isset($returnArray['schoolclasses'][$classID])){
            $returnArray['schoolclasses'][$classID] = $UntisRetreiver->getSingleSchoolClass($classID);
          }
        }
      }
      die(json_encode(array("page"=>$_POST['page'], "data"=>$roosterData)));
    }
  }

  // ---------------------------------------------------------------- ScheduleBuilder ------------------------------------------------------ //
  if($_POST['action']=='ScheduleBuilder'){
    if($_POST['page']=='dep'){
      $ReturnData = array();
      $ReturnData['departments'] = $UntisRetreiver->getDepartmentsSQL();
      $ReturnData['schoolclasses'] = $UntisRetreiver->getSchoolClassesSQL();
      $ReturnData['dis_departments'] = json_decode($SQL->getSetting('dis_departments'));
      die(json_encode(array("page"=>$_POST['page'], "data"=>$ReturnData)));
    } else
    if($_POST['page']=='per'){
      $ReturnData = array();
      $ReturnData['raw'] = $UntisRetreiver->getSubjects(true, $UntisData);
      die(json_encode(array("page"=>$_POST['page'], "data"=>$ReturnData)));
    }
    die(json_encode(array("error"=>"ScheduleBuilder action doesn't exist"), true));
  }

  // ---------------------------------------------------------------- ADMIN ---------------------------------------------------------------- //
  if($_POST['action']=='admin_login'){ // ------------------------------------- Login ------------------------------------- //
    if(!isset($_POST['user']) || $_POST['user']=="" || !isset($_POST['pw']) || $_POST['pw']==""){
    	$ErrorState = array(
    		"login"=>"failed", "message"=>"", "header"=>getallheaders(), "POST"=>$_POST
    	);
    	if(!isset($_POST['user']) || $_POST['user']==""){
    		if($ErrorState["message"]!="")$ErrorState["message"].=", ";
    		$ErrorState["message"].="User Empty";
    	}
    	if(!isset($_POST['pw']) || $_POST['pw']==""){
    		if($ErrorState["message"]!="")$ErrorState["message"].=", ";
    		$ErrorState["message"].="PW Empty, ".json_encode($_POST, true);
    	}
    	echo json_encode($ErrorState, true);
    	die;
    }

    if(isset($_POST['raw'])){
    	$PrivateKey = $ini_array['PrivateKey'];
    	$_POST['iv'] = Encryption::randomString(16);
    	$_POST['pw'] = Encryption::encrypt($PrivateKey, $_POST['iv'], $_POST['pw']);
    }

    $Login_attempt = $SQL->Login($_POST['user'], $_POST['pw'], $_POST['iv']);
    if(isset($Login_attempt['login_error'])){
    	echo json_encode(array(
    		"login"=>"failed",
    		"message"=>$Login_attempt['login_error'],
    		"Login_data"=>array(
    			"user"=>$_POST['user'],
    			"pw"=>$_POST['pw'],
    			"iv"=>$_POST['iv']
    		)
    	), true);
    	die;
    }

    unset($Login_attempt['password']);
    echo json_encode(array("login"=>"success", "data"=>$Login_attempt), true);
    die();
  }
  if($_POST['action']=='admin_pagedata'){ // ---------------------------------- Admin Page data --------------------------- //
    if($_POST['page']=='Dash'){ // --------------------------- Dash --------------------------- //
      die(json_encode(array("page"=>$_POST['page'], "data"=>"No Dashdata yet")));
    }
    if($_POST['page']=='Users'){ // -------------------------- Users -------------------------- //
      $UserData = $SQL->getUsers(true);
      die(json_encode(array("page"=>$_POST['page'], "data"=>$UserData)));
    }
    if($_POST['page']=='Untis'){ // -------------------------- Untis -------------------------- //
      $UntisData = array();
      $UntisData['untis_url'] = $SQL->getSetting('untis_url');
      $UntisData['untis_school'] = $SQL->getSetting('untis_school');
      $UntisData['untis_sync_before'] = $SQL->getSetting('untis_sync_before');
      $UntisData['untis_sync_after'] = $SQL->getSetting('untis_sync_after');
      $UntisData['untis_school'] = $SQL->getSetting('untis_school');
      $UntisData['dummyUsers'] = $SQL->getDummyUsers();
      $UntisData['departments'] = $UntisRetreiver->getDepartmentsSQL();
      $UntisData['dis_departments'] = $SQL->getSetting('dis_departments');
      die(json_encode(array("page"=>$_POST['page'], "data"=>$UntisData)));
    }
    die(json_encode(array("error"=>"page doesn't exist"), true));
  }
  if($_POST['action']=='updateDB'){ // ---------------------------------------- Update DB --------------------------------- //
    $responseArray = array();

    // -------------------- Exclude unused department data from collection -------------------- //
    $responseArray['jDS_response'] = $UntisData->jsonDepartmentService($UntisRetreiver, $SQL);
    // -------------------------------------- SchoolClasses ----------------------------------- //
    $ServerSchoolClasses = $UntisData->SchoolClasses();
    $responseArray['schoolclasses'] = $UntisRetreiver->insertSchoolClasses($ServerSchoolClasses);

    // -------------------------------------- Subjects ---------------------------------------- //
    $ServerSubjects = $UntisData->Subjects();
    $responseArray['subjects'] = $UntisRetreiver->insertSubjects($ServerSubjects);

    // -------------------------------------- Periods ---------------------------------------- //
    $sync_history = intval(str_replace('week ', '', $SQL->getSetting('untis_sync_before')))*-1;
    $sync_future = intval(str_replace('week ', '', $SQL->getSetting('untis_sync_after')));

    $schoolclass_ids = $UntisRetreiver->getSchoolClassIDsSQL();

    $dates = array();
    $ServerPeriods = array();
    for($DateWalker = $sync_history; $DateWalker <= $sync_future; $DateWalker++) {
      $date = date('Y-m-d', strtotime($DateWalker." week"));
      foreach ($schoolclass_ids as $key => $id) {
        $ServerPeriods = array_merge($ServerPeriods, $UntisData->Periods($id['id'], $date));
      }
      array_push($dates, $date);
    }
    $responseArray['dates'] = $dates;
    $responseArray['periods'] = $UntisRetreiver->insertPeriods($ServerPeriods);
    die(json_encode($responseArray));
  }

  // ---------------------------------------------------------------- SQL Actions ---------------------------------------------------------- //
  if($_POST['action']=='user'){ // -------------------------------------------- Users ------------------------------------- //
    if($_POST['sql_action']=='insert'){
      $response = $SQL->addUser($_POST['fields']);
    }
    if($_POST['sql_action']=='remove'){
      $response = $SQL->removeUser($_POST['username']);
    }
    if($_POST['sql_action']=='update'){
      $response = $SQL->updateUser($_POST['fields']);
    }
    die(json_encode($response));
  }
  if($_POST['action']=='untis'){ // ------------------------------------------- Untis ------------------------------------- //
    if($_POST['sql_action']=='insert'){
      $response = $SQL->addSetting($_POST['fields']);
    }
    if($_POST['sql_action']=='remove'){
      $response = $SQL->removeSetting($_POST['key']);
    }
    if($_POST['sql_action']=='update'){
      // ----------------------------------- untis_url ----------------------------------- //
      if($SQL->getSetting('untis_url')==null){
        $response = $SQL->addSetting(array("key"=>"untis_url", "value"=>$_POST['fields']['untis_url']));
      } else {
        $response = $SQL->updateSetting(array("key"=>"untis_url", "value"=>$_POST['fields']['untis_url']));
      }
      if($response['status']!="success"){
         die(json_encode($response));
      }

      // ----------------------------------- untis_school ----------------------------------- //
      if($SQL->getSetting('untis_school')==null){
        $response = $SQL->addSetting(array("key"=>"untis_school", "value"=>$_POST['fields']['untis_school']));
      } else {
        $response = $SQL->updateSetting(array("key"=>"untis_school", "value"=>$_POST['fields']['untis_school']));
      }
      if($response['status']!="success"){
         die(json_encode($response));
      }

      // ----------------------------------- untis_sync_before ----------------------------------- //
      if($SQL->getSetting('untis_sync_before')==null){
        $response = $SQL->addSetting(array("key"=>"untis_sync_before", "value"=>$_POST['fields']['untis_sync_before']));
      } else {
        $response = $SQL->updateSetting(array("key"=>"untis_sync_before", "value"=>$_POST['fields']['untis_sync_before']));
      }
      if($response['status']!="success"){
         die(json_encode($response));
      }

      // ----------------------------------- untis_sync_after ----------------------------------- //
      if($SQL->getSetting('untis_sync_after')==null){
        $response = $SQL->addSetting(array("key"=>"untis_sync_after", "value"=>$_POST['fields']['untis_sync_after']));
      } else {
        $response = $SQL->updateSetting(array("key"=>"untis_sync_after", "value"=>$_POST['fields']['untis_sync_after']));
      }
      if($response['status']!="success"){
         die(json_encode($response));
      }
    }

    if($_POST['sql_action']=='dis_departments'){
      if($SQL->getSetting('dis_departments')==null){
        $response = $SQL->addSetting(array("key"=>"dis_departments", "value"=>json_encode($_POST['fields'])));
      } else {
        $response = $SQL->updateSetting(array("key"=>"dis_departments", "value"=>json_encode($_POST['fields'])));
      }
      if($response['status']!="success"){
         die(json_encode($response));
      }
    }
    die(json_encode($response));
  }
  if($_POST['action']=='dummyUser'){ // --------------------------------------- DummyUsers -------------------------------- //
    if($_POST['sql_action']=='saveMix'){
      $_POST['fields']['insert'] = json_decode($_POST['fields']['insert'], true);
      $_POST['fields']['update'] = json_decode($_POST['fields']['update'], true);
      foreach ($_POST['fields']['insert'] as $key => $value) {
        $response = $SQL->addDummyUser($value);
        if($response['status']!='success')die(json_encode($response));
      }
      foreach ($_POST['fields']['update'] as $key => $value) {
        $response = $SQL->updateDummyUser($value);
        if($response['status']!='success')die(json_encode($response));
      }
      die(json_encode(array("status"=>"success", "postdata"=>$_POST)));
    }
    if($_POST['sql_action']=='insert'){
      $response = $SQL->addDummyUser($_POST['fields']);
    }
    if($_POST['sql_action']=='update'){
      $response = $SQL->updateDummyUser($_POST['fields']);
    }
    if($_POST['sql_action']=='remove'){
      $response = $SQL->removeDummyUser($_POST['id']);
    }
    die(json_encode($response));
  }
  die(json_encode(array("error"=>"action doesn't exist", "meh"=>$_POST), true));
?>
