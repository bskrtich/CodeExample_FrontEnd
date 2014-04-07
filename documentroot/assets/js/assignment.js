"use strict";

// Configuration
var base_page_title = '[Msgs] - ';


$(function() {
    // Get User Info
    callAPI('usergetinfo', parseUserInfo, {});

    //$('#main-navbar a:first').tab('show');

    // Set Default Title Page
    //document.title = base_page_title+$("ul#main-navbar li.active a").html();

    // Text Counter for a new msg
    $("#newmsgtext").keyup(function() {
        $("#charcount > span").html($(this).val().length+"/140 Characters");
    });

    // On tab change do stuff
    $('a[data-toggle="tab"]').on('show.bs.tab', function (event) {
        // Change Page Title
        document.title = base_page_title+$(event.target).html();

        // Load/Setup Data for tab
        switch ($(event.target).attr('href').substring(1)) {
            case "users":
                callAPI('userlist', parseUserList, {});
            break;

            default:
                console.log("Unknown Tab");
            break;
        }
    });

    function changeTab() {

    }

    function parseUserInfo(result){
        $('#loggedInAs').html('Logged in as: ' + result['user'][0]['user_name']);
    }

    function parseUserList(data){
        $("#userlist > tbody").empty();

        $.each(data, function(index, value) {
            $("#userlist > tbody").append("<tr><td>"+value.user_name+"</td></tr>");
        });
    }

});
