<?php
	$install_error = array();
	// ---------MySQL checks--------- //
	if(!isset($_POST['msql_db']) || $_POST['msql_db']==""){
		$install_error['msql_db'] = "Database is not defined";
	}
	if(!isset($_POST['msql_user']) || $_POST['msql_user']==""){
		$install_error['msql_user'] = "User is not defined";
	}
	if(!isset($_POST['msql_pwd']) || $_POST['msql_pwd']==""){
		$install_error['msql_pwd'] = "Password is not defined";
	}
	if(!isset($_POST['msql_server']) || $_POST['msql_server']==""){
		$install_error['msql_server'] = "Server is not defined";
	}

	// ---------Admin checks--------- //
	if(!isset($_POST['admin_mail']) || $_POST['admin_mail']==""){
		$install_error['admin_mail'] = "Mail is not defined";
	}
	if(!isset($_POST['admin_firstname']) || $_POST['admin_firstname']==""){
		$install_error['admin_firstname'] = "Name is not defined";
	}
	if(!isset($_POST['admin_lastname']) || $_POST['admin_lastname']==""){
		$install_error['admin_lastname'] = "Name is not defined";
	}
	if(!isset($_POST['admin_username']) || $_POST['admin_username']==""){
		$install_error['admin_username'] = "Username is not defined";
	}

	// ---------Syntax fixes--------- //
	$_POST['BasePath'] .= "/"; //append a '/' to the end of the BasePath to make sure it's interpreted as a directory


	$_POST['PrivateKey'] = randomString(16); //gererate private key

	if($install_error==array()){
		$install_error = initSQL();
	}
	if($install_error==array()) {
		writeToFile();
		echo "Install Complete";
		//die;
	}

	// ---------Settings naar file--------- //
	function writeToFile(){
		$config_file = fopen($_POST['BasePath']."config.php", "w") or die("Unable to open file!");;
		fwrite($config_file, "<?php\n");
		fwrite($config_file, "\$ini_array = array();\n");
		fwrite($config_file, "\$ini_array['msql_server'] = '".$_POST['msql_server']."';\n");
		fwrite($config_file, "\$ini_array['msql_db'] = '".$_POST['msql_db']."';\n");
		fwrite($config_file, "\$ini_array['msql_prefix'] = '".$_POST['msql_prefix']."';\n");
		fwrite($config_file, "\$ini_array['msql_user'] = '".$_POST['msql_user']."';\n");
		fwrite($config_file, "\$ini_array['msql_pwd'] = '".$_POST['msql_pwd']."';\n\n");
		fwrite($config_file, "\$ini_array['BasePath'] = '".$_POST['BasePath']."';\n");
		fwrite($config_file, "\$ini_array['BaseURL'] = '".$_POST['BaseURL']."';\n");
		fwrite($config_file, "\$ini_array['RelativeURL'] = '".$_POST['RelativeURL']."';\n");
		fwrite($config_file, "\$ini_array['PrivateKey'] = '".$_POST['PrivateKey']."';\n");
		fclose($config_file);
	}

	// ---------init MySQL Database--------- //
	function initSQL(){
		$conn = mysqli_connect($_POST['msql_server'], $_POST['msql_user'], $_POST['msql_pwd'], $_POST['msql_db']);

		// ---------Check connection--------- //
		if (!$conn) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// ---------Prepend prefix to tables--------- //
		$prefixUsers = $_POST['msql_prefix']."Users";
		$prefixCategories = $_POST['msql_prefix']."Categories";
		$prefixItems = $_POST['msql_prefix']."Items";
		$prefixEntries = $_POST['msql_prefix']."Entries";

		// ---------Check if DB exists--------- //
		$sql_CheckDB_Users = mysqli_query($conn, "select 1 from $prefixUsers");
		$sql_CheckDB_Catagories = mysqli_query($conn, "select 1 from $prefixCategories");
		$sql_CheckDB_Items = mysqli_query($conn, "select 1 from $prefixCategories");
		$sql_CheckDB_Entries = mysqli_query($conn, "select 1 from $prefixCategories");
		if($sql_CheckDB_Users !== FALSE){
			$install_error['msql_error'] = "Table '$prefixUsers' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		if($sql_CheckDB_Catagories !== FALSE){
			$install_error['msql_error'] = "Table '$prefixCategories' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		if($sql_CheckDB_Items !== FALSE){
			$install_error['msql_error'] = "Table '$prefixItems' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		if($sql_CheckDB_Entries !== FALSE){
			$install_error['msql_error'] = "Table '$prefixEntries' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		// ---------Creeer Database--------- //
		// Users DB
		$sql_CreateDB_Users = "CREATE TABLE $prefixUsers (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`username` varchar(64) NOT NULL DEFAULT '',
			`firstname` varchar(64) DEFAULT NULL,
			`lastname` varchar(64) DEFAULT NULL,
			`mail` varchar(128) DEFAULT NULL,
			`admin` tinyint(1) DEFAULT '0',
			`password` varchar(256) NOT NULL DEFAULT '',
			`iv` varchar(32) DEFAULT NULL,
			`last_login` datetime DEFAULT NULL,
			`devices` varchar(256) DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Users)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// ---------Insert Data--------- //
		// require encryption
		require_once($_POST['BasePath']."scripts/helpers/encryption.php");

		// Admin User
		$adminFirstname = $_POST['admin_firstname'];
		$adminLastname = $_POST['admin_lastname'];
		$adminUsername = $_POST['admin_username'];
		$adminMail = $_POST['admin_mail'];
		$adminIV = randomString(16); //Unieke code per user, voor "end to end" encryptie
		$adminPasswordRaw = $_POST['admin_password'];
		$adminPasswordEnc = Encryption::encrypt($_POST['PrivateKey'], $adminIV, $adminPasswordRaw);
		$sql_Insert_AdminUser = "REPLACE INTO $prefixUsers (firstname, lastname, username, mail, admin, password, iv)
		VALUES ('$adminFirstname', '$adminLastname', '$adminUsername', '$adminMail', 1, '$adminPasswordEnc', '$adminIV')";

		if (!mysqli_query($conn, $sql_Insert_AdminUser)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
	}
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
?>
