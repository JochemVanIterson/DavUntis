function AdminPage(parent){
  var self = this;
  self.parent = parent;

  // ----------------------------------- Common UI ---------------------------------------------- //
  self.view = $(`<div class='admin_view'>
  	<div class='admin_selectors unselectable'>
  		<div class='admin_selector_dd'>
  			Users
  		</div>
  		<table class='admin_selectors_table'>
        <tr class='admin_selector_itm Selected' id='Dash'>
          <td class='selector_icon'><object type='image/svg+xml' data='assets/images/icon_dash.svg' alt='Users'></td>
          <td class='selector_text'>
            Dashboard
          </td>
        </tr>
        <tr class='admin_selector_itm' id='Groups'>
  				<td class='selector_icon'><object type='image/svg+xml' data='assets/images/icon_group.svg' alt='Groups'></td>
  				<td class='selector_text'>
  					Groups
  				</td>
  			</tr>
        <tr class='admin_selector_itm' id='Children'>
  				<td class='selector_icon'><object type='image/svg+xml' data='assets/images/icon_children.svg' alt='Children'></td>
  				<td class='selector_text'>
  					Children
  				</td>
  			</tr>
        <tr class='admin_selector_itm' id='Credits'>
  				<td class='selector_icon'><object type='image/svg+xml' data='assets/images/icon_credits.svg' alt='Credits'></td>
  				<td class='selector_text'>
  					Credits
  				</td>
  			</tr>
  			<tr class='admin_selector_itm' id='Users'>
  				<td class='selector_icon'><object type='image/svg+xml' data='assets/images/icon_users.svg' alt='Users'></td>
  				<td class='selector_text'>
  					Users
  				</td>
  			</tr>
        <tr class='admin_selector_itm' id='System'>
  				<td class='selector_icon'><object type='image/svg+xml' data='assets/images/icon_system.svg' alt='System'></td>
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
      console.log('hide selector_text');
    } else {
      self.view.find('.selector_text').show();
    }
  });

  // ----------------------------------- Dynamic PageData --------------------------------------- //
  self.show = function(page='Dash', sortmode=null){
    $('#openAdmin').text("Back");
    self.view.find('.admin_page').empty();
    $('.sp-container').remove(); // Remove the color chooser dialogs

    pageView = null;
    switch(page){
      case 'Dash':
        pageView = self.pageDash();
        break;
      case 'Groups':
        pageView = self.pageGroups();
        break;
      case 'Children':
        pageView = self.pageChildren(sortmode);
        break;
      case 'Credits':
        pageView = self.pageCredits(sortmode);
        break;
      case 'Users':
        pageView = self.pageUsers(sortmode);
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
    socket.emit("admin_pagedata", page);
    socket.on("admin_pagedata_r", function(response){
      console.log("admin_pagedata_r", response);
      if(response.page=="Groups"){ // --------------------------------------------- PageData Groups --------------------------------------------- //
        console.log("response.data", response.data);
        response.data.sort(function(a,b) {return (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0);} );
        response.data.forEach(function(itm){
          itmView = $(`<div class='list_element' id='${itm.id}'>
            <div class='unselectable header' style='border-left: 8px solid ${itm.color};'>
              ${itm.name}
            </div>
            <div class='content'>
              <table class='GroupForm'>
                <input type='hidden' name='id' value='${itm.id}'>
                <tr>
                  <td class='unselectable'>Group Name</td>
                  <td style='padding:0px;'><input type='text' name='name' value='${itm.name}'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Color</td>
                  <td style='padding:0px;'>
                    <input class='colorpicker' type='color' name='color' value='${itm.color}'>
                    <span class='colortext'>${itm.color}</span>
                  </td>
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
          itmView.find(".colorpicker").spectrum({
            showPaletteOnly: true,
            togglePaletteOnly: true,
            togglePaletteMoreText: 'more',
            togglePaletteLessText: 'less',
            hideAfterPaletteSelect:true,
            showInitial: true,
            showInput: true,
            preferredFormat: "hex",
            move: function(color) {
                color.toHexString(); // #ff0000
                itmView.find('.colortext').text(color);
            },
            palette: [
                ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
                ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
                ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
                ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
                ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
                ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
                ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
                ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
            ]
          });
          itmView.find(".colorpicker").parent().css('padding-left', "8px");
          itmView.find('.saveButton').click(function(){
            inputs = $(this).closest('.GroupForm').find('input');
            fields = {};
            inputs.each(function(index){
              fields[$(this).attr("name")] = $(this).val();
            });
            console.log("inputs", fields);
            EditDialog = new Dialog('UpdateGroup', true, `Save group <span>${fields.name}</span>?`, function(){
              socket.emit('admin', {'action':"UpdateGroup", 'fields':fields});
              socket.on('admin_r', function(result){
                console.log(result);
                if(result.action=='UpdateGroup'){
                  if(result.status=='success'){
                    self.show('Groups');
                    socket.off('admin_r');
                  }
                }
              });
              console.log("inputs", fields);
            }, "Save", function(){
              self.show('Groups');
            }, "Cancel");
            EditDialog.show();
          });
          itmView.find('.removeButton').click(function(){
            inputs = $(this).closest('.GroupForm').find('input');
            fields = {};
            inputs.each(function(index){
              fields[$(this).attr("name")] = $(this).val();
            });
            console.log("inputs", fields);
            RemoveDialog = new Dialog('RemoveGroup', true, `Remove group <span>${fields.name}</span>?`, function(){
              socket.emit('admin', {'action':"RemoveGroup", 'fields':fields});
              socket.on('admin_r', function(result){
                console.log(result);
                if(result.action=='RemoveGroup'){
                  if(result.status=='success'){
                    self.show('Groups');
                    socket.off('admin_r');
                  }
                }
              });
              console.log("inputs", fields);
            }, "Save", function(){
              // self.show('Groups');
            }, "Cancel");
            RemoveDialog.show();
          });
          $('.list_element#AddGroup').before(itmView);
        });
      } else
      if(response.page=="Children"){ // ------------------------------------------- PageData Children ------------------------------------------- //
        // --------- Default sorting --------- //
        response.groups.sort(function(a,b) {return (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0);} );
        response.data.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );

        // --------- Add Group to child itm --------- //
        response.data.forEach(function(itm){
          itm.group = response.groups.find(obj => {
            return obj.id === itm.groupid
          });
        });

        // --------- Sort array --------- //
        switch(sortmode){
          case 'firstname':
            response.data.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
            break;
          case 'lastname':
            response.data.sort(function(a,b) {return (a.lastname > b.lastname) ? 1 : ((b.lastname > a.lastname) ? -1 : 0);} );
            break;
          case 'group':
            response.data.sort(function(a,b) {return (a.group.name > b.group.name) ? 1 : ((b.group.name > a.group.name) ? -1 : 0);} );
            break;
          default:
            response.data.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
        }

        // --------- Create view --------- //
        response.data.forEach(function(itm){
          itmView = $(`<div class='list_element' id='${itm.id}'>
            <div class='unselectable header' style='border-left: 8px solid ${itm.group.color};'>
              ${itm.firstname} ${itm.lastname}
            </div>
            <div class='content'>
              <table class='ChildForm'>
                <input type='hidden' name='id' value='${itm.id}'>
                <tr>
                  <td class='unselectable'>First Name</td>
                  <td style='padding:0px;'><input type='text' name='firstname' value='${itm.firstname}'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Last Name</td>
                  <td style='padding:0px;'><input type='text' name='lastname' value='${itm.lastname}'></td>
                </tr>
                <tr>
                  <td class='unselectable'>Secret Name</td>
                  <td style='padding:0px;'><input type='text' name='secretname' value='${itm.secretname}'></td>
                </tr>
                <tr>
    							<td class='unselectable'>Group</td>
    							<td style='padding-left:5px;'>
                    <div class="custom-select" style="width:200px;">
                      <select class='select_group' name='groupid'>
                        <option value="" selected>Select Group:</option>
                      </select>
                    </div>
                  </td>
    						</tr>
                <tr>
                  <td class='unselectable'>NFC</td>
                  <td style='padding-left:5px;'>
                    <button class='bind_nfc_button'>Bind NFC</button>
                    <span class='bind_nfc_id'>${(itm.cardid==null)?'No card connected':itm.cardid}</span>
                    <input class='bind_nfc_input' type='hidden' name='cardid' value='${(itm.cardid==null)?"":itm.cardid}'>
                  </td>
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
          response.groups.forEach(function(group){
            itmView.find('.select_group').each(function(){
              if(group.id == itm.groupid){
                $(this).append(`<option value="${group.id}" color="${group.color}" selected>${group.name}</option>`);
              } else {
                $(this).append(`<option value="${group.id}" color="${group.color}">${group.name}</option>`);
              }
            });
          });
          itmView.find('.bind_nfc_button').click(function(){
            BindNFCDialog = new DialogLarge('BindNFCDialog', true, `
              <img class="bind_nfc_icon" src="assets/images/icon_nfc.svg">
              <div class='bind_nfc_message'>Hold NFC Tag near the reader</div>`,
            function(){
              socket.off('nfc_action');
              $('.bind_nfc_input').val(nfcdata.uid);
              $('.bind_nfc_id').text(nfcdata.uid);
              console.log("BindNFC");
            }, "Bind",
            function(){
              socket.off('nfc_action');
              console.log("BindNFC Cancel");
            }, "Close");
            BindNFCDialog.show();
            BindNFCDialog.positiveDisabled(true);
            var nfcdata;
            socket.on('nfc_action', function(data){
              nfcdata = data;
              console.log("nfc data", data);
              BindNFCDialog.positiveDisabled(true);
              if(data.status=="card found"){
                if("child" in data){
                  message = `Tag already connected to child ${data.child.firstname} ${data.child.lastname}<br>Try another tag`;
                } else {
                  message = `Tag found with id '${data.uid}'`;
                  BindNFCDialog.positiveDisabled(false);
                }
                BindNFCDialog.returnview().find('.bind_nfc_message').html(message);
              } else
              if(data.status=="connection lost"){
                message = `Keep tag closer to the reader`;
                BindNFCDialog.returnview().find('.bind_nfc_message').html(message);
              } else
              if(data.status=="card disconnected"){
                message = `Hold NFC Tag near the reader`;
                BindNFCDialog.returnview().find('.bind_nfc_message').html(message);
              }
              // cardMessage = new Message('card_message', data.status, 1000);
              // cardMessage.show();
            });
          });
          itmView.find('.saveButton').click(function(){
            inputs = $(this).closest('.ChildForm').find('input');
            selects = $(this).closest('.ChildForm').find('select');
            fields = {};
            inputs.each(function(index){
              fields[$(this).attr("name")] = $(this).val();
            });
            selects.each(function(index){
              fields[$(this).attr("name")] = $(this).val();
            });
            console.log("inputs", fields);
            EditDialog = new Dialog('UpdateChild', true, `Save child <span>${fields.firstname} ${fields.lastname}</span>?`, function(){
              socket.emit('admin', {'action':"UpdateChild", 'fields':fields});
              socket.on('admin_r', function(result){
                console.log(result);
                if(result.action=='UpdateChild'){
                  if(result.status=='success'){
                    self.show('Children', actionsView.find('#sort').value);
                    socket.off('admin_r');
                  }
                }
              });
              console.log("inputs", fields);
            }, "Save", function(){
              self.show('Children', actionsView.find('#sort').value);
            }, "Cancel");
            EditDialog.show();
          });
          itmView.find('.removeButton').click(function(){
            inputs = $(this).closest('.ChildForm').find('input');
            fields = {};
            inputs.each(function(index){
              fields[$(this).attr("name")] = $(this).val();
            });
            console.log("inputs", fields);
            RemoveDialog = new Dialog('RemoveChild', true, `Remove child <span>${fields.firstname} ${fields.lastname}</span>?`, function(){
              socket.emit('admin', {'action':"RemoveChild", 'fields':fields});
              socket.on('admin_r', function(result){
                console.log(result);
                if(result.action=='RemoveChild'){
                  if(result.status=='success'){
                    self.show('Children', actionsView.find('#sort').value);
                    socket.off('admin_r');
                  }
                }
              });
              console.log("inputs", fields);
            }, "Save", function(){
              // self.show('Groups');
            }, "Cancel");
            RemoveDialog.show();
          });
          $('.list_element#AddChild').before(itmView);
        });
        response.groups.forEach(function(group){
          $('#addchild_groupid').each(function(){
            $(this).append(`<option value="${group.id}" color="${group.color}">${group.name}</option>`);
          });
        });
        init_dropdown_menu($('.custom-select'));
      } else
      if(response.page=="Credits"){ // -------------------------------------------- PageData Credits -------------------------------------------- //
        // --------- Default sorting --------- //
        response.groups.sort(function(a,b) {return (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0);} );
        response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );

        // --------- Add Group to child itm --------- //
        response.children.forEach(function(itm){
          itm.group = response.groups.find(obj => {
            return obj.id === itm.groupid
          });
        });

        // --------- Sort array --------- //
        switch(sortmode){
          case 'firstname':
            response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
            break;
          case 'lastname':
            response.children.sort(function(a,b) {return (a.lastname > b.lastname) ? 1 : ((b.lastname > a.lastname) ? -1 : 0);} );
            break;
          case 'group':
            response.children.sort(function(a,b) {return (a.group.name > b.group.name) ? 1 : ((b.group.name > a.group.name) ? -1 : 0);} );
            break;
          default:
            response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
        }

        // --------- Create view From --------- //
        checkButton = function(){
          console.log({'from':$('.credits_from_list_itm.itm_selected').length>0, 'from_bank':$('#type_from').val()=='bank', 'receiver':$('.credits_receiver_list_itm.itm_selected').length>0, 'receiver_bank':$('#type_receiver').val()=='bank'});
          if((
              $('.credits_from_list_itm.itm_selected').length>0 ||
              $('#type_from').val()=='bank'
            ) && (
              $('.credits_receiver_list_itm.itm_selected').length>0 ||
              $('#type_receiver').val()=='bank'
            )
          ){
            $('#credits_commit_button').removeClass('disabled');
          } else {
            $('#credits_commit_button').addClass('disabled');
          }
        };

        response.children.forEach(function(itm){
          itmView = $(`<tr class='unselectable credits_from_list_itm' id='${itm.id}'>
            <td style='background-color:${itm.group.color};'></td>
            <td>${itm.firstname} ${itm.lastname}</td>
            <td>${itm.creds} Creds</td>
          </tr>`);
          $('#child.credits_from_list').append(itmView);
        });
        console.log('groups', response.groups);
        response.groups.forEach(function(itm){
          itmView = $(`<tr class='unselectable credits_from_list_itm' id='${itm.id}'>
            <td style='background-color:${itm.color};'></td>
            <td>${itm.name}</td>
          </tr>`);
          $('#group.credits_from_list').append(itmView);
        });
        $('.credits_from_list_itm').click(function(){
          $(this).toggleClass('itm_selected');
          checkButton();
        });

        // --------- Create view Receiver --------- //
        response.children.forEach(function(itm){
          itmView = $(`<tr class='unselectable credits_receiver_list_itm' id='${itm.id}'>
            <td style='background-color:${itm.group.color};'></td>
            <td>${itm.firstname} ${itm.lastname}</td>
            <td>${itm.creds} Creds</td>
          </tr>`);
          $('#child.credits_receiver_list').append(itmView);
        });
        response.groups.forEach(function(itm){
          itmView = $(`<tr class='unselectable credits_receiver_list_itm' id='${itm.id}'>
            <td style='background-color:${itm.color};'></td>
            <td>${itm.name}</td>
          </tr>`);
          $('#group.credits_receiver_list').append(itmView);
        });
        $('.credits_receiver_list_itm').click(function(){
          $(this).toggleClass('itm_selected');
          checkButton();
        });


        // --------- Bind Commit button --------- //
        $('#credits_commit_button').click(function(){
          if($(this).hasClass('disabled')){
            return view;
          }
          from_list = view.find('.credits_from_list_itm.itm_selected');
          from_list_data = [];
          from_type = view.find('#type_from').val();
          from_list.each(function(){
            id = $(this).attr('id');
            if(from_type=='group'){
              group = response.children.filter(itm => itm.groupid == id);
              group.forEach(function(child){
                from_list_data.push(child);
              });
            } else if(from_type=='child'){
              child = response.children.find(itm => itm.id == id);
              from_list_data.push(child);
            }
          });

          receiver_list = view.find('.credits_receiver_list_itm.itm_selected');
          receiver_list_data = [];
          receiver_type = view.find('#type_receiver').val();
          receiver_list.each(function(){
            id = $(this).attr('id');
            if(receiver_type=='group'){
              group = response.children.filter(itm => itm.groupid == id);
              group.forEach(function(child){
                receiver_list_data.push(child);
              });
            } else if(receiver_type=='child'){
              child = response.children.find(itm => itm.id == id);
              receiver_list_data.push(child);
            }
          });
          amount = parseInt($('#credits_amount_input').val());
          console.log({"fromList":from_list_data, "receiverList":receiver_list_data, "amount": amount});

          CommitCredsDialog = new DialogLarge('commit_creds_dialog', true, `<div class='commit_creds_dialog_content'>
            <div class='commit_creds_actions unselectable'>
              <div class='before_after_chooser'>
                <span id='before'>Before</span>
                <span id='after' class='selected'>After</span>
              </div>
              <div class='divide_chooser'>
                <span id='divide'>Divide</span>
                <span id='same' class='selected'>Same amount</span>
              </div>
            </div>
            <!--<div class='commit_creds_headers'>
              <div>From</div>
              <div>Receiver</div>
            </div>-->
            <div class='commit_creds_list'>
              <table class='commit_creds_list_from'>
              </table>
              <table class='commit_creds_list_receiver'>
              </table>
            </div>
          </div>`,
          function(){
            console.log("CommitCreds");
            type = CommitCredsDialog.returnview().find('.divide_chooser').find('.selected').attr('id');
            from_before = [];
            from_list_data.forEach(function(itm){
              from_before.push({id:itm.id, creds:itm.creds});
            });
            to_before = [];
            receiver_list_data.forEach(function(itm){
              to_before.push({id:itm.id, creds:itm.creds});
            });
            from_after = [];
            from_list_data.forEach(function(itm){
              if(type=='same'){
                from_after.push({id:itm.id, creds:(Math.round(itm.creds-amount))});
              } else {
                from_after.push({id:itm.id, creds:(Math.round(itm.creds-(amount/from_list_data.length)))});
              }
            });
            to_after = [];
            receiver_list_data.forEach(function(itm){
              if(type=='same'){
                to_after.push({id:itm.id, creds:(Math.round(itm.creds+amount))});
              } else {
                to_after.push({id:itm.id, creds:(Math.round(itm.creds+(amount/receiver_list_data.length)))});
              }
            });
            if(type=='same'){
              from_delta = Math.round(amount);
              to_delta = Math.round(amount);
            } else {
              from_delta = Math.round(amount/from_list_data.length);
              to_delta = Math.round(amount/receiver_list_data.length);
            }
            socket.emit('admin', {
              action:'CommitCreds',
              fields:{
                'from_type': from_type,
                'from_before': from_before,
                'from_after': from_after,
                'from_delta': from_delta,
                'to_type': receiver_type,
                'to_before': to_before,
                'to_after': to_after,
                'to_delta': to_delta,
                'type': type,
                'datetime': 'NOW()'
              }
            });
            socket.on('admin_r', function(result){
              console.log(result);
              if(result.action=='CommitCreds'){
                if(result.status=='success'){
                  self.show('Credits');
                  socket.off('admin_r');
                }
              }
            });
          }, "Commit",
          function(){
            console.log("CommitCreds Cancel");
          }, "Cancel");
          CommitCredsDialog.show();
          fromListView = CommitCredsDialog.returnview().find('.commit_creds_list_from');
          receiverListView = CommitCredsDialog.returnview().find('.commit_creds_list_receiver');
          from_list_data.forEach(function(itm){
            console.log(itm);
            itmView = $(`<tr class='unselectable commit_creds_list_from_itm' id='${itm.id}'>
              <td style='background-color:${itm.group.color};'></td>
              <td>${itm.firstname} ${itm.lastname}</td>
              <td class='creds_before'>${itm.creds} Creds</td>
              <td class='creds_after_s'>${itm.creds-amount} Creds</td>
              <td class='creds_after_d'>${Math.round(itm.creds-(amount/from_list_data.length))} Creds</td>
            </tr>`);
            fromListView.append(itmView);
          });
          if(from_type=='bank'){
            itmView = $(`<tr class='unselectable commit_creds_list_from_itm' id='bank'>
              <td style='background-color:#fff;'></td>
              <td>Bank</td>
              <td>inf Creds</td>
            </tr>`);
            fromListView.append(itmView);
          }

          receiver_list_data.forEach(function(itm){
            console.log(itm);
            itmView = $(`<tr class='unselectable commit_creds_list_receiver_itm' id='${itm.id}'>
              <td style='background-color:${itm.group.color};'></td>
              <td>${itm.firstname} ${itm.lastname}</td>
              <td class='creds_before'>${itm.creds} Creds</td>
              <td class='creds_after_s'>${Math.round(itm.creds+amount)} Creds</td>
              <td class='creds_after_d'>${Math.round(itm.creds+(amount/receiver_list_data.length))} Creds</td>
            </tr>`);
            receiverListView.append(itmView);
          });
          if(receiver_type=='bank'){
            itmView = $(`<tr class='unselectable commit_creds_list_receiver_itm' id='bank'>
              <td style='background-color:#fff;'></td>
              <td>Bank</td>
              <td>inf Creds</td>
            </tr>`);
            receiverListView.append(itmView);
          }

          before_after_chooser_value = "after";
          divide_chooser_value = "same";
          CommitCredsDialog.returnview().find('.before_after_chooser').children().click(function(){
            CommitCredsDialog.returnview().find('.before_after_chooser').children().removeClass('selected');
            $(this).addClass('selected');
            before_after_chooser_value = $(this).attr('id');
            if(before_after_chooser_value=='before'){
              CommitCredsDialog.returnview().find('.creds_before').show();
              CommitCredsDialog.returnview().find('.creds_after_s').hide();
              CommitCredsDialog.returnview().find('.creds_after_d').hide();
            } else if(before_after_chooser_value=='after'){
              CommitCredsDialog.returnview().find('.creds_before').hide();
              if(divide_chooser_value=='same'){
                CommitCredsDialog.returnview().find('.creds_after_s').show();
                CommitCredsDialog.returnview().find('.creds_after_d').hide();
              } else if(divide_chooser_value=='divide'){
                CommitCredsDialog.returnview().find('.creds_after_s').hide();
                CommitCredsDialog.returnview().find('.creds_after_d').show();
              }
            }
            console.log('before_after_chooser', before_after_chooser_value);
          });
          CommitCredsDialog.returnview().find('.divide_chooser').children().click(function(){
            CommitCredsDialog.returnview().find('.divide_chooser').children().removeClass('selected');
            $(this).addClass('selected');
            divide_chooser_value = $(this).attr('id');
            if(before_after_chooser_value=='before')return;
            if(divide_chooser_value=='same'){
              CommitCredsDialog.returnview().find('.creds_after_s').show();
              CommitCredsDialog.returnview().find('.creds_after_d').hide();
            } else if(divide_chooser_value=='divide'){
              CommitCredsDialog.returnview().find('.creds_after_s').hide();
              CommitCredsDialog.returnview().find('.creds_after_d').show();
            }
            console.log('divide_chooser', divide_chooser_value);
          });
          // ---- Default values ---- //
          CommitCredsDialog.returnview().find('.creds_before').hide();
          CommitCredsDialog.returnview().find('.creds_after_s').show();
          CommitCredsDialog.returnview().find('.creds_after_d').hide();
        });

        // --------- Bind Sort handlers --------- //
        $('.sort_from_holder').find("#sort_from").change(function(){
          $('#child.credits_from_list').empty();
          response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
          switch(this.value){
            case 'firstname':
              response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
              break;
            case 'lastname':
              response.children.sort(function(a,b) {return (a.lastname > b.lastname) ? 1 : ((b.lastname > a.lastname) ? -1 : 0);} );
              break;
            case 'group':
              response.children.sort(function(a,b) {return (a.group.name > b.group.name) ? 1 : ((b.group.name > a.group.name) ? -1 : 0);} );
              break;
            default:
              response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
          }
          response.children.forEach(function(itm){
            itmView = $(`<tr class='credits_from_list_itm' id='${itm.id}'>
              <td style='background-color:${itm.group.color};'></td>
              <td>${itm.firstname} ${itm.lastname}</td>
              <td>${itm.creds} Creds</td>
            </tr>`);
            $('#child.credits_from_list ').append(itmView);
          });
          $('.credits_from_list_itm').click(function(){
            $(this).toggleClass('itm_selected');
            checkButton();
          });
          checkButton();
        });
        $('.sort_receiver_holder').find("#sort_receiver").change(function(){
          $('#child.credits_receiver_list').empty();
          response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
          switch(this.value){
            case 'firstname':
              response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
              break;
            case 'lastname':
              response.children.sort(function(a,b) {return (a.lastname > b.lastname) ? 1 : ((b.lastname > a.lastname) ? -1 : 0);} );
              break;
            case 'group':
              response.children.sort(function(a,b) {return (a.group.name > b.group.name) ? 1 : ((b.group.name > a.group.name) ? -1 : 0);} );
              break;
            default:
              response.children.sort(function(a,b) {return (a.firstname > b.firstname) ? 1 : ((b.firstname > a.firstname) ? -1 : 0);} );
          }
          response.children.forEach(function(itm){
            itmView = $(`<tr class='credits_receiver_list_itm' id='${itm.id}'>
              <td style='background-color:${itm.group.color};'></td>
              <td>${itm.firstname} ${itm.lastname}</td>
              <td>${itm.creds} Creds</td>
            </tr>`);
            $('#child.credits_receiver_list').append(itmView);
          });
          $('.credits_receiver_list_itm').click(function(){
            $(this).toggleClass('itm_selected');
            checkButton();
          });
          checkButton();
        });
      } else
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
          if(itm.admin==1){
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
                      <input ${itm.admin?'checked':''} type='checkbox' name='admin'>
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
              fields[$(this).attr("name")] = $(this).val();
            });
            console.log("inputs", fields);
            EditDialog = new Dialog('UpdateUser', true, `Save user <span>${fields.firstname} ${fields.lastname}</span>?`, function(){
              socket.emit('admin', {'action':"UpdateUser", 'fields':fields});
              socket.on('admin_r', function(result){
                console.log(result);
                if(result.action=='UpdateUser'){
                  if(result.status=='success'){
                    self.show('Users');
                    socket.off('admin_r');
                  }
                }
              });
              console.log("inputs", fields);
            }, "Save", function(){
              // self.show('Users');
            }, "Cancel");
            EditDialog.show();
          });
          itmView.find('.removeButton').click(function(){
            inputs = $(this).closest('.UserForm').find('input');
            fields = {};
            inputs.each(function(index){
              fields[$(this).attr("name")] = $(this).val();
            });
            console.log("inputs", fields);
            RemoveDialog = new Dialog('RemoveUser', true, `Remove user <span>${fields.firstname} ${fields.lastname}</span>?`, function(){
              socket.emit('admin', {'action':"RemoveUser", 'fields':fields});
              socket.on('admin_r', function(result){
                console.log(result);
                if(result.action=='RemoveUser'){
                  if(result.status=='success'){
                    self.show('Users');
                    socket.off('admin_r');
                  }
                }
              });
              console.log("inputs", fields);
            }, "Save", function(){
              // self.show('Users');
            }, "Cancel");
            RemoveDialog.show();
          });
          $('.list_element#AddUser').before(itmView);
        });
      } else
      if(response.page=="System"){ // --------------------------------------------- PageData System --------------------------------------------- //
        console.log("response.data", response.data);
        // response.data.forEach(function(itm){
        //   itmView = $(`<div class='list_element' id='${itm.id}'>
        //     <div class='unselectable header'>
        //       ${itm.name}
        //     </div>
        //     <div class='content'>
        //       <table class='GroupForm'>
        //         <tr>
        //           <td class='unselectable'>Group Name</td>
        //           <td style='padding:0px;'><input type='text' name='name' value='${itm.name}'></td>
        //         </tr>
        //         <tr>
        //           <td class='unselectable'>Color</td>
        //           <td style='padding:0px;'><input type='text' name='color' value='${itm.color}'></td>
        //         </tr>
        //         <tr>
        //           <td style='padding-right:0px;' align='right' colspan='2'>
        //             <button class='removeButton type='submit'>Remove</button>
        //             <button class='saveButton type='submit'>Save</button>
        //           </td>
        //         </tr>
        //       </table>
        //     </div>
        //   </div>`);
        //   $('.list_element#AddGroup').before(itmView);
        // });
      }

      $('.list_element > .header').click(function(){
    		$(this).closest('.list_element').find('.content').slideToggle("fast");
    	});
      socket.off('admin_pagedata_r');
    });
  }
  self.unbind = function(){
    console.log("unbind Admin");
    $('#openAdmin').text("Admin");
    socket.off('admin_r');
  }

  // ----------------------------------- Static PageData ---------------------------------------- //
  self.pageDash = function(){ // -------------------------------------------------- Page Dashboard ---------------------------------------------- //
    view = $(`<div>
      Dash div<br>
      TODO:
        <ul>
          <li>Dashboard page</li>
          <li>System page</li>
          <li>√ Spraak</li>
          <li>√ Credits</li>
          <li>√ Edit en remove acties</li>
          <li>√ Groepen</li>
          <li>√ Kleuren onder groepen</li>
          <li>√ Children</li>
          <li>√ Dropdown voor groepen onder Children</li>
          <li>√ Kaart koppelen</li>
        </ul>
    </div>`);
    return view;
  }
  self.pageGroups = function(){ // ------------------------------------------------ Page Groups ------------------------------------------------- //
    view = $(`<div class='admin_page_groups'></div>`);
    itmAdd = $(`<div class='list_element' id='AddGroup'>
			<div class='unselectable header'>Add Group</div>
        <div class='content'>
					<table class='GroupForm'>
						<tr>
							<td class='unselectable'>Group Name</td>
							<td style='padding:0px;'><input type='text' name='name'></td>
						</tr>
						<tr>
							<td class='unselectable'>Group Color</td>
							<td style='padding:0px;'>
                <input class='colorpicker' type='color' name='color' value='#ccc'>
                <span class='colortext'>#ccc</span>
              </td>
						</tr>
            <tr>
							<td style='padding-right:0px;' align='right' colspan='2'>
								<button class='saveButton' id='addGroup'>Save</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
    </div>`);
    itmAdd.find(".colorpicker").spectrum({
      showPaletteOnly: true,
      togglePaletteOnly: true,
      togglePaletteMoreText: 'more',
      togglePaletteLessText: 'less',
      hideAfterPaletteSelect:true,
      showInitial: true,
      showInput: true,
      preferredFormat: "hex",
      move: function(color) {
          color.toHexString(); // #ff0000
          itmAdd.find('.colortext').text(color);
      },
      palette: [
          ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
          ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
          ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
          ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
          ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
          ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
          ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
          ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
      ]
    });
    itmAdd.find(".colorpicker").parent().css('padding-left', "8px");
    itmAdd.find('.saveButton').click(function(){
      inputs = $(this).closest('.GroupForm').find('input');
      fields = {};
      inputs.each(function(index){
        fields[$(this).attr("name")] = $(this).val();
      });
      socket.emit('admin', {'action':"InsertGroup", 'fields':fields});
      socket.on('admin_r', function(result){
        console.log(result);
        if(result.action=='InsertGroup'){
          if(result.status=='success'){
            SuccessDialog = new Dialog('InsertGroup', true, `Group <span>${fields.name}</span> added`, function(){
              self.show('Groups');
              socket.off('admin_r');
            });
            SuccessDialog.show();
          }
        }
      });
      console.log("inputs", fields);
    });
    view.append(itmAdd);
    return view;
  }
  self.pageChildren = function(sortmode = null){ // ------------------------------- Page Children ----------------------------------------------- //
    view = $(`<div class='admin_page_children'></div>`);

    actionsView = $(`<div class='actions'>
      <label for='sort'>Sort</lable>
      <select id='sort' class='native-dropdown'>
        <option value='firstname' selected>First name</option>
        <option value='lastname'>Last name</option>
        <option value='group'>Group</option>
      </select>
    </div>`);
    actionsView.find('#sort').change(function(){
      console.log("sort");
      self.show('Children', this.value);
    });
    if(sortmode!=null){
      actionsView.find('#sort').val(sortmode);
    }
    view.append(actionsView);

    itmAdd = $(`<div class='list_element' id='AddChild'>
			<div class='unselectable header'>Add Child</div>
        <div class='content'>
					<table class='ChildForm'>
						<tr>
							<td class='unselectable'>First Name</td>
							<td style='padding:0px;'><input type='text' name='firstname'></td>
						</tr>
						<tr>
							<td class='unselectable'>Last Name</td>
							<td style='padding:0px;'><input type='text' name='lastname'></td>
						</tr>
            <tr>
							<td class='unselectable'>Secret Name</td>
							<td style='padding:0px;'><input type='text' name='secretname'></td>
						</tr>
            <tr>
              <td class='unselectable'>NFC</td>
              <td style='padding-left:5px;'>
                <button class='bind_nfc_button'>Bind NFC</button>
                <span class='bind_nfc_id'>No card connected</span>
                <input class='bind_nfc_input' type='hidden' name='cardid' value=''>
              </td>
            </tr>
            <tr>
							<td class='unselectable'>Group</td>
							<td style='padding-left:5px;'>
                <div class="custom-select" style="width:200px;">
                  <select class='select_group' id='addchild_groupid' name='groupid'>
                    <option value="" selected>Select Group:</option>
                  </select>
                </div>
              </td>
						</tr>
						<tr>
							<td style='padding-right:0px;' align='right' colspan='2'>
								<button class='saveButton' id='addChild'>Save</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
    </div>`);
    itmAdd.find('.bind_nfc_button').click(function(){
      BindNFCDialog = new DialogLarge('BindNFCDialog', true, `
        <img class="bind_nfc_icon" src="assets/images/icon_nfc.svg">
        <div class='bind_nfc_message'>Hold NFC Tag near the reader</div>`,
      function(){
        socket.off('nfc_action');
        $('.bind_nfc_input').val(nfcdata.uid);
        $('.bind_nfc_id').text(nfcdata.uid);
        console.log("BindNFC");
      }, "Bind",
      function(){
        socket.off('nfc_action');
        console.log("BindNFC Cancel");
      }, "Close");
      BindNFCDialog.show();
      BindNFCDialog.positiveDisabled(true);
      var nfcdata;
      socket.on('nfc_action', function(data){
        nfcdata = data;
        console.log("nfc data", data);
        BindNFCDialog.positiveDisabled(true);
        if(data.status=="card found"){
          if("child" in data){
            message = `Tag already connected to child ${data.child.firstname} ${data.child.lastname}<br>Try another tag`;
          } else {
            message = `Tag found with id '${data.uid}'`;
            BindNFCDialog.positiveDisabled(false);
          }
          BindNFCDialog.returnview().find('.bind_nfc_message').html(message);
        } else
        if(data.status=="connection lost"){
          message = `Keep tag closer to the reader`;
          BindNFCDialog.returnview().find('.bind_nfc_message').html(message);
        } else
        if(data.status=="card disconnected"){
          message = `Hold NFC Tag near the reader`;
          BindNFCDialog.returnview().find('.bind_nfc_message').html(message);
        }
        // cardMessage = new Message('card_message', data.status, 1000);
        // cardMessage.show();
      });
    });
    itmAdd.find('.saveButton').click(function(){
      inputs = $(this).closest('.ChildForm').find('input');
      selects = $(this).closest('.ChildForm').find('select');
      fields = {};
      inputs.each(function(index){
        fields[$(this).attr("name")] = $(this).val();
      });
      selects.each(function(index){
        fields[$(this).attr("name")] = $(this).val();
      });
      socket.emit('admin', {'action':"InsertChild", 'fields':fields});
      socket.on('admin_r', function(result){
        console.log(result);
        if(result.action=='InsertChild'){
          if(result.status=='success'){
            SuccessDialog = new Dialog('InsertChild', false, `Child <span>${fields.firstname} ${fields.lastname}</span> added`, function(){
              self.show('Children', actionsView.find('#sort').value);
              socket.off('admin_r');
            });
            SuccessDialog.show();
          }
        }
      });
      console.log("inputs", fields);
    });
    view.append(itmAdd);
    return view;
  }
  self.pageCredits = function(sortmode = null){ // -------------------------------- Page Credits ------------------------------------------------ //
    view = $(`<div class='admin_page_credits'>
      <div class='credits_from_container'>
        <div class='credits_from_header'>From</div>
        <div class='credits_from_actions'>
          <div class='type_from_holder'>
            <label for='type_from'>Type</lable>
            <select id='type_from' class='native-dropdown'>
              <option value='child' selected>Child</option>
              <option value='group'>Group</option>
              <option value='bank'>Bank</option>
            </select>
          </div>
          <div class='sort_from_holder'>
            <label for='sort_from'>Sort</lable>
            <select id='sort_from' class='native-dropdown'>
              <option value='firstname' selected>First name</option>
              <option value='lastname'>Last name</option>
              <option value='group'>Group</option>
            </select>
          </div>
        </div>
        <div class='credits_from_content'>
          <table id='child' class='credits_from_list'></table>
          <table id='group' class='credits_from_list'></table>
          <div id='bank' class='credits_from_bank'>
            <img class="credits_from_bank_icon" src="assets/images/icon_bank.svg">
            Creds: ∞
          </div>
        </div>
      </div>
      <div class='credits_amount_container'>
        <div class='credits_amount_box'>
          <div class='credits_amount_header'>Amount</div>
          <input type='number' id='credits_amount_input' value='10'><br>
          <button id='credits_commit_button' class='disabled'>Commit</button>
        </div>
      </div>
      <div class='credits_receiver_container'>
        <div class='credits_receiver_header'>To</div>
        <div class='credits_receiver_actions'>
          <div class='type_receiver_holder'>
            <label for='type_receiver'>Type</lable>
            <select id='type_receiver' class='native-dropdown'>
              <option value='child' selected>Child</option>
              <option value='group'>Group</option>
              <option value='bank'>Bank</option>
            </select>
          </div>
          <div class='sort_receiver_holder'>
            <label for='sort_receiver'>Sort</lable>
            <select id='sort_receiver' class='native-dropdown'>
              <option value='firstname' selected>First name</option>
              <option value='lastname'>Last name</option>
              <option value='group'>Group</option>
            </select>
          </div>
        </div>
        <div class='credits_receiver_content'>
          <table id='child' class='credits_receiver_list'></table>
          <table id='group' class='credits_receiver_list'></table>
          <div id='bank' class='credits_receiver_bank'>
            <img class="credits_receiver_bank_icon" src="assets/images/icon_bank.svg">
            Bank
          </div>
        </div>
      </div>
    </div>`);

    view.find('#group.credits_from_list').hide();
    view.find('.credits_from_bank').hide();
    view.find('#group.credits_receiver_list').hide();
    view.find('.credits_receiver_bank').hide();

    view.find('#type_from').change(function(){
      $('#child.credits_from_list').hide();
      $('#group.credits_from_list').hide();
      $('.sort_from_holder').hide();
      $('.credits_from_bank').hide();
      $('.credits_from_list_itm').removeClass("itm_selected");
      switch(this.value){
        case 'child':
          $('#child.credits_from_list').show();
          $('.sort_from_holder').show();
          break;
        case 'group':
          $('#group.credits_from_list').show();
          break;
        case 'bank':
          $('.credits_from_bank').show();
          break;
      };
      console.log("type_from", this.value);
    });
    view.find('#sort_from').change(function(){
      console.log("sort_from", this.value);
    });

    view.find('#type_receiver').change(function(){
      $('#child.credits_receiver_list').hide();
      $('#group.credits_receiver_list').hide();
      $('.sort_receiver_holder').hide();
      $('.credits_receiver_bank').hide();
      $('.credits_receiver_list_itm').removeClass("itm_selected");
      switch(this.value){
        case 'child':
          $('#child.credits_receiver_list').show();
          $('.sort_receiver_holder').show();
          break;
        case 'group':
          $('#group.credits_receiver_list').show();
          break;
        case 'bank':
          $('.credits_receiver_bank').show();
          break;
      };
      console.log("type_receiver", this.value);
    });
    view.find('#sort_receiver').change(function(){
      console.log("sort_receiver", this.value);
    });

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
      socket.emit('admin', {'action':"InsertUser", 'fields':fields});
      socket.on('admin_r', function(result){
        console.log(result);
        if(result.action=='InsertUser'){
          if(result.status=='success'){
            SuccessDialog = new Dialog('InsertUser', true, `User <span>${fields.username}</span> added`, function(){
              self.show('Users');
              socket.off('admin_r');
            });
            SuccessDialog.show();
          }
        }
      });
      console.log("inputs", fields);
    });
    view.append(itmAdd);
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
      <button id='shutdownButton'>Shut Down</button><br>
      <button id='SQLDumpButton'>Dump SQL Data</button>
    </div>`);
    view.find('#shutdownButton').click(function(){
      ShutdownDialog = new Dialog('shutdown', true, `Shutdown?`, function(){
        socket.emit('shutdown');
        console.log("shutdown button");
      }, "Yes", function(){}, "Cancel");
      ShutdownDialog.show();
    });
    view.find('#SQLDumpButton').click(function(){
      SQLDumpDialog = new Dialog('SQLDump', true, `Export SQL data?`, function(){
        socket.emit('export_sql');
        socket.on('export_sql_r', function(data){
          console.log(data.dump.data);
          saveFile(data.dump.data, 'mysql_dump.sql');
        });
        console.log("export_sql button");
      }, "Yes", function(){}, "Cancel");
      SQLDumpDialog.show();
    });
    return view;
  }

  // ----------------------------------- Helpers ------------------------------------------------ //
  var saveFile = (function () {
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
  }());
}
