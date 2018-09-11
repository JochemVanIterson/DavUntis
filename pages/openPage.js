currentPage = new HomePage(parent);
currentPageID = "";

function openPage(page){
  if(page==currentPageID)return;
  currentPageID = page;
  parent = $('.content_view');
  currentPage.unbind();
  parent.empty();
  if(page=="home"){
    currentPage = new HomePage(parent);
  } else if(page=="login"){
    currentPage = new LoginPage(parent);
  } else if(page=="roosterlist"){
    currentPage = new RoosterListPage(parent);
  } else if(page=="schedulebuilder"){
    currentPage = new ScheduleBuilderPage(parent);
  } else if(page=="admin"){
    currentPage = new AdminPage(parent);
  }
  currentPage.show();
}
