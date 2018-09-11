function HomePage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class='page_home_container'>
      <div class='page_home_box'>
        <img id='app_icon' src="assets/img/app_icon.svg">
        <h1 id='app_title'><span class='app_title'></span></h1>
        <article id='app_summary'></article>
        <article>Log in met je WebUntis acountgegevens</article>
        <button id='login_button'>Login</button>
        <footer id='small_text'>
          <span class='app_title'></span> is ontwikkeld door <b><a href='http://www.audioware.nl/blog/'>Jochem van Iterson</a></b><br>
          Dit systeem maakt gebruik van cookies om de sessiedata op te slaan
        </footer>
      </div>
    </div>`);
  self.view.find('#login_button').click(function(){
    openPage('login');
  })
  self.show = function(){
    console.log("show Home");
    self.view.find('.app_title').text(package.name);
    self.view.find('#app_summary').text(package.summary);
    self.parent.append(self.view);
  }
  self.unbind = function(){
    console.log("unbind Home");
  }
}
