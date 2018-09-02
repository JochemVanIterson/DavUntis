function Dialog(id, cancelable=true, message, positiveAction = null, positiveText="Close", negativeAction = null, negativeText="Cancel"){
  var self = this;
  self.dialogobject = $(" \
  <div class='dialog_holder'> \
    <div id="+id+" class='dialog'> \
      <div class=dialog_message>"+message+"</div> \
      <div class=dialog_actionholder> \
        <div class=dialog_negative>"+negativeText+"</div>\
        <div class=dialog_positive>"+positiveText+"</div>\
      </div> \
    </div> \
  </div>");

  self.isShowing = false;

  self.id = id;
  self.cancelable = cancelable;
  self.message = message;

  self.show = function(){
    self.isShowing = true;
    self.dialogobject.find('.dialog_holder').click(function(e){ // cancel action
      if (e.target == this) {
        if(cancelable){
          self.cancel();
        }
      }
    });
    self.dialogobject.find('.dialog_negative').click(function(){ // negative action
      if(negativeAction!==null){
        negativeAction();
      }
      self.cancel();
    });
    self.dialogobject.find('.dialog_positive').click(function(){ // positive action
      positiveAction();
      self.hide();
    });

    if(positiveAction===null){
      self.dialogobject.find('.dialog_actionholder').hide();
      self.dialogobject.find('.dialog_positive').text("");
      self.dialogobject.find('.dialog_positive').unbind("click");
      self.dialogobject.find('.dialog_positive').addClass('dialog_disabled');
    }

    if(negativeAction===null){
      self.dialogobject.find('.dialog_negative').text("");
      self.dialogobject.find('.dialog_negative').unbind("click");
      self.dialogobject.find('.dialog_negative').addClass('dialog_disabled');
    }
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

  self.changeMessage = function(message){
    self.message = message;
    self.dialogobject.find('.dialog_message').text(message);
  }
}
