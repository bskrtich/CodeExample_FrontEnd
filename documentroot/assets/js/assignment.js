"use strict";

/////
// Configuration Vars
/////
var base_page_title = '[Msgs] - ';


$(function() {
    /////
    // Event Functions
    /////

    // Text Counter for a new msg
    $("#newmsgtext").keyup(function () {
        $("#charcount > span").html($(this).val().length+"/140 Characters");
    });

    $("#newmsg").submit(function (event) {
        event.preventDefault();

        // Dont submit if msg is empty
        if ($("#newmsgtext").val().length == 0) {
            setAlert('warning', "You can't post an empty Msg");
            return;
        }

        callAPI('msgadd', parseMsgAdd, {msg: $("#newmsgtext").val()});
    });

    // On tab change do stuff
    $('a[data-toggle="tab"]').on('show.bs.tab', function (event) {
        // Change Page Title
        document.title = base_page_title+$(event.target).html();

        // Remove any alerts
        $("#alert-area").html('');

        // Load/Setup Data for tab
        switch ($(event.target).attr('href').substring(1)) {
            case "latestmsgs":
                callAPI('submsglist', parseMsgList, {});
            break;

            case "users":
                callAPI('userlist', parseUserList, {});
            break;

            default:
                console.log("Unknown Tab: "+$(event.target).attr('href').substring(1));
            break;
        }
    });

    /////
    // parse Functions
    /////

    function parseUserInfo(result){
        $('#loggedInAs').html('Logged in as: ' + result['user'][0]['user_name']);
    }

    function parseMsgAdd(result){
        if (result.error) {
            setAlert('warning', "Error posting msg");
        } else {
            setAlert('success', "Your msg has been posted");
            $("#newmsgtext").val('');
        }
    }

    function parseMsgList(result){
        console.log(result);
    }

    function parseUserList(data){
        $("#userlist > tbody").empty();

        $.each(data, function(index, value) {
            var html;

            html = '<tr>';
            html += '<td>@'+value.user_name+'</td>';
            html += '<td>';
            html += '<button type="button" class="btn btn-success btn-xs">';
            html += '<span class="glyphicon glyphicon-plus"></span> Follow';
            html += '</button>';
            html += '</td>';
            html += '</tr>';

            $("#userlist > tbody").append(html);
        });
    }

    /////
    // General Functions
    /////

    // Alert Function
    function setAlert(type, message) {
        var html = '';
        console.log('setAlert: '+type+' '+message);
        switch (type) {
            case "success":
                html += '<div class="alert alert-success alert-dismissable">';
                html += message;
                html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                html += '</div>';
                $("#alert-area").html(html);
            break;

            case "info":
                html += '<div class="alert alert-info alert-dismissable">';
                html += message;
                html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                html += '</div>';
                $("#alert-area").html(html);
            break;

            case "warning":
                html += '<div class="alert alert-warning alert-dismissable">';
                html += message;
                html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                html += '</div>';
                $("#alert-area").html(html);
            break;

            default:
                html += '<div class="alert alert-danger alert-dismissable">';
                html += message;
                html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
                html += '</div>';
                $("#alert-area").html(html);
            break;
        }
    }

    /////
    // On Load Functions
    /////

    // Get User Info
    callAPI('usergetinfo', parseUserInfo, {});

    // Load First Tab
    $('#main-navbar a:first').tab('show');

});
