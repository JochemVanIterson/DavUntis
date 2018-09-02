function DialogLarge(id, cancelable=true, content, positiveAction = null, positiveText="Close", negativeAction = null, negativeText="Cancel", disabled=false){
  var self = this;
  self.disabled = disabled;
  self.dialogobject = $(`
  <div class='dialog_holder'>
    <div id=${id} class='dialog_large'>
      <div class=dialog_content>${content}</div>
      <div class=dialog_actionholder>
        <div class=dialog_negative>${negativeText}</div>
        <div class=dialog_positive>${positiveText}</div>
      </div>
    </div>
  </div>`);

  self.isShowing = false;

  self.id = id;
  self.cancelable = cancelable;
  self.content = content;

  self.show = function(){
    self.isShowing = true;
    self.dialogobject.find('.dialog_holder').click(function(e){ // cancel action
      console.log("cancel action");
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
      if(!self.disabled){
        positiveAction();
        self.hide();
      }
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
  self.positiveDisabled = function(status){
    self.disabled = status;
    self.dialogobject.find('.dialog_positive').toggleClass("dialog_positive_disabled", status);
  }

  self.changeContent = function(content){
    self.content = content;
    self.dialogobject.find('.dialog_content').html(content);
  }
  self.returnview = function(){
    return self.dialogobject.find('.dialog_content');
  }
}
