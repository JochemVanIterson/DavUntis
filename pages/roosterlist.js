function RoosterListPage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class='page_roosterlist_container'>
      <div class='page_roosterlist_box'>
        <div class='page_roosterlist_title'>Personal schedules</div>

        <div class='rooster_itm' id='new_rooster'>Create new schedule</div>
      </div>
    </div>`);
  self.show = function(){
    console.log("show RoosterList");
    self.view.find('.rooster_itm#new_rooster').click(function(){
      openPage('schedulebuilder');
    });
    self.parent.append(self.view);
    self.getData();
  }
  self.unbind = function(){
    console.log("unbind RoosterList");
  }

  self.getData = function(){
    $.post('scripts/actions.php',{
      action: 'pagedata',
      page: 'roosterlist',
    },function(data, status){
      console.log("ajax", `data ${data}, status ${status}`);
      returnJSON = JSON.parse(data);
      if(returnJSON.error!=null && returnJSON.error=='empty'){
        $('.page_roosterlist_box').find('#new_rooster').before($("<div class='roosterlist_empty'>No schedules</div>"))
        // TODO: build rooster automatisch geladen
      }
    });
  }
}
