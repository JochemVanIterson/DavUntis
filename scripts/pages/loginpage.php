<div class="flex-container">
    <div class="CenteredContent">
		<!--<img class="flex-item TitleImage noselect" src="assets/img/TitleImage.png" alt="HKUntis">-->
		<form action="#" method="post">
			<input class="flex-item" type="hidden" name="school" placeholder="School" value="hku">
			<?php
				if(!isset($UserName)){
					$UserName = "";
				} else {
					$UserName = $_POST['username'];
				}
				echo "<input class='flex-item' type='text' name='username' placeholder='Email' value='$UserName' required><br>";
			?>
			<input class="flex-item" type="password" name="password" placeholder="Password" required><br>
			<?php
				$UserName = (isset($_POST['username'])) ? $_POST['username'] : "";
				if(isset($_GET['LoginError'])){
					if($_GET['LoginError'] == "Ue")echo "<div class='flex-item' id='LoginError'>Username empty</div>";
					if($_GET['LoginError'] == "Pe")echo "<div class='flex-item' id='LoginError'>Password empty</div>";
					if($_GET['LoginError'] == "Uw")echo "<div class='flex-item' id='LoginError'>Username Wrong</div>";
					if($_GET['LoginError'] == "Pw")echo "<div class='flex-item' id='LoginError'>Password Wrong</div>";
					if($_GET['LoginError'] == "Lw")echo "<div class='flex-item' id='LoginError'>Username and/or Password wrong</div>";
				}
			?>
			<input class="flex-item LoginButton" type="submit" value="Login">
			<div class="CookieDiv noselect">Deze site maakt gebruik van cookies om instellingen te bewaren</div>
		</form>
  </div>
</div>
