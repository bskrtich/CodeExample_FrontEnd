"use strict";

function parseUserInfo(result){
	$('#loggedInAs').html('Logged in as: ' + result['user']['username']);
}

$(document).ready(function(){
	callAPI('GetUserInfo', parseUserInfo, {});
});

$(function() {
    function update(method) {
        console.log("Update Function");

        var request = new Object();
        request.method = method;

        console.log("Method: "+method);

        $.getJSON('ajaxendpoint.php', request, function(data) {
            switch (request.method) {
                case "users":
                    // Clear Existing Data
                    $("#userlist > tbody").empty();

                    console.log(data);
                    $.each(data.result, function(index, value) {
                        $("#userlist > tbody").append("<tr><td>"+value.username+"</td></tr>");
                    });

                    break;

                default :
                    console.log("Unknown Data Received");
                    console.log(data);
                break;
            }
        });
    }

    var base_page_title = '[Msgs] - ';

    // Set Default Title Page
    document.title = base_page_title+$("ul#main-navbar li.active a").html();

    // On tab change
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Change Page Title
        document.title = base_page_title+$(e.target).html();

        // Load New Data for tab
        update($(e.target).attr('href').substring(1));

    });


});
