var userdata;
var logindata;
function LoginDialog(id){
  var self = this;
  self.dialogobject = $("\
    <div class='dialog_holder'>\
      <div class='login_container' id="+id+">\
        <div class='login_box'>\
          <img class='login_icon' src='assets/img/app_icon.svg'>\
          <form id='login_data'>\
            Username or Email Address<br>\
            <input type='text' name='user_login' id='user_login' class='input' value='' size='20'>\
            Password<br>\
            <input type='password' name='password_login' id='password_login' class='input' value='' size='20'>\
          </form>\
          <div style='height: 32px'>\
            <div class='error_view'></div>\
            <button id='login_button'>Log In</button>\
          </div>\
        </div>\
      </div>\
    </div>");

  self.isShowing = false;

  self.id = id;

  self.show = function(){
    self.isShowing = true;
    self.dialogobject.click(function(e){ // cancel action
      if (e.target == this) {
        self.cancel();
      }
    });
    self.dialogobject.find("#login_button").click(function(){
  		var form_data = getFormObj('login_data');
  		if(form_data.user_login == ""){
  			errorView.text("User Empty");
  			errorView.show();
  			return;
  		}
  		if(form_data.password_login == ""){
  			errorView.text("Password Empty");
  			errorView.show();
  			return;
  		}
  		errorView.hide();
  		attempt_login(self);
  	});

    $('body').append(self.dialogobject);
    $('.dialog_holder').fadeIn(150, "swing");
  }
  self.cancel = function(){
    console.log("cancel");
    self.hide();
  }
  self.hide = function(){
    $('.dialog_holder').fadeOut(100, "swing", function(){
      $('.dialog_holder').remove();
      self.isShowing = false;
    });
  }

  var errorView = self.dialogobject.find(".error_view");

	$(window).keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			attempt_login(self);
			return true;
		}
	});
}

function getFormObj(formId) {
  var formObj = {};
  var inputs = $('#'+formId).serializeArray();
  $.each(inputs, function (i, input) {
      formObj[input.name] = input.value;
  });
  return formObj;
}

function attempt_login(self){
	var form_data = getFormObj('login_data');

  $.post('scripts/actions.php',{
    action: 'admin_login',
    user: form_data.user_login,
    pw: form_data.password_login,
    raw: true
  },function(data, status){
    console.log("ajax", `data ${data}, status ${status}`);
    var response = JSON.parse(data);
    if(response.login == "failed"){
      console.log("Failed" + data);
      var errorView = $(".error_view");
      errorView.text(response.message);
      errorView.show();
    } else {
      console.log("Success" + data);
      setCookie("admin_username", response.data.username, 10);
      setCookie("iv", response.data.iv, 10);
      userdata = response.data;

      logindata = getFormObj('login_data');
      console.log('logindata', logindata);
      $('#logoutButton').text("Log Out");
      $('#UserText').text(userdata.firstname+" "+userdata.lastname);
      // if(userdata.admin==1 && !$("#openAdmin").length){
      //   $('.navContent').append($("<button class='navItem' id='openAdmin'>Admin</button>"));
      // }
      $('.full_screen_error').remove();
      self.hide();
    }
  });
}
