	toJson = function( selector ) {
		var o = {};
		$.map( $( selector ), function( n,i ) {
			o[n.name] = $(n).val();
		});
		return o;
	}

	function parseUserInfo(result){
		$('#loggedInAs').html('Logged in as: ' + result['user']['username']);
	}


	$(document).ready(function(){
		callAPI('GetUserInfo', parseUserInfo, {});
	});