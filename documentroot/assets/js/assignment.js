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

    $("#changepassword").submit(function (event) {
        event.preventDefault();

    });

    $("#addnewaccount").submit(function (event) {
        event.preventDefault();

    });

    // On tab change do stuff
    $('a[data-toggle="tab"]').on('show.bs.tab', function (event) {
        // Change Page Title
        document.title = base_page_title + $(event.target).html();

        // Remove any alerts
        $("#alert-area").html('');

        // Load/Setup Data for tab
        switch ($(event.target).attr('href').substring(1)) {
            case "latestmsgs":
                callAPI('followmsglist', parseMsgList, {});
            break;

            case "users":
                callAPI('userlist', parseUserList, {});
            break;
        }
    });

    /////
    // parse Functions
    /////

    function parseUserInfo(result) {
        $('#loggedInAs').html('Logged in as: <span class="msg-font">@' + result['user'][0]['user_name'] + '</span>');
    }

    function parseMsgAdd(result) {
        if (result.error) {
            setAlert('warning', "Error posting msg");
        } else {
            setAlert('success', "Your msg has been posted");
            $("#newmsgtext").val('');
        }
    }

    function parseMsgList(data) {
        $("#msgslist > tbody").empty();

        $.each(data, function(index, value) {
            var html = '';

            html += '<tr>';
            html += '<td>';
            html += '<div class="pull-left well well-sm user-well"><span class="glyphicon glyphicon-user"></span></div>'
            html += '<div class="msg-info msg-font">@'+value.user_name+' - '+value.created+'</div>';
            html += '<div class="msg-action msg-font"><button type="button" class="btn btn-link btn-xs"><span class="glyphicon glyphicon-repeat"></span> Repost</button></div>';
            html += '<div class="msg-content">'+value.msg+'</div>';
            html += '</td>';
            html += '</tr>';

            $("#msgslist > tbody").append(html);
        });
    }

    function parseUserList(data) {
        $("#userlist > tbody").empty();

        $.each(data, function(index, value) {
            var html = '';

            html += '<tr>';
            html += '<td class="msg-font">@'+value.user_name+'</td>';
            html += '<td>';

            if (value.is_following == 0) {
                html += '<button type="button" class="btn btn-success btn-xs">';
                html += '<span class="glyphicon glyphicon-plus"></span> Follow';
                html += '</button>';
            } else {
                html += '<button type="button" class="btn btn-default btn-xs">';
                html += '<span class="glyphicon glyphicon-plus"></span> Unfollow';
                html += '</button>';
            }

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
