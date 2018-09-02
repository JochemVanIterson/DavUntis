function RoosterListPage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class='page_roosterlist_container'>

    </div>`);
  self.show = function(){
    console.log("show RoosterList");
    self.parent.append(self.view);
  }
  self.unbind = function(){
    console.log("unbind RoosterList");
  }
}
