<?php
  require_once("../scripts/helpers/jsonparser.php");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
  $package = JsonParser("../package.json");
?>
<head>
  <!-- <link rel="stylesheet" type="text/css" href="assets/css/colors.css"> -->
  <link rel="stylesheet" type="text/css" href="assets/css/install.css">
  <script src="../assets/js/jquery.min.js"></script>
  <script src="assets/js/pw_checker.js"></script>
  <title><?php echo $package['name'] ?> | Install Script</title>
</head>
<body>
  <div class='content_view'>
    <h1><?php echo $package['name'] ?></h1>
    <?php
    	if(isset($_POST['status'])){
    		require("install_script.php");
    	}
    ?>
    <h2>Install Script</h2>
    Dit script is bedoeld om het <?php echo $package['name'] ?> systeem te installeren. Dit script doet de volgende dingen:
    <ul>
    	<li>Creëert de mysql database</li>
    	<li>Maakt de admin user aan</li>
    </ul>

    <form method="post">
       <table
        <!-- MySQL -->
      	<tr>
          <td colspan="3">
            <br>
    		    <strong>MySQL</strong>
  		      <hr>
          </td>
        </tr>
        <?php
          if(isset($install_error['msql_error'])){
          	echo "<tr class='error' id='sql_error'><td colspan='3'>";
          	echo $install_error['msql_error'];
          	echo "</td></tr>";
          }
        ?>
        <tr>
          <td><strong>Database Name</strong></td>
          <td>
          	<?php
          		if(!isset($_POST['msql_db']))$_POST['msql_db']="";
          		echo "<input type='text' name='msql_db' value='".$_POST['msql_db']."'>";
          		if(isset($install_error['msql_db']))echo "<br><span class='error'>".$install_error['msql_db']."</span>";
          	?>
          </td>
          <td>Naam van de database vanwaar de service gaat draaien.</td>
        </tr>

        <tr>
          <td><strong>Database user</strong></td>
          <td>
          	<?php
          		if(!isset($_POST['msql_user']))$_POST['msql_user']="";
          		echo "<input type='text' name='msql_user' value='".$_POST['msql_user']."'>";
          		if(isset($install_error['msql_user']))echo "<br><span class='error'>".$install_error['msql_user']."</span>";
          	?>
          </td>
          <td>MySQL user</td>
        </tr>
        <tr>
        	<td><strong>Database password</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['msql_pwd']))$_POST['msql_pwd']="";
        			echo "<input type='password' name='msql_pwd' value='".$_POST['msql_pwd']."'>";
        			if(isset($install_error['msql_pwd']))echo "<br><span class='error'>".$install_error['msql_pwd']."</span>";
        		?>
        	</td>
        	<td>MySQL wachtwoord</td>
        </tr>
        <tr>
        	<td><strong>Database Host</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['msql_server']))$_POST['msql_server']="localhost";
        			echo "<input type='text' name='msql_server' value='".$_POST['msql_server']."'>";
        			if(isset($install_error['msql_server']))echo "<br><span class='error'>".$install_error['msql_server']."</span>";
        		?>
        	</td>
        	<td>Server waar de MySQL op draait. In de meeste gevallen is dit <span style="background-color: #ddd">localhost</span>.</td>
        </tr>
        <tr>
        	<td><strong>Table Prefix</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['msql_prefix']))$_POST['msql_prefix']=$package['proposed_prefix'];
        			echo "<input type='text' name='msql_prefix' value='".$_POST['msql_prefix']."'>";
        			if(isset($install_error['msql_prefix']))echo "<br><span class='error'>".$install_error['msql_prefix']."</span>";
        		?>
        	</td>
        	<td>Als je slechts een enkele database tot je beschikking hebt, kun je hier een prefix instellen.</td>
        </tr>

        <!-- Admin User -->
        <tr>
          <td colspan="3">
            <br>
            <strong>Admin User</strong>
            <hr>
          </td>
        </tr>
        <tr>
        	<td><strong>First Name</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['admin_firstname']))$_POST['admin_firstname']="";
        			echo "<input type='text' name='admin_firstname' value='".$_POST['admin_firstname']."'>";
        			if(isset($install_error['admin_firstname']))echo "<br><span class='error'>".$install_error['admin_firstname']."</span>";
        		?>
        	</td>
        </tr>
        <tr>
        	<td><strong>Last Name</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['admin_lastname']))$_POST['admin_lastname']="";
        			echo "<input type='text' name='admin_lastname' value='".$_POST['admin_lastname']."'>";
        			if(isset($install_error['admin_lastname']))echo "<br><span class='error'>".$install_error['admin_lastname']."</span>";
        		?>
        	</td>
        </tr>
        <tr>
        	<td><strong>Mail</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['admin_mail']))$_POST['admin_mail']="";
        			echo "<input type='email' name='admin_mail' value='".$_POST['admin_mail']."'>";
        			if(isset($install_error['admin_mail']))echo "<br><span class='error'>".$install_error['admin_mail']."</span>";
        		?>
        	</td>
        </tr>
        <tr>
        	<td><strong>Username</strong></td>
        	<td>
        		<?php
        			if(!isset($_POST['admin_username']))$_POST['admin_username']="";
        			echo "<input type='text' name='admin_username' value='".$_POST['admin_username']."'>";
        			if(isset($install_error['admin_username']))echo "<br><span class='error'>".$install_error['admin_username']."</span>";
        		?>
        	</td>
        </tr>
        <tr>
        	<td><strong>Password</strong></td>
        	<td>
        		<input id="set_password" type="password" name="admin_password"><br>
        	</td>
        	<td>At least 6 characters</td>
        </tr>
        <tr>
        	<td><strong>Password Check</strong></td>
        	<td>
        		<input id="set_password_test" type="password" name="admin_password_test">
        	</td>
        	<td>
        		<div id="set_password_status">Enter password</div>
        	</td>
        </tr>
        <tr>
          <td colspan="3">
            <hr>
            <br>
            <input id="submit_button" type="submit" value="Save">
          </td>
        </tr>
    	</table>
    	<?php
        $BasePath = str_replace("install", "", __DIR__);
        $BaseURL = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $BaseURL = str_replace("install/", "", $BaseURL);
        $RelativeURL = str_replace("install/", "", $_SERVER['REQUEST_URI']);
        echo "<input hidden name='BasePath' value='$BasePath'>";
        echo "<input hidden name='BaseURL' value='$BaseURL'>";
        echo "<input hidden name='RelativeURL' value='$RelativeURL'>";
        echo "<input hidden name='status' value='installing'>";
    	?>
    </form>
  </div>
</body>
