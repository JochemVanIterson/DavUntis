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

  // ---------------------------------------------------------------- UNTIS ---------------------------------------------------------------- //
  if($_POST['action']=='untis_login'){
    $data = $_POST['data'];
		$UntisLogin = new UntisLogin($UntisURL, $_COOKIE, $ini_array);
		if(!isset($_COOKIE['JSESSIONID']))$UntisLogin->GetSessionIDData((isset($_COOKIE["school"])) ? $_COOKIE["school"] : $UntisSchool);
    $LoginDone = $UntisLogin->Login($UntisSchool, $data['username'], $data['password']);
    die(json_encode($LoginDone));
  }
  if($_POST['action']=='untis_departments'){
    if(isset($_POST['fresh']) && $_POST['fresh']=='true'){
      $UntisData = new UntisData($UntisURL, $_COOKIE, $ini_array);
      $ServerDepartments = $UntisData->Departments();
      $response = $UntisRetreiver->insertDepartments($ServerDepartments);
      if($response['success']=true){
        $data = $UntisRetreiver->getDepartments();
        die(json_encode($data, true));
      } else {
        die(json_encode($response, true));
      }
    } else {
      $data = $UntisRetreiver->getDepartments();
      die(json_encode($data, true));
    }
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
  if($_POST['action']=='admin_pagedata'){ // ---------------------------------- Page data --------------------------------- //
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
      die(json_encode(array("page"=>$_POST['page'], "data"=>$UntisData)));
    }
    die(json_encode(array("error"=>"page doesn't exist"), true));
  }
  if($_POST['action']=='updateDB'){ // ---------------------------------------- Update DB --------------------------------- //
    $UntisData = new UntisData($UntisURL, $_COOKIE, $ini_array);
    $ServerDepartments = $UntisData->Departments();
    $response = $UntisRetreiver->insertDepartments($ServerDepartments);
    die(json_encode($response));
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
    }
    die(json_encode($response));
  }
  die(json_encode(array("error"=>"action doesn't exist", "meh"=>$_POST), true));
?>
