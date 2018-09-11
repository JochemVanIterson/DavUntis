function RawDataPage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class='page_rawdata_container'>

    </div>`);
  self.show = function(){
    console.log("show RawData");
    self.parent.append(self.view);
    self.getData();

  }
  self.unbind = function(){
    console.log("unbind RawData");
  }

  self.getData = function(){
    $.post('scripts/actions.php',{
      action: 'untis_school_classes',
      fresh: true
    },function(data, status){
      console.log("ajax", `data ${data}, status ${status}`);
      $('.page_rawdata_container').text(data);
    });
  }
}
