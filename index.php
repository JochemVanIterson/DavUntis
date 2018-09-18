<!DOCTYPE html>
<html lang="en">
<head>
  <?php
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		require_once("config.php");
  	require_once("scripts/helpers/jsonparser.php");
  	$package = JsonParser("package.json");
		echo "<base href='//$ini_array[BaseURL]'>";
	?>
  <link rel="manifest" href="manifest.json">
	<link rel="shortcut icon" type="image/png" href="assets/launcher_icons/16.png">
	<link rel="apple-touch-icon" href="assets/launcher_icons/192_apple.png">
	<meta name="theme-color" content="#1565C0">
	<meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel='stylesheet' type="text/css" href='assets/fonts/font-awesome.min.css'>
  <link rel="stylesheet" type="text/css" href="assets/css/colors.css">
  <link rel="stylesheet" type="text/css" href="assets/css/ui.css">
  <link rel="stylesheet" type="text/css" href="assets/css/main.css">

  <?php // ----------------------------------------- Pages CSS ---------------------------------------- //?>
  <link rel="stylesheet" type="text/css" href="assets/css/pages/home.css">
  <link rel="stylesheet" type="text/css" href="assets/css/pages/login.css">
  <link rel="stylesheet" type="text/css" href="assets/css/pages/roosterlist.css">
  <link rel="stylesheet" type="text/css" href="assets/css/pages/schedulebuilder.css">
  <link rel="stylesheet" type="text/css" href="assets/css/pages/admin.css">

  <?php // ----------------------------------------- UI Elements CSS ---------------------------------- //?>
  <link rel="stylesheet" type="text/css" href="assets/css/ui/spectrum.css">

  <script type="text/javascript" src="assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="assets/js/cookieHandler.js"></script>

  <?php // ----------------------------------------- UI Elements JS ----------------------------------- //?>
  <script type="text/javascript" src="assets/js/ui/dialog.js"></script>
  <script type="text/javascript" src="assets/js/ui/dialog_large.js"></script>
  <script type="text/javascript" src="assets/js/ui/message.js"></script>
  <script type="text/javascript" src="assets/js/ui/login_dialog.js"></script>
  <script type="text/javascript" src="assets/js/ui/dropdown_menu.js"></script>

  <?php // ----------------------------------------- Pages JS ----------------------------------------- //?>
  <script type="text/javascript" src="pages/home.js"></script>
  <script type="text/javascript" src="pages/login.js"></script>
  <script type="text/javascript" src="pages/roosterlist.js"></script>
  <script type="text/javascript" src="pages/schedulebuilder.js"></script>
  <script type="text/javascript" src="pages/admin.js"></script>

  <script type="text/javascript" src="pages/openPage.js"></script>

  <?php // ----------------------------------------- PHP Head ----------------------------------------- //
		require_once($ini_array['BasePath']."scripts/sql.php");
		$SQL = new SQL($ini_array);

    $packageJson = json_encode($package);
		echo "<script>
      window.package = $packageJson;
		</script>";
	?>
	<title><?php echo $package['name']?></title>
</head>
<body>
  <div class='navbar'>
  	<div class='navContent'>
	    <img id='home_icon' class='app_icon' src='assets/img/app_icon_white.svg'>
  		<span id='UserText'></span>
  	</div>
    <button class='navItem' id='logoutButton'>Log In</button>
  </div>
  <div class='content_view'></div>
  <script type="text/javascript" src="script.js"></script>
</body>
</html>
