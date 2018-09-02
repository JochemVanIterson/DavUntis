$(document).ready(function() {
	// $(".navbar").find('#logoutButton').click(function(){
	// 	logindialog = new LoginDialog("logindialog");
	// 	logindialog.show();
	// 	// alert("logout", refered);
	// 	// window.open("https://www.youraddress.com","_self");
	// });

// 	$('.admin_selector_dd').click(function(){
// 		$('.admin_selectors_table').slideToggle("fast");
// 		//$('.admin_selector_sub').show();
// 	});
// 	$('.admin_selector_itm').click(function(){
// 		var selected = $(this).attr('id');
// 		if($('.admin_selector_dd').is(":visible")){
// 			$('.admin_selectors_table').slideUp("fast");
// 		} else {
// 			$('.admin_selector_sub').not('.child_of_'+selected).slideUp("fast");
// 			$('.child_of_'+selected).slideDown("fast");
// 		}
// 		openPage(selected);
// 	});
//
// 	$('.admin_selector_sub').click(function(){
// 		var id = $(this).attr('id');
// 		var parent = id.split("_")[0];
// 		var selected = id.split("_")[1];
//
// 		if($('.admin_selector_dd').is(":visible")){
// 			$('.admin_selectors_table').slideUp("fast");
// 		}
// 		openPage(selected, parent);
// 	});
// 	$(".admin_selectors").mouseenter(function() {
// 		if ($(window).width() > 500 && $(window).width() < 1200) {
// 			$('.selector_text').show();
// 		}
// 		else $('.selector_text').show();
// 	});
// 	$(".admin_selectors").mouseleave(function() {
// 		if ($(window).width() > 500 && $(window).width() < 1200) {
// 			$('.selector_text').hide();
// 		}
// 		else $('.selector_text').show();
// 	});
// 	$(window).resize(function() {
// 		if ($(window).width() > 500 && $(window).width() < 1200) {
// 			$('.admin_selectors_table').show();
// 			$('.selector_text').hide();
// 			console.log('hide selector_text');
// 		} else {
// 			$('.selector_text').show();
// 		}
// 	});
// 	openPage(page);
// });

	currentPage = new HomePage(parent);
	currentPageID = "";
	function openPage(page){
	  if(page==currentPageID)return;
	  currentPageID = page;
	  parent = $('.content_view');
	  currentPage.unbind();
	  parent.empty();
	  if(page=="admin"){
	    currentPage = new AdminPage(parent);
	  } else if(page=="home"){
	    currentPage = new HomePage(parent);
	  }
	  currentPage.show();
	}

	$('#openAdmin').click(function(){
	  if(currentPageID!="admin"){
	    openPage("admin");
	  } else {
	    openPage("home");
	  }
	});


// function openPage(page, parent = ""){
// 	if(pageID!=-1 && page=="ArtObjects"){
// 		$('.admin_page').load('admin/ArtObjectEdit.php?included&id='+pageID);
// 	} else {
// 		$('.admin_page').load('admin/'+page+'.php?included');
// 	}
// 	console.log(page);
// 	$('.admin_selector_dd_text').text(page);
// 	$('.admin_selector_itm').removeClass("Selected");
// 	$('.admin_selector_sub').removeClass("Selected");
// 	$('#'+page+'.admin_selector_itm').addClass("Selected");
// 	$('#'+parent+"_"+page+'.admin_selector_sub').addClass("Selected");
// }
});
