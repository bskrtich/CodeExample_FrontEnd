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

    $("#msgslist").on("click", ".action-repost", function (event) {
        var msg = $(event.target).parent().parent().find('.msg-content').html();
        callAPI('msgadd', parseMsgRepost, {msg: msg, attrmsgid: $(event.target).data("msg-id")});
    });

    $("#users").on("click", ".action-follow", function (event) {
        callAPI('followadd', parseFollowAddRemove, {followuserid: $(event.target).data("user-id")});
    });

    $("#users").on("click", ".action-unfollow", function (event) {
        callAPI('followremove', parseFollowAddRemove, {followuserid: $(event.target).data("user-id")});
    });

    $("#newmsgform").submit(function (event) {
        event.preventDefault();

        if ($("#newmsgtext").val().length == 0) {
            setAlert('warning', "You can't post an empty Msg");
            return;
        }

        callAPI('msgadd', parseMsgAdd, {msg: $("#newmsgtext").val()});
    });

    $("#changepassword").submit(function (event) {
        event.preventDefault();

        if ($("#newwpassword").val().length <= 3) {
            setAlert('warning', "Your password must be longer then 3 characters");
            return;
        }

        callAPI('userchangepass', parsePassChange, {password: $("#newwpassword").val()});
    });

    $("#addnewaccount").submit(function (event) {
        event.preventDefault();

        if ($("#username").val().length <= 3 || $("#password").val().length <= 3) {
            setAlert('warning', "Your user name and password must be longer then 3 characters");
            return;
        }

        callAPI('useradd', parseAccountAdd, {username: $("#username").val(), password: $("#password").val()});
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

    function parsePassChange(result) {
        if (result.error) {
            setAlert('warning', "Error changing password");
        } else {
            setAlert('success', "Your password has been changed");
            $('#changepassword')[0].reset();
        }
    }

    function parseFollowAddRemove(result) {
        if (result.error) {
            setAlert('warning', "Error following/unfollowing account");
        } else {
            callAPI('userlist', parseUserList, {});
        }
    }

    function parseAccountAdd(result) {
        if (result.error) {
            setAlert('warning', "Error adding account");
        } else {
            setAlert('success', "Your account has been added");
            $('#addnewaccount')[0].reset();
        }
    }

    function parseMsgAdd(result) {
        if (result.error) {
            setAlert('warning', "Error posting msg");
        } else {
            setAlert('success', "Your msg has been posted");
            $('#newmsgform')[0].reset();
            $("#charcount > span").html("0/140 Characters");
        }
    }

    function parseMsgRepost(result) {
        if (result.error) {
            setAlert('warning', "Error reposting msg");
        } else {
            callAPI('followmsglist', parseMsgList, {});
        }
    }

    function parseMsgList(result) {
        $("#msgslist > tbody").empty();

        $.each(result, function(index, value) {
            var html = '';

            html += '<tr>';
            html += '<td>';
            html += '<div class="pull-left well well-sm user-well"><span class="glyphicon glyphicon-user"></span></div>'
            html += '<div class="msg-info msg-font">';
            html += '@'+value.user_name+' - '+value.created;
            html += '</div>';
            html += '<div class="msg-action msg-font">';
            html += '<button type="button" data-msg-id="'+value.msg_id+'" class="action-repost btn btn-link btn-xs"><span class="glyphicon glyphicon-repeat"></span> Repost</button>';
            html += '</div>';
            html += '<div class="msg-content">'+value.msg+'</div>';

            if (value.attribution_user_id) {
                html += '<div class="msg-repost-tag msg-font">REPOST</div>';
                html += '<div class="msg-repost msg-font">Original by @'+value.attribution_user_name+'</div>';
            }

            html += '</td>';
            html += '</tr>';

            $("#msgslist > tbody").append(html);
        });
    }

    function parseUserList(result) {
        $("#userlist > tbody").empty();

        $.each(result, function(index, value) {
            var html = '';

            html += '<tr>';
            html += '<td class="msg-font">@'+value.user_name+'</td>';
            html += '<td>';

            if (value.is_following == 0) {
                html += '<button type="button" data-user-id="'+value.user_id+'" class="action-follow btn btn-success btn-xs">';
                html += '<span class="glyphicon glyphicon-plus"></span> Follow';
                html += '</button>';
            } else {
                html += '<button type="button" data-user-id="'+value.user_id+'" class="action-unfollow btn btn-default btn-xs">';
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
