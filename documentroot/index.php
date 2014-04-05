<?php
require_once '../api/msgservice.class.php';

$msgservice = new msgservice($db);
$user = $msgservice->validateUser();

// Validates the user via HTTP Auth
if (!$user) {
    exit();
}

?><!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="Cache-Control" content="no-cache" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title>[Msgs]</title>

        <script src="libs/jquery-2.1.0.js" type="text/javascript"></script>

        <link rel="stylesheet" href="libs/journal.bootstrap.theme.css" type="text/css" media="screen">
        <script src="libs/bootstrap/js/bootstrap.js" type="text/javascript"></script>

        <script src="assets/js/api.js" type="text/javascript"></script>
        <script src="assets/js/assignment.js" type="text/javascript"></script>

        <link rel="stylesheet" href="assets/css/assignment.css" type="text/css" media="screen">
    </head>
    <body>
        <div class="navbar navbar-default navbar-fixed-top">
            <span class="navbar-brand">[Msgs]</span>
            <ul id="main-navbar" class="nav navbar-nav">
                <li class="active"><a data-toggle="tab" href="#latestmsgs">Latest Msgs</a></li>
                <li><a data-toggle="tab" href="#newmsg">New Msg</a></li>
                <li><a data-toggle="tab" href="#users">Users</a></li>
                <li><a data-toggle="tab" href="#account">Account</a></li>
            </ul>
            <p id="loggedInAs" class="navbar-text pull-right">Signed in as [user]</p>
        </div>
        <div id="content" class="container">
            <div class="tab-content">
                <div class="tab-pane active" data-toggle="tab" id="latestmsgs">
                    <table id="msgslist" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Current Msgs</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>No Msgs Found</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <div class="tab-pane" id="newmsg">
                    [New Msg]
                </div>
                <div class="tab-pane" id="users">
                    <table id="userlist" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Users</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>No Users Found</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <div class="tab-pane" id="account">
                    <h3>Change Password</h3>
                    <form id="changepassword">
                        <label for="newwpassword" id="newwpassword_label">Password</label>
                        <input type="password" name="newwpassword" id="newwpassword" size="10" />

                        <br>
                        <input type="submit" value="Submit" />
                    </form>

                    <h3>Add New Account</h3>
                    <form id="addnewaccount" name="addnewaccount">
                        <label for="username" id="username_label">User Name</label>
                        <input type="text" name="username" id="username" size="10" />
                        <br>
                        <label for="password" id="password_label">Password</label>
                        <input type="password" name="password" id="password" size="10" />

                        <br>
                        <input type="submit" value="Submit" />
                    </form>
                    <script>
                        $( "#addnewaccount" ).submit(function( event ) {
                            event.preventDefault();
                            console.log("submitted");

                            var request = new Object();
                            request.method = "AddUser";
                            request.params = toJson($(":input", this));
                            console.log(request);

                            $.post("api/index.php", JSON.stringify(request, null, 2), function(data) {
                                console.log(data);
                            });

                        });

                    </script>


                </div>
            </div>
        </div>
    </body>
</html>
