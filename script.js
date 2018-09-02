if(getCookie('admin_username')!="" && sessionStorage.admin_userdata!=null){ // reload admin ui
  userdata = JSON.parse(sessionStorage.admin_userdata);
  if(getCookie('admin_username') === userdata.username){
    $('#logoutButton').text("Log Out");
    $('#UserText').text(userdata.firstname+" "+userdata.lastname);
  } else {
    userdata = null;
    sessionStorage.removeItem('admin_username');
  }
} else {
  userdata = null;
  sessionStorage.removeItem('admin_username');
}

$('#logoutButton').click(function(){
  console.log("logout", getCookie('admin_username'), sessionStorage.admin_userdata);
  if(getCookie('username')!=""){
    console.log("user");
    removeCookie('JSESSIONID');
    removeCookie('schoolname');
    removeCookie('username');

    openPage('home');
    $('#logoutButton').text("Log In");
    return;
  }
  if(getCookie('admin_username')!="" && sessionStorage.admin_userdata!=null){
    removeCookie('admin_username');
    sessionStorage.removeItem('admin_username');

    openPage('home');
    $('#logoutButton').text("Log In");
    return;
  }
  console.log("login");
  openPage('login');
});

$('#home_icon').click(function(){
  openPage('home');
});

openPage('home');
