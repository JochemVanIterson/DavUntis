function Message(id, message, time){
  var self = this;
  self.messageobject = $(" \
  <div id="+id+" class='message'> \
    <div class=message_text>"+message+"</div> \
  </div>");

  self.isShowing = false;

  self.id = id;
  self.message = message;
  self.time = time;

  self.show = function(){
    self.isShowing = true;
    self.messageobject.click(function(e){ // cancel action
      if (e.target == this) {
        self.hide();
      }
    });
    // TODO lijst van messages ipv vervangen
    $('.message').remove();
    $('body').append(self.messageobject);
    self.messageobject.fadeIn(150, "swing");
    setTimeout(function() {
      self.hide();
    }, time);
  }
  self.hide = function(){
    self.messageobject.fadeOut(100, "swing", function(){
      self.messageobject.remove();
      self.isShowing = false;
    });
  }
}
