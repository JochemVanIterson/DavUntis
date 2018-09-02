function LoginPage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class="login_page_container">
      <div class='login_container' id='login'>
        <div class='login_box'>
          <img class='login_icon' src='assets/img/app_icon.svg'>
          <form id='login_data'>
            Username or Email Address<br>
            <input type='text' name='user_login' id='user_login' class='input' value='' size='20'>
            Password<br>
            <input type='password' name='password_login' id='password_login' class='input' value='' size='20'>
          </form>
          <div style='height: 32px'>
            <div class='error_view'></div>
            <button id='login_button'>Log In</button>
          </div>
        </div>
      </div>
    </div>
  `);
  self.view.find('.error_view').hide();
  self.view.find('#login_button').click(function(){
    self.view.find('.error_view').hide();
    if(self.view.find('#user_login').val()==""){
      self.view.find('.error_view').text("Username empty");
      self.view.find('.error_view').show();
      return;
    } else if(self.view.find('#password_login').val()==""){
      self.view.find('.error_view').text("Password empty");
      self.view.find('.error_view').show();
      return;
    }
    if(self.view.find('#user_login').val().substring(0,2)=='##'){
      username = self.view.find('#user_login').val().slice(2);
      password = self.view.find('#password_login').val();
      console.log("attemt admin", username, password);
      loginAdmin(username, password, function(response){
        console.log("login_button", response);
      });
    } else {
      username = self.view.find('#user_login').val();
      password = self.view.find('#password_login').val();
      loginUntis(username, password, function(response){
        console.log("login_button", response);
      });
    }
  });

  self.show = function(){
    console.log("show Login");
    self.parent.append(self.view);
    $('#logoutButton').hide();
  }
  self.unbind = function(){
    console.log("unbind Login");
    $('#logoutButton').show();
  }
  function loginUntis(username, password, done){
    var data = {
      username: username,
      password: password,
      school: 'hku'
    };
    $.post('scripts/actions.php',{
      action: 'untis_login',
      data: data
    },
    function(data, status){
      var response = JSON.parse(data);
      if(response.status!="success"){
        self.view.find('.error_view').text("Username and/or Password wrong");
        self.view.find('.error_view').show();
        return;
      }
      setCookie("schoolname", response.data.schoolname, 10);
      setCookie("JSESSIONID", response.data.JSESSIONID, 10);
      setCookie("username", username, 10);

      $('#logoutButton').text("Log Out");

      openPage('roosterlist');
    });
  }
  function loginAdmin(username, password, done){
    $.post('scripts/actions.php',{
      action: 'admin_login',
      user: username,
      pw: password,
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
        sessionStorage.admin_userdata = JSON.stringify(response.data);

        logindata = getFormObj('login_data');
        logindata.user_login = logindata.user_login.slice(2);
        console.log('logindata', logindata);
        $('#logoutButton').text("Log Out");
        $('#UserText').text(userdata.firstname+" "+userdata.lastname);
        if(userdata.admin==1 && !$("#openAdmin").length){
          $('.navContent').append($("<button class='navItem' id='openAdmin'>Admin</button>"));
          $('.navContent').find('#openAdmin').click(function(){
            openPage('admin');
          });
          openPage('admin');
        }
        done();
      }
    });
  }

}
