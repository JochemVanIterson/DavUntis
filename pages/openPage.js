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
    document.title = `${window.package.name}`;
  } else if(page=="login"){
    currentPage = new LoginPage(parent);
    document.title = `${window.package.name} | Login`;
  } else if(page=="roosterlist"){
    currentPage = new RoosterListPage(parent);
    document.title = `${window.package.name} | Personal Schedules`;
  } else if(page=="schedulebuilder"){
    currentPage = new ScheduleBuilderPage(parent);
    document.title = `${window.package.name} | Schedule Builder`;
  } else if(page=="admin"){
    currentPage = new AdminPage(parent);
    document.title = `${window.package.name} | Admin`;
  }
  currentPage.show();
}
