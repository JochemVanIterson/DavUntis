<?php
class SQL {
	private $connection;
	private $ini_array;

	function __construct($ini_array) {
		$this->ini_array = $ini_array;

		$SQLservername = $this->ini_array["msql_server"];
		$SQLusername = $this->ini_array["msql_user"];
		$SQLpassword = $this->ini_array["msql_pwd"];
		$SQLdbname = $this->ini_array["msql_db"];

		$this->connection = mysqli_connect($SQLservername, $SQLusername, $SQLpassword, $SQLdbname) or die("Error " . mysqli_error($this->connection));
	}

	// ------------------------------------------------------------------------ Login ------------------------------------------------------------------------ //
	function Login($user, $password, $publicIV){
		// ---------Check if User in DB--------- //
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$user') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return array("login_error"=>"User doesn't exists");
		}

		// ---------Check if PW matches UserData--------- //
		$PrivateKey = $this->ini_array['PrivateKey'];
		$UserData = mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC);
		// Decrypt with temperary key
		$password_dec = Encryption::decrypt($PrivateKey, $publicIV, $password);
		// Encrypt with personal key
		$password_enc = Encryption::encrypt($PrivateKey, $UserData['iv'], $password_dec);
		if($UserData['password'] != $password_enc){
			return array("login_error"=>"Wrong password");
		}

		// ---------Login Successful, Update last login--------- //
		$sqlq_Update_LastLogin = "UPDATE $prefixUsers SET last_login=NOW() WHERE username='$user'";
		if (!mysqli_query($this->connection, $sqlq_Update_LastLogin)) {
			echo "Error updating record: " . mysqli_error($this->connection);
		    return array("login_error"=>"Database error");
		}
		return $UserData;
	}
	function CheckLogin($cookie){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$cookie[admin_username]' AND iv = '$cookie[iv]') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return array("login_error"=>"iv doesnt match");
		}
		return array("login"=>"success", "data"=>mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC));
	}

	// ------------------------------------------------------------------------ User ------------------------------------------------------------------------- //
	function addUser($post_data){ // ----------------------------- Add ----------------------------- //
		if(isset($post_data['admin']) && $post_data['admin'] == "on"){
			$post_data['admin']=1;
		} else {
			$post_data['admin']=0;
		}
		$PrivateKey = $this->ini_array['PrivateKey'];
		$Firstname = $post_data['firstname'];
		$Lastname = $post_data['lastname'];
		$Username = $post_data['username'];
		$Mail = $post_data['mail'];
		$Admin = $post_data['admin'];
		$IV = $this->randomString(16); //Unieke code per user, voor "end to end" encryptie
		$PasswordRaw = $post_data['password'];
		$PasswordEnc = Encryption::encrypt($PrivateKey, $IV, $PasswordRaw);
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sql_Insert_User = "REPLACE INTO $prefixUsers (firstname, lastname, username, mail, admin, password, iv)
		VALUES ('$Firstname', '$Lastname', '$Username', '$Mail', $Admin, '$PasswordEnc', '$IV')";

		if (!mysqli_query($this->connection, $sql_Insert_User)) {
			return array("status"=>"failed", "error"=>"SQL error: ".mysqli_connect_error());
		} else {
			return array("status"=>"success");
		}
	}
	function updateUser($post_data){ // -------------------------- Update -------------------------- //
		if($post_data['password'] == ""){
			unset($post_data['password']);
		} else {
			$IV = $this->randomString(16); //Unieke code per user, voor "end to end" encryptie
			$PasswordRaw = $post_data['password'];
			$PrivateKey = $this->ini_array['PrivateKey'];
			$PasswordEnc = Encryption::encrypt($PrivateKey, $IV, $PasswordRaw);
			$post_data['password'] = $PasswordEnc;
			$post_data['iv'] = $IV;
		}
		if(isset($post_data['admin']) && $post_data['admin'] == "on"){
			$post_data['admin']=1;
		} else {
			$post_data['admin']=0;
		}
		$username = $post_data['username'];
		unset($post_data['username']);
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sql_update_user = "UPDATE $prefixUsers SET ";
		foreach($post_data as $key => $value){
			if($sql_update_user != "UPDATE $prefixUsers SET ")$sql_update_user .= ", ";
			$sql_update_user .= "$key='$value'";
		}
		$sql_update_user .= "WHERE username='$username'";

		if (!mysqli_query($this->connection, $sql_update_user)) {
			return array("status"=>"failed", "error"=>"SQL error: ".mysqli_connect_error());
		} else {
			return array("status"=>"success", "admin"=>$post_data['admin']);
		}
	}
	function removeUser($user){ // ------------------------------- Remove -------------------------- //
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sql_remove_user = "DELETE FROM $prefixUsers WHERE username='$user'";
		// echo $sql_remove_user;
		if (!mysqli_query($this->connection, $sql_remove_user)) {
			return array("status"=>"failed", "error"=>"SQL error: ".mysqli_connect_error());
		} else {
			return array("status"=>"success");
		}
	}
	function getUser($user){ // ---------------------------------- Get Query ----------------------- //
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$user') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return null;
		}
		$UserData = mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC);
		return $UserData;
	}
	function getUsers($web = true){ // --------------------------- Get All ------------------------- //
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUsers = "SELECT * from $prefixUsers ORDER BY firstname ASC";
		$sql_GetUsers = mysqli_query($this->connection, $sqlq_GetUsers);
		if(mysqli_num_rows($sql_GetUsers) == 0){
			return null;
		}
		while ($row_user = mysqli_fetch_assoc($sql_GetUsers)){
			if(!$web){
				unset($row_user['password']);
				unset($row_user['iv']);
			}
			$UserData[] = $row_user;
		}
		return $UserData;
	}

	function addSetting($post_data){ // -------------------------- Add ----------------------------- //
		$key = $post_data['key'];
		$value = $post_data['value'];
		$prefixSettings = $this->ini_array['msql_prefix']."Settings";
		$sql_Insert_Settings = "INSERT INTO $prefixSettings (setting_key, setting_value) VALUES ('$key', '$value');";

		if (!mysqli_query($this->connection, $sql_Insert_Settings)) {
			return array("status"=>"failed", "query"=>$sql_Insert_Settings, "error"=>"SQL error: ".mysqli_connect_error());
		} else {
			return array("status"=>"success");
		}
	}
	function updateSetting($post_data){ // ----------------------- Update -------------------------- //
		$key = $post_data['key'];
		$value = $post_data['value'];
		$prefixSettings = $this->ini_array['msql_prefix']."Settings";
		$sql_update_setting = "UPDATE $prefixSettings SET setting_value='$value' WHERE setting_key='$key';";

		if (!mysqli_query($this->connection, $sql_update_setting)) {
			return array("status"=>"failed", "error"=>"SQL error: ".mysqli_connect_error());
		} else {
			return array("status"=>"success");
		}
	}
	function removeSetting($key){
		$prefixSettings = $this->ini_array['msql_prefix']."Settings";
		$sql_remove_setting = "DELETE FROM $prefixSettings WHERE key='$key'";
		if (!mysqli_query($this->connection, $sql_remove_setting)) {
			return array("status"=>"failed", "error"=>"SQL error: ".mysqli_connect_error());
		} else {
			return array("status"=>"success");
		}
	}
	function getSetting($key){
		$prefixSettings = $this->ini_array['msql_prefix']."Settings";
		$sqlq_GetSetting = "SELECT * from $prefixSettings WHERE setting_key='$key' LIMIT 1";
		$sql_GetSetting = mysqli_query($this->connection, $sqlq_GetSetting);
		if(mysqli_num_rows($sql_GetSetting) == 0){
			return null;
		}
		$SettingData = mysqli_fetch_array($sql_GetSetting, MYSQLI_ASSOC);
		return $SettingData['setting_value'];
	}

	// ------------------------------------------------------------------------ Helpers ---------------------------------------------------------------------- //
	function randomString($length) {
		$str = "";
		$characters = array_merge(range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}
}
