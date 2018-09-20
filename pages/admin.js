function AdminPage(parent){
  var self = this;
  self.parent = parent;

  // ----------------------------------- Common UI ---------------------------------------------- //
  self.view = $(`
    <div class='admin_view'>
    	<div class='admin_selectors unselectable'>
    		<div class='admin_selector_dd'>
    			Users
    		</div>
    		<table class='admin_selectors_table'>
          <tr class='admin_selector_itm Selected' id='Dash'>
            <td class='selector_icon'><object type='image/svg+xml' data='assets/img/icon_dash.svg' alt='Dash'></td>
            <td class='selector_text'>
              Dashboard
            </td>
          </tr>
    			<tr class='admin_selector_itm' id='Users'>
    				<td class='selector_icon'><object type='image/svg+xml' data='assets/img/icon_users.svg' alt='Users'></td>
    				<td class='selector_text'>
    					Users
    				</td>
    			</tr>
          <tr class='admin_selector_itm' id='Untis'>
    				<td class='selector_icon'><object type='image/svg+xml' data='assets/img/icon_untis.svg' alt='Untis'></td>
    				<td class='selector_text'>
    					Untis Settings
    				</td>
    			</tr>
          <tr class='admin_selector_itm' id='Database'>
    				<td class='selector_icon'><object type='image/svg+xml' data='assets/img/icon_database.svg' alt='Database'></td>
    				<td class='selector_text'>
    					Database
    				</td>
    			</tr>
          <tr class='admin_selector_itm' id='System'>
    				<td class='selector_icon'><object type='image/svg+xml' data='assets/img/icon_system.svg' alt='System'></td>
    				<td class='selector_text'>
    					System
    				</td>
    			</tr>
    		</table>
    	</div>
  	<div class='admin_page'/>`);

  // Bind view handlers
  self.view.find('.admin_selector_dd').click(function(){
    self.view.find('.admin_selectors_table').slideToggle("fast");
    //$('.admin_selector_sub').show();
  });
  self.view.find('.admin_selector_itm').click(function(){
    var selected = $(this).attr('id');
    if(self.view.find('.admin_selector_dd').is(":visible")){
      self.view.find('.admin_selectors_table').slideUp("fast");
    } else {
      self.view.find('.admin_selector_sub').not('.child_of_'+selected).slideUp("fast");
      self.view.find('.child_of_'+selected).slideDown("fast");
    }
    self.show(selected);
  });
  self.view.find('.admin_selector_sub').click(function(){
    var id = $(this).attr('id');
    var parent = id.split("_")[0];
    var selected = id.split("_")[1];

    if(self.view.find('.admin_selector_dd').is(":visible")){
      self.view.find('.admin_selectors_table').slideUp("fast");
    }
    self.show(parent, selected, parent);
  });
  self.view.find(".admin_selectors").mouseenter(function() {
    if ($(window).width() > 500 && $(window).width() < 1200) {
      $('.selector_text').show();
    }
    else $('.selector_text').show();
  });
  self.view.find(".admin_selectors").mouseleave(function() {
    if ($(window).width() > 500 && $(window).width() < 1200) {
      $('.selector_text').hide();
    }
    else $('.selector_text').show();
  });
  self.view.find(window).resize(function() {
    if ($(window).width() > 500 && $(window).width() < 1200) {
      self.view.find('.admin_selectors_table').show();
      self.view.find('.selector_text').hide();
    } else {
      self.view.find('.selector_text').show();
    }
  });

  // ----------------------------------- Dynamic PageData --------------------------------------- //
  self.show = function(page='Dash', sortmode=null){
    $('#openAdmin').text("Back");
    self.view.find('.admin_page').empty();

    pageView = null;
    switch(page){
      case 'Dash':
        pageView = self.pageDash();
        break;
      case 'Users':
        pageView = self.pageUsers(sortmode);
        break;
      case 'Untis':
        pageView = self.pageUntis();
        break;
      case 'Database':
        pageView = self.pageDatabase();
        break;
      case 'System':
        pageView = self.pageSystem();
        break;
      default:
        pageView = self.pageDash();
    }
    self.view.find('.admin_page').append(pageView);
    self.view.find('.admin_selector_itm.Selected').removeClass('Selected');
    self.view.find('.admin_selector_itm#'+page).addClass('Selected');
    self.parent.append(self.view);

    $.post('scripts/actions.php',{
      action: 'admin_pagedata',
      page: page,
      raw: true
    },
    function(data, status){
      var response = JSON.parse(data);
      if(response.page=="Users"){ // ---------------------------------------------- PageData Users ---------------------------------------------- //
        // --------- Default sorting --------- //
        response.data.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );

        // --------- Sort array --------- //
        switch(sortmode){
          case 'firstname':
            response.data.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
            break;
          case 'lastname':
            response.data.sort(function(a,b) {return (a.lastname > b.lastname) ? 1 : ((b.lastname > a.lastname) ? -1 : 0);} );
            break;
          case 'username':
            response.data.sort(function(a,b) {return (a.username > b.username) ? 1 : ((b.username > a.username) ? -1 : 0);} );
            break;
          default:
            response.data.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
        }

        response.data.forEach(function(itm){
          if(itm.admin==="1"){
            var isadmin = "admin";
          } else {
            var isadmin = "";
          }
          itmView = $(`<div class='list_element' id='${itm.id}'>
            <div class='unselectable header ${isadmin}'>
              ${itm.firstname} ${itm.lastname}
            </div>
            <div class='content'>
              <table class='UserForm'>
                <input type='hidden' name='username' value='${itm.username}'>
                <tr>
                  <td class='unselectable'>User Name</td>
                  <td class='unselectable'>${itm.username}</td>
                </tr>
                <tr>
                  <td class='unselectable'>Admin</td>
                  <td>
                    <label class='switch'>
                      <input ${isadmin?'checked':''} type='checkbox' name='admin'>
                      <span class='slider round'></span>
                    </label>
                  </td>
                </tr>
                <tr>
                  <td class='unselectable'>First Name</td>
                  <td style='padding:0px;'><input type='text' name='firstname' value='${itm.firstname}'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Last Name</td>
                  <td style='padding:0px;'><input type='text' name='lastname' value='${itm.lastname}'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Mail</td>
                  <td style='padding:0px;'><input type='text' name='mail' value='${itm.mail}'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Password</td>
                  <td style='padding:0px;'><input type='password' name='password' placeholder='Change Password'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Last Login</td>
                  <td class='unselectable'>${itm.last_login}</td>
                </tr>
                <tr>
                  <td style='padding-right:0px;' align='right' colspan='2'>
                    <button class='removeButton' type='submit'>Remove</button>
                    <button class='saveButton' type='submit'>Save</button>
                  </td>
                </tr>
              </table>
            </div>
          </div>`);
          itmView.find('.saveButton').click(function(){
            inputs = $(this).closest('.UserForm').find('input');
            fields = {};
            inputs.each(function(index){
              if($(this).attr('type')=="checkbox"){
                fields[$(this).attr("name")] = $(this).prop('checked');
              } else {
                fields[$(this).attr("name")] = $(this).val();
              }
            });
            EditDialog = new Dialog('UpdateUser', true, `Save user <span>${fields.firstname} ${fields.lastname}</span>?`, function(){
              $.post('scripts/actions.php',{
                action: 'user',
                sql_action: 'update',
                fields: fields
              },
              function(data, status){
                data = JSON.parse(data);
                if(data.status=='success'){
                  self.show('Users');
                }
              });
            }, "Save", function(){
              // self.show('Users');
            }, "Cancel");
            EditDialog.show();
          });
          itmView.find('.removeButton').click(function(){
            inputs = $(this).closest('.UserForm').find('input');
            fields = {};
            inputs.each(function(index){
              if($(this).attr('type')=="checkbox"){
                fields[$(this).attr("name")] = $(this).prop('checked');
              } else {
                fields[$(this).attr("name")] = $(this).val();
              }
            });
            RemoveDialog = new Dialog('RemoveUser', true, `Remove user <span>${fields.firstname} ${fields.lastname}</span>?`, function(){
              $.post('scripts/actions.php',{
                action: 'user',
                sql_action: 'remove',
                username: fields.username
              },
              function(data, status){
                data = JSON.parse(data);
                if(data.status=='success'){
                  self.show('Users');
                }
              });
            }, "Save", function(){
              // self.show('Users');
            }, "Cancel");
            RemoveDialog.show();
          });
          $('.list_element#AddUser').before(itmView);
        });
        $('.list_element > .header').click(function(){
      		$(this).closest('.list_element').find('.content').slideToggle("fast");
      	});
      }
      if(response.page=="Untis"){ // ---------------------------------------------- PageData Untis ---------------------------------------------- //
        console.log('pagedata', response.data);
        response.data.dis_departments = JSON.parse(response.data.dis_departments);
        $('#untis_url').attr('placeholder', 'empty');
        if(response.data.untis_url!=null){
          $('#untis_url').val(response.data.untis_url);
        }

        $('#untis_school').attr('placeholder', 'empty');
        if(response.data.untis_school!=null){
          $('#untis_school').val(response.data.untis_school);
        }

        if(response.data.untis_sync_before!=null){
          $('select#untis_sync_before').val(response.data.untis_sync_before.replace(/ /g,'_'));
        }

        if(response.data.untis_sync_after!=null){
          $('select#untis_sync_after').val(response.data.untis_sync_after.replace(/ /g,'_'));
        }

        response.data.dummyUsers.forEach(function(DummyUserObject){
          view.find('#dummy_table_header').after($(`<tr class='dummy_row' id='dummy_${DummyUserObject.id}'>
            <td><input class='dummy_username' placeholder='Username' value='${DummyUserObject.username}'></td>
            <td><input class='dummy_password' placeholder='Unchanged' type='password'></td>
            <td class='dummy_removebutton'>X</td>
          </tr>`));
        });
        departmentsSorted = Object.values(response.data.departments);
        departmentsSorted.sort(function(a,b) {
          if (a.name < b.name)
            return -1;
          if (a.name > b.name)
            return 1;
          return 0;
        });
        departmentsSorted.forEach(function(DepartmentObject){
          view.find('#department_list').append($(`
            <div class='department_itm' id='dep_${DepartmentObject.id}'>
              ${DepartmentObject.name}
            </div>`
          ));
        });
        response.data.dis_departments.forEach(function(itm){
          view.find('#department_list').find('#dep_'+itm).addClass('selected');
        });
        view.find('.department_itm').click(function(){
          $(this).toggleClass('selected');
        });

        view.find('#untis_dummy_table').find('.dummy_removebutton').click(function(){
          parent = $(this).closest('.dummy_row');
          id = parent.attr('id').replace(/dummy_/g,'');
          name = parent.find('.dummy_username').val()
          SuccessDialog = new Dialog('DummyUserRemove', true, `Remove user ${name}?`, function(){
            $.post('scripts/actions.php',{
              action: 'dummyUser',
              sql_action: 'remove',
              id:id
            },
            function(data, status){
              data = JSON.parse(data);
              if(data.status=='success'){
                self.show('Untis');
              }
            });
          }, 'Remove', function(){}, 'Cancel');
          SuccessDialog.show();
        });
        console.log(response.data);
      }
      if(response.page=="Database"){

      }
    });
  }
  self.unbind = function(){
    console.log("unbind Admin");
    $('#openAdmin').text("Admin");
  }

  // ----------------------------------- Static PageData ---------------------------------------- //
  self.pageDash = function(){ // -------------------------------------------------- Page Dashboard ---------------------------------------------- //
    view = $(`<div>
      Dash div<br>
      TODO:
        <ul>
          <li>Dashboard page</li>
          <li>Database page</li>
          <li>System page</li>
        </ul>
    </div>`);
    return view;
  }
  self.pageUsers = function(sortmode = null){ // ---------------------------------- Page Users -------------------------------------------------- //
    view = $(`<div class='admin_page_users'></div>`);

    actionsView = $(`<div class='actions'>
      <label for='sort'>Sort</lable>
      <select id='sort' class='native-dropdown'>
        <option value='firstname' selected>First name</option>
        <option value='lastname'>Last name</option>
        <option value='username'>User name</option>
      </select>
    </div>`);
    actionsView.find('#sort').change(function(){
      console.log("sort");
      self.show('Users', this.value);
    });
    if(sortmode!=null){
      actionsView.find('#sort').val(sortmode);
    }
    view.append(actionsView);

    itmAdd = $(`<div class='list_element' id='AddUser'>
			<div class='unselectable header'>Add User</div>
        <div class='content'>
					<table class='UserForm'>
						<tr>
							<td class='unselectable'>User Name</td>
							<td style='padding:0px;'><input type='text' name='username'></td>
						</tr>
						<tr>
							<td class='unselectable'>Admin</td>
							<td>
								<label class='switch'>
									<input type='checkbox' name='admin'>
                  <span class='slider round'></span>
								</label>
							</td>
						</tr>
						<tr>
							<td class='unselectable'>First Name</td>
							<td style='padding:0px;'><input type='text' name='firstname'></td>
						</tr>
						<tr>
							<td class='unselectable'>Last Name</td>
							<td style='padding:0px;'><input type='text' name='lastname'></td>
						</tr>
						<tr>
							<td class='unselectable'>Mail</td>
							<td style='padding:0px;'><input type='text' name='mail'></td>
						</tr>
						<tr>
							<td class='unselectable'>Password</td>
							<td style='padding:0px;'><input type='password' name='password'></td>
						</tr>
						<tr>
							<td style='padding-right:0px;' align='right' colspan='2'>
								<button class='saveButton' id='addUser'>Save</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
    </div>`);
    itmAdd.find('.saveButton').click(function(){
      inputs = $(this).closest('.UserForm').find('input');
      fields = {};
      inputs.each(function(index){
        if($(this).attr('type')=="checkbox"){
          fields[$(this).attr("name")] = $(this).prop('checked');
        } else {
          fields[$(this).attr("name")] = $(this).val();
        }
      });
      $.post('scripts/actions.php',{
        action: 'user',
        sql_action: 'insert',
        fields: fields
      },
      function(data, status){
        data = JSON.parse(data);
        if(data.status=='success'){
          SuccessDialog = new Dialog('InsertUser', true, `User <span>${fields.username}</span> added`, function(){
            self.show('Users');
          });
          SuccessDialog.show();
        }
      });
    });
    view.append(itmAdd);
    return view;
  }
  self.pageUntis = function(){ // ------------------------------------------------- Page Untis -------------------------------------------------- //
    view = $(`<div class='admin_page_untis'>
      <div class='untis_settings_holder'>
        <div id='title'>Untis setup</div>
        <table id='untis_setup_table'>
          <tr>
            <td>Server url</td>
            <td><input id='untis_url'></td>
          </tr>
          <tr>
            <td>Schoolname</td>
            <td><input id='untis_school'></td>
          </tr>
          <tr>
            <td>Sync before</td>
            <td>
              <select id='untis_sync_before' class='custom-select'>
                <option selected disabled>Select</option>
                <option value='1_week'>1 week</option>
                <option value='2_week'>2 weeks</option>
                <option value='3_week'>3 weeks</option>
                <option value='4_week'>1 month</option>
                <option value='6_week'>1.5 month</option>
                <option value='8_week'>2 months</option>
                <option value='10_week'>2.5 months</option>
                <option value='12_week'>3 months</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Sync after</td>
            <td>
              <select id='untis_sync_after' class='custom-select'>
                <option selected disabled>Select</option>
                <option selected disabled>Select</option>
                <option value='1_week'>1 week</option>
                <option value='2_week'>2 weeks</option>
                <option value='3_week'>3 weeks</option>
                <option value='4_week'>1 month</option>
                <option value='6_week'>1.5 month</option>
                <option value='8_week'>2 months</option>
                <option value='10_week'>2.5 months</option>
                <option value='12_week'>3 months</option>
              </select>
            </td>
          </tr>
          <tr id='actions'>
            <td colspan=2>
              <button id='saveUntisSetup' class='saveButton'>Save</button>
            </td>
          </tr>
        </table>
      </div>
      <div class='untis_dummy_holder'>
        <div id='title'>Untis dummy users</div>
        <table id='untis_dummy_table'>
          <tr id='dummy_table_header'>
            <th>username</th>
            <th>password</th>
            <th></th>
          </tr>
          <tr id='new_dummy_row'>
            <td colspan=3 id='addDummyUser'>
              New User
            </td>
          </tr>
          <tr id='actions'>
            <td colspan=3>
              <button id='saveUntisDummy' class='saveButton'>Save</button>
            </td>
          </tr>
        </table>
      </div>
      <div class='untis_department_selector_holder'>
        <div id='title'>Disable departments</div>
        <div id='department_list'></div>
        <div id='savebutton_holder'>
          <button id='saveDepartmentSelectors' class='saveButton'>Save</button>
        </div>
      </div>
    </div>`);
    newcounter = 0;
    view.find('#addDummyUser').click(function(){
      view.find('#new_dummy_row').before($(`<tr class='new_row' id='new_${newcounter++}'>
        <td><input class='dummy_username' placeholder='Username'></td>
        <td><input class='dummy_password' placeholder='Password' type='password'></td>
        <td class='dummy_removebutton'>X</td>
      </tr>`));
    });
    view.find('#saveUntisSetup').click(function(){
      $.post('scripts/actions.php',{
        action: 'untis',
        sql_action: 'update',
        fields: {
          untis_url: view.find('input#untis_url').val(),
          untis_school: view.find('input#untis_school').val(),
          untis_sync_before: view.find('select#untis_sync_before').val().replace(/_/g,' '),
          untis_sync_after: view.find('select#untis_sync_after').val().replace(/_/g,' ')
        }
      },
      function(data, status){
        data = JSON.parse(data);
        console.log("saveUntisSetup", data);
        if(data.status=='success'){
          SuccessDialog = new Dialog('UpdateUntis', true, `Settings saved`, function(){
            self.show('Untis');
          });
          SuccessDialog.show();
        }
      });
    });
    view.find('#saveUntisDummy').click(function(){
      var updateItms = [];
      var newItms = [];
      view.find('#untis_dummy_table').find('tr.dummy_row').each(function(){
        id = $(this).attr('id').replace(/dummy_/g,'');
        username = $(this).find('.dummy_username').val();
        password = $(this).find('.dummy_password').val();
        updateItms.push({'id':id, 'username': username, 'password': password});
      });
      view.find('#untis_dummy_table').find('tr.new_row').each(function(){
        username = $(this).find('.dummy_username').val();
        password = $(this).find('.dummy_password').val();
        newItms.push({'username': username, 'password': password});
      });

      $.post('scripts/actions.php',{
        action: 'dummyUser',
        sql_action: 'saveMix',
        fields:{
          insert: JSON.stringify(newItms),
          update: JSON.stringify(updateItms)
        }
      },
      function(data, status){
        data = JSON.parse(data);
        if(data.status=='success'){
          SuccessDialog = new Dialog('DummyUserSaved', true, `Dummy users saved`, function(){
            self.show('Untis');
          });
          SuccessDialog.show();
        }
      });
    });
    view.find('#saveDepartmentSelectors').click(function(){
      var ids = [];
      view.find('#department_list').find('.department_itm.selected').each(function(){
        id = $(this).attr('id').replace(/dep_/g,'');
        ids.push(id);
      });
      console.log(ids);
      $.post('scripts/actions.php',{
        action: 'untis',
        sql_action: 'dis_departments',
        fields: ids
      },
      function(data, status){
        data = JSON.parse(data);
        if(data.status=='success'){
          SuccessDialog = new Dialog('DepartmentsSaved', true, `Disabled departments saved`, function(){
            self.show('Untis');
          });
          SuccessDialog.show();
        }
      });
    });
    return view;
  }
  self.pageDatabase = function(){ // ------------------------------------------------ Page System ------------------------------------------------- //
    view = $(`<div>Database
      TODO:
        <ul>
          <li>Global UI</li>
          <li>Global sync button</li>
          <li>
            Sync button per type <ul>
              <li>departments</li>
              <li>classes</li>
              <li>subjects</li>
              <li>periods</li>
              <li>etc.</li>
            </ul>
          </li>
          <li>Info about data</li>
        </ul><br>
      <button id='UpdateDbButton' class='button'>Update DB</button>
    </div>`);
    view.find('#UpdateDbButton').click(function(){
      types = ['init', 'schoolclasses', 'teachers', 'subjects', 'rooms', 'periods'];
      updateDataBase(types, null);
    });
    return view;
  }
  self.pageSystem = function(){ // ------------------------------------------------ Page System ------------------------------------------------- //
    view = $(`<div>
      TODO:
        <ul>
          <li>time sinds boot</li>
          <li>shutdown/reboot/etc...</li>
          <li>WiFi</li>
          <li>
            Backup<ul>
              <li>√ MySQL</li>
              <li>Project</li>
              </ul>
          </li>
        </ul><br>
      <button id='SQLDumpButton'>Dump SQL Data</button>
    </div>`);
    view.find('#SQLDumpButton').click(function(){
      SQLDumpDialog = new Dialog('SQLDump', true, `Export SQL data?`, function(){
        alert("no action configured yet");
        // socket.emit('export_sql');
        // socket.on('export_sql_r', function(data){
        //   console.log(data.dump.data);
        //   saveFile(data.dump.data, 'mysql_dump.sql');
        // });
        console.log("export_sql button");
      }, "Yes", function(){}, "Cancel");
      SQLDumpDialog.show();
    });
    return view;
  }

  // ----------------------------------- Helpers ------------------------------------------------ //
  var saveFile = function () {
    var a = document.createElement("a");
    document.body.appendChild(a);
    a.style = "display: none";
    return function (data, name) {
      var blob = new Blob([data]);
      var url = window.URL.createObjectURL(blob);
      a.href = url;
      a.download = name;
      a.click();
      window.URL.revokeObjectURL(url);
    };
  }();
  var updateDataBase = function(typesArray, sessiondata){
    options = {
      action: 'updateDB',
      type: typesArray[0],
      sessiondata: sessiondata
    }
    console.log('updateDB options', options);
    $.post('scripts/actions.php', options,
    function(data, status){
      data = JSON.parse(data);
      if(typesArray[0]=='init')sessiondata = data.sessionData;
      console.log('updateDB', typesArray[0], data);
      typesArray.shift();
      if(typesArray.length>0){
        updateDataBase(typesArray, sessiondata);
      }
    });
  }
}
