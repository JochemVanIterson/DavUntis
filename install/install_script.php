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
		$prefixDepartments = $_POST['msql_prefix']."Departments";
		$prefixDepartments_his = $_POST['msql_prefix']."Departments_his";

		$prefixDummyUsers = $_POST['msql_prefix']."DummyUsers";

		$prefixPeriods = $_POST['msql_prefix']."Periods";
		$prefixPeriods_his = $_POST['msql_prefix']."Periods_his";

		$prefixRooms = $_POST['msql_prefix']."Rooms";
		$prefixRooms_his = $_POST['msql_prefix']."Rooms_his";

		$prefixRoosterlist = $_POST['msql_prefix']."roosterlist";

		$prefixSchoolClasses = $_POST['msql_prefix']."SchoolClasses";
		$prefixSchoolClasses_his = $_POST['msql_prefix']."SchoolClasses_his";

		$prefixSettings = $_POST['msql_prefix']."Settings";

		$prefixSchoolSubjects = $_POST['msql_prefix']."Subjects";
		$prefixSchoolSubjects_his = $_POST['msql_prefix']."Subjects_his";

		$prefixSchoolTeachers = $_POST['msql_prefix']."Teachers";
		$prefixSchoolTeachers_his = $_POST['msql_prefix']."Teachers_his";

		$prefixUsers = $_POST['msql_prefix']."Users";

		// ---------Check if DB exists--------- //
		$sql_CheckDB_Departments = mysqli_query($conn, "select 1 from $prefixDepartments");
		if($sql_CheckDB_Departments !== FALSE){
			$install_error['msql_error'] = "Table '$prefixDepartments' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		$sql_CheckDB_Departments_his = mysqli_query($conn, "select 1 from $prefixDepartments_his");
		if($sql_CheckDB_Departments_his !== FALSE){
			$install_error['msql_error'] = "Table '$prefixDepartments_his' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_DummyUsers = mysqli_query($conn, "select 1 from $prefixDummyUsers");
		if($sql_CheckDB_DummyUsers !== FALSE){
			$install_error['msql_error'] = "Table '$prefixDummyUsers' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_Periods = mysqli_query($conn, "select 1 from $prefixPeriods");
		if($sql_CheckDB_Periods !== FALSE){
			$install_error['msql_error'] = "Table '$prefixPeriods' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		$sql_CheckDB_Periods_his = mysqli_query($conn, "select 1 from $prefixPeriods_his");
		if($sql_CheckDB_Periods_his !== FALSE){
			$install_error['msql_error'] = "Table '$prefixPeriods_his' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_Rooms = mysqli_query($conn, "select 1 from $prefixRooms");
		if($sql_CheckDB_Rooms !== FALSE){
			$install_error['msql_error'] = "Table '$prefixRooms' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		$sql_CheckDB_Rooms_his = mysqli_query($conn, "select 1 from $prefixRooms_his");
		if($sql_CheckDB_Rooms_his !== FALSE){
			$install_error['msql_error'] = "Table '$prefixRooms_his' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_Roosterlist = mysqli_query($conn, "select 1 from $prefixRoosterlist");
		if($sql_CheckDB_Roosterlist !== FALSE){
			$install_error['msql_error'] = "Table '$prefixRoosterlist' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_SchoolClasses = mysqli_query($conn, "select 1 from $prefixSchoolClasses");
		if($sql_CheckDB_SchoolClasses !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSchoolClasses' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		$sql_CheckDB_SchoolClasses_his = mysqli_query($conn, "select 1 from $prefixSchoolClasses_his");
		if($sql_CheckDB_SchoolClasses_his !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSchoolClasses_his' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_Settings = mysqli_query($conn, "select 1 from $prefixSettings");
		if($sql_CheckDB_Settings !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSettings' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_SchoolSubjects = mysqli_query($conn, "select 1 from $prefixSchoolSubjects");
		if($sql_CheckDB_SchoolSubjects !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSchoolSubjects' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		$sql_CheckDB_SchoolSubjects_his = mysqli_query($conn, "select 1 from $prefixSchoolSubjects_his");
		if($sql_CheckDB_SchoolSubjects_his !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSchoolSubjects_his' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		$sql_CheckDB_SchoolTeachers = mysqli_query($conn, "select 1 from $prefixSchoolTeachers");
		if($sql_CheckDB_SchoolTeachers !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSchoolTeachers' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}
		$sql_CheckDB_SchoolTeachers_his = mysqli_query($conn, "select 1 from $prefixSchoolTeachers_his");
		if($sql_CheckDB_SchoolTeachers_his !== FALSE){
			$install_error['msql_error'] = "Table '$prefixSchoolTeachers_his' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}


		$sql_CheckDB_Users = mysqli_query($conn, "select 1 from $prefixUsers");
		if($sql_CheckDB_Users !== FALSE){
			$install_error['msql_error'] = "Table '$prefixUsers' already exists. Change prefix, or remove the existing db";
			return $install_error;
		}

		// ---------Creeer Database--------- //
		// Departments DB
		$sql_CreateDB_Departments = "CREATE TABLE $prefixDepartments (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) DEFAULT NULL,
		  `label` varchar(64) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Departments)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		// Departments_his DB
		$sql_CreateDB_Departments_his = "CREATE TABLE $prefixDepartments_his (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `itm_id` int(11) DEFAULT NULL,
		  `name` varchar(64) DEFAULT NULL,
		  `label` varchar(64) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  `status` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Departments_his)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// DummyUsers DB
		$sql_CreateDB_DummyUsers = "CREATE TABLE $prefixDummyUsers (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `username` varchar(128) DEFAULT NULL,
		  `password` varchar(256) DEFAULT NULL,
		  `iv` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_DummyUsers)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// Periods DB
		$sql_CreateDB_Periods = "CREATE TABLE $prefixPeriods (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `lesson_id` int(11) DEFAULT NULL,
		  `lesson_number` int(11) DEFAULT NULL,
		  `lesson_code` varchar(32) DEFAULT '',
		  `lesson_text` varchar(128) DEFAULT NULL,
		  `period_text` varchar(64) DEFAULT NULL,
		  `has_period_text` tinyint(1) DEFAULT NULL,
		  `date` datetime DEFAULT NULL,
		  `start_time` datetime DEFAULT NULL,
		  `end_time` datetime DEFAULT NULL,
		  `student_group` varchar(32) DEFAULT NULL,
		  `has_info` tinyint(1) DEFAULT NULL,
		  `code` int(11) DEFAULT NULL,
		  `cell_state` varchar(32) DEFAULT NULL,
		  `priority` int(11) DEFAULT NULL,
		  `schoolclasses` varchar(128) DEFAULT NULL,
		  `teachers` varchar(128) DEFAULT NULL,
		  `subjects` varchar(128) DEFAULT NULL,
		  `rooms` varchar(128) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Periods)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		// Periods_his DB
		$sql_CreateDB_Periods_his = "CREATE TABLE $prefixPeriods_his (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `itm_id` int(11) DEFAULT NULL,
		  `lesson_id` int(11) DEFAULT NULL,
		  `lesson_number` int(11) DEFAULT NULL,
		  `lesson_code` varchar(32) DEFAULT '',
		  `lesson_text` varchar(128) DEFAULT NULL,
		  `period_text` varchar(64) DEFAULT NULL,
		  `has_period_text` tinyint(1) DEFAULT NULL,
		  `date` datetime DEFAULT NULL,
		  `start_time` datetime DEFAULT NULL,
		  `end_time` datetime DEFAULT NULL,
		  `student_group` varchar(32) DEFAULT NULL,
		  `has_info` tinyint(1) DEFAULT NULL,
		  `code` int(11) DEFAULT NULL,
		  `cell_state` varchar(32) DEFAULT NULL,
		  `priority` int(11) DEFAULT NULL,
		  `schoolclasses` varchar(128) DEFAULT NULL,
		  `teachers` varchar(128) DEFAULT NULL,
		  `subjects` varchar(128) DEFAULT NULL,
		  `rooms` varchar(128) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  `status` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Periods_his)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// Rooms DB
		$sql_CreateDB_Rooms = "CREATE TABLE $prefixRooms (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(32) DEFAULT NULL,
		  `longname` varchar(128) DEFAULT NULL,
		  `displayname` varchar(64) DEFAULT '',
		  `building_id` int(11) DEFAULT NULL,
		  `capacity` int(11) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Rooms)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		// Rooms_his DB
		$sql_CreateDB_Rooms_his = "CREATE TABLE $prefixRooms_his (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `itm_id` int(11) DEFAULT NULL,
		  `name` varchar(32) DEFAULT NULL,
		  `longname` varchar(128) DEFAULT NULL,
		  `displayname` varchar(64) DEFAULT '',
		  `building_id` int(11) DEFAULT NULL,
		  `capacity` int(11) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  `status` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Rooms_his)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// Roosterlist DB
		$sql_CreateDB_Roosterlist = "CREATE TABLE $prefixRoosterlist (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `usermail` varchar(128) NOT NULL DEFAULT '',
		  `name` varchar(64) DEFAULT NULL,
		  `departments` varchar(64) DEFAULT NULL,
		  `schoolclasses` varchar(64) DEFAULT NULL,
		  `periods` longtext,
		  `created` datetime DEFAULT NULL,
		  `last_used` double DEFAULT NULL,
		  `force` tinyint(1) DEFAULT NULL,
		  `hash` varchar(32) DEFAULT NULL,
		  `auto_add` tinyint(1) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Roosterlist)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// SchoolClasses DB
		$sql_CreateDB_SchoolClasses = "CREATE TABLE $prefixSchoolClasses (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(32) DEFAULT NULL,
		  `longname` varchar(64) DEFAULT NULL,
		  `displayname` varchar(32) DEFAULT '',
		  `dids` varchar(32) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_SchoolClasses)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		// SchoolClasses_his DB
		$sql_CreateDB_SchoolClasses_his = "CREATE TABLE $prefixSchoolClasses_his (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `itm_id` int(11) DEFAULT NULL,
		  `name` varchar(32) DEFAULT NULL,
		  `longname` varchar(64) DEFAULT NULL,
		  `displayname` varchar(32) DEFAULT '',
		  `dids` varchar(32) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  `status` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_SchoolClasses_his)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// Settings DB
		$sql_CreateDB_Settings = "CREATE TABLE $prefixSettings (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `setting_key` varchar(64) NOT NULL DEFAULT '',
		  `setting_value` longtext,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_Settings)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// SchoolSubjects DB
		$sql_CreateDB_SchoolSubjects = "CREATE TABLE $prefixSchoolSubjects (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) DEFAULT NULL,
		  `longname` varchar(128) DEFAULT NULL,
		  `displayname` varchar(64) DEFAULT '',
		  `alternatename` varchar(64) DEFAULT NULL,
		  `dids` varchar(64) DEFAULT NULL,
		  `schoolclasses` varchar(128) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_SchoolSubjects)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		// SchoolSubjects_his DB
		$sql_CreateDB_SchoolSubjects_his = "CREATE TABLE $prefixSchoolSubjects_his (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `itm_id` int(11) DEFAULT NULL,
		  `name` varchar(64) DEFAULT NULL,
		  `longname` varchar(128) DEFAULT NULL,
		  `displayname` varchar(64) DEFAULT '',
		  `alternatename` varchar(64) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  `status` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_SchoolSubjects_his)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

		// SchoolTeachers DB
		$sql_CreateDB_SchoolTeachers = "CREATE TABLE $prefixSchoolTeachers (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(32) DEFAULT NULL,
		  `forename` varchar(64) DEFAULT NULL,
		  `longname` varchar(64) DEFAULT NULL,
		  `displayname` varchar(64) DEFAULT '',
		  `dids` varchar(32) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_SchoolTeachers)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		// SchoolTeachers_his DB
		$sql_CreateDB_SchoolTeachers_his = "CREATE TABLE $prefixSchoolTeachers_his (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `itm_id` int(11) DEFAULT NULL,
		  `name` varchar(32) DEFAULT NULL,
		  `forename` varchar(64) DEFAULT NULL,
		  `longname` varchar(64) DEFAULT NULL,
		  `displayname` varchar(64) DEFAULT '',
		  `dids` varchar(32) DEFAULT NULL,
		  `last_update` datetime DEFAULT NULL,
		  `hash` varchar(256) DEFAULT NULL,
		  `status` varchar(32) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if(!mysqli_query($conn, $sql_CreateDB_SchoolTeachers_his)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}

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
