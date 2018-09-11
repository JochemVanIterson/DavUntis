function ScheduleBuilderPage(parent){
  var self = this;
  self.parent = parent;
  self.view = $(`
    <div class='page_schedulebuilder_container'>
      <div class='page_schedulebuilder_box'>
        <div class='page_schedulebuilder_title'>Schedule Builder</div>
        <div class='page_schedulebuilder_content'></div>
      </div>
      <div class='page_schedulebuilder_footer'>
        <div class='schedulebuilder_footer_content'></div>
        <div class='schedulebuilder_footer_navigation'>
          <button id='schedulebuilder_back'>Back</button>
          <button id='schedulebuilder_next'>Next</button>
        </div>
      </div>
    </div>`);
  self.show = function(page='dep'){
    console.log("show ScheduleBuilder");
    switch (page) {
      case 'dep':
        pageView = self.depPage();
        break;
    }
    self.view.append(pageView);
    self.getData(page);
    self.parent.append(self.view);
  }
  self.unbind = function(){
    console.log("unbind ScheduleBuilder");
  }

  self.depPage = function(){
    view = $(``);
    return view;
  }

  self.getData = function(page){
    $.post('scripts/actions.php',{
      action: 'ScheduleBuilder',
      page: page,
    },function(response, status){
      response = JSON.parse(response);
      if(response.page=='dep'){
        // ----------------------------------------- Sorting and preparing data -------------------------- //
        for(var depID in response.data.departments){
          response.data.departments[depID].schoolclasses = [];
        }
        for(var classID in response.data.schoolclasses){
          schoolclass = response.data.schoolclasses[classID];
          dids = JSON.parse(response.data.schoolclasses[classID].dids);
          dids.forEach(function(depID){
            response.data.departments[depID].schoolclasses.push(schoolclass);
          });
        }

        response.data.dis_departments.forEach(function(itm){
          response.data.departments[itm].status = 'disabled';
        });

        departmentsSorted = Object.values(response.data.departments);
        departmentsSorted.sort(function(a,b) {
          if (a.name < b.name)
            return -1;
          if (a.name > b.name)
            return 1;
          return 0;
        });
        departmentsSorted.forEach(function(department){
          department.schoolclasses.sort(function(a,b) {
            if (a.longname < b.longname)
              return -1;
            if (a.longname > b.longname)
              return 1;
            return 0;
          });
        });
        console.log("ScheduleBuilder", response.data);

        // ----------------------------------------- Create View ----------------------------------------- //
        departmentsSorted.forEach(function(department){
          depView = $(`
            <div class='schedulebuilder_itm' id='dep_${department.id}'>
              <div class='schedulebuilder_itm_header'>
                ${department.name}
              </div>
              <div class='schedulebuilder_itm_content_container'></div>
            </div>`);
          if(department.status=="disabled"){ // -------- if disabled, grey out and ignore clicks -------- //
            depView.find('.schedulebuilder_itm_header').addClass('disabled');
            $('.page_schedulebuilder_content').append(depView);
            return;
          }
          depView.find('.schedulebuilder_itm_header').click(function(){
            $(this).parent().find('.schedulebuilder_itm_content_container').slideToggle();
          });
          department.schoolclasses.forEach(function(schoolclass){
            schoolclassView = $(`
              <div class='schedulebuilder_itm_content_object' id='sc_${schoolclass.id}'>
                ${schoolclass.longname}
              </div>`);
            schoolclassView.click(function(){
              // id = parseInt($(this).attr('id').replace(/sc_/g,''));
              selected = !$(this).hasClass('selected');
              $(this).closest('.page_schedulebuilder_content').find(`.schedulebuilder_itm_content_object[id="sc_${schoolclass.id}"]`).each(function(){
                $(this).toggleClass('selected');
              });
              if(selected){
                footer_itm = $(`<div class='schedulebuilder_footer_itm' id='sc_${schoolclass.id}'>${schoolclass.name}</div>`);
                footer_itm.click(function(){
                  $(`.schedulebuilder_itm_content_object[id="sc_${schoolclass.id}"]`).each(function(){
                    $(this).removeClass('selected');
                  });
                  $(this).remove();
                });
                $('.schedulebuilder_footer_content').append(footer_itm);
              } else {
                $('.schedulebuilder_footer_content').find('#sc_'+schoolclass.id).remove();
              }
            });
            depView.find('.schedulebuilder_itm_content_container').append(schoolclassView);
          });
          $('.page_schedulebuilder_content').append(depView);
        });
      }
    });
  }
}
