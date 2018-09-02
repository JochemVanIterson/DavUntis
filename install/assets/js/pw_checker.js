$(document).ready(function() {
	$("#submit_button").attr('disabled', 'disabled');
	var set_password_element = $("#set_password");
	set_password_element.bind("propertychange change click keyup input paste", function(event){
		// If value has changed...
		if (set_password_element.data('oldVal') != set_password_element.val()) {
			// Updated stored value
			set_password_element.data('oldVal', set_password_element.val());
			if(set_password_element.val().length==0){
				$("#set_password_status").css('background-color','#4CAF50');
				$("#set_password_status").text("Enter password");
				$("#submit_button").attr('disabled', 'disabled');
			} else if(set_password_element.val().length<6){
				$("#set_password_status").css('background-color','red');
				$("#set_password_status").text(set_password_element.val().length + " characters");
				$("#submit_button").attr('disabled', 'disabled');
			} else {
				$("#set_password_status").css('background-color','yellow');
				$("#set_password_status").text(set_password_element.val().length + " characters");
				$("#submit_button").attr('disabled', 'disabled');
			}
			if(set_password_test_element.val().length>=6 && set_password_test_element.val()===set_password_element.val()){
				$("#set_password_status").text('match');
				$("#set_password_status").css('background-color','#4CAF50');
				$("#submit_button").removeAttr('disabled');
			}
		}
	});

	var set_password_test_element = $("#set_password_test");
	set_password_test_element.bind("propertychange change click keyup input paste", function(event){
		// If value has changed...
		if (set_password_test_element.data('oldVal') != set_password_test_element.val()) {
			// Updated stored value
			set_password_test_element.data('oldVal', set_password_test_element.val());
			if(set_password_test_element.val().length>=6){
				if(set_password_test_element.val()===set_password_element.val()){
					$("#set_password_status").text('match');
					$("#set_password_status").css('background-color','#4CAF50');
					$("#submit_button").removeAttr('disabled');
				} else {
					$("#set_password_status").text('don\'t match');
					$("#set_password_status").css('background-color','yellow');
					$("#submit_button").attr('disabled', 'disabled');
				}
			} else {
				$("#set_password_status").text('don\'t match');
				$("#set_password_status").css('background-color','yellow');
				$("#submit_button").attr('disabled', 'disabled');
			}
		}
	});
});
