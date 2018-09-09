function RoosterListPage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class='page_roosterlist_container'>

    </div>`);
  self.show = function(){
    console.log("show RoosterList");
    self.parent.append(self.view);
    self.getData();

  }
  self.unbind = function(){
    console.log("unbind RoosterList");
  }

  self.getData = function(){
    $.post('scripts/actions.php',{
      action: 'untis_school_classes',
      fresh: true
    },function(data, status){
      console.log("ajax", `data ${data}, status ${status}`);
      $('.page_roosterlist_container').text(data);
    });
  }
}
