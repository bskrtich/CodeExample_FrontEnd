<?php
	include('api/api.php');
	$validUser = validateUser();
	
	if(!$validUser)
	exit();

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<title>[Msgs]</title>
		
		<script src="js/common/jquery-1.10.0.js" type="text/javascript"></script>
		
		<script src="js/common/api.js" type="text/javascript"></script>
		<script src="js/assignment.js" type="text/javascript"></script>
		
		<link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" media="screen">
		<script src="bootstrap/js/bootstrap.js" type="text/javascript"></script>
		
		<link rel="stylesheet" href="assignment.css" type="text/css" media="screen">
	</head>
	<body>
		<div class="navbar">
			<a class="navbar-brand" href="#">[Msgs]</a>
			<ul class="nav navbar-nav">
				<li class="active"><a data-toggle="tab" href="#latestmsgs">Latest Msgs</a></li>
				<li><a data-toggle="tab" href="#newmsg">New Msg</a></li>
				<li><a data-toggle="tab" href="#followers">Followers</a></li>
				<li><a data-toggle="tab" href="#users">Users</a></li>
				<li><a data-toggle="tab" href="#account">Account</a></li>
			</ul>
			<p id="loggedInAs" class="navbar-text pull-right">Signed in as <?php echo $validUser->username; ?></p>
		</div>
		<div id="content">
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
				<div class="tab-pane" id="newmsg">[New Msg]</div>
				<div class="tab-pane" id="followers">
					
					<table id="followerlist" class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>People I am Following</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>No Follows Found</td>
							</tr>
						</tbody>
					</table>
					
					
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
			
						<br />
						<input type="submit" value="Submit" />
					</form>
					
					<h3>Add New Account</h3>
					<form id="addnewaccount" name="addnewaccount">
						<label for="username" id="username_label">User Name</label>
						<input type="text" name="username" id="username" size="10" />
						<br />
						<label for="password" id="password_label">Password</label>
						<input type="password" name="password" id="password" size="10" />
			
						<br />
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
		<script>
			function update(method) {
				console.log("Update Function");
				
				var request = new Object();
				request.method = method;
				
				console.log("Method: "+method);

				$.getJSON('api/index.php', request, function(data) {
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
		
			// Event Content Change
			$(".nav a").click(function() {
				console.log("Event Content Change");
				update($(this).attr('href').substring(1));
				
				
			});
			
			
		</script>
		
	</body>
</html>