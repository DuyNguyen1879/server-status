<?php
require_once '../settings.php';
require_once( "query-servers.php");
require "login/loginheader.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" href="/res/images/favicon.ico">
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jq-2.2.4/dt-1.10.13/b-1.2.4/b-colvis-1.2.4/cr-1.3.2/r-2.1.1/datatables.min.css"/>
	<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jq-2.2.4/dt-1.10.13/b-1.2.4/b-colvis-1.2.4/cr-1.3.2/r-2.1.1/datatables.min.js"></script>


	<script>
		$(document).ready(function() {

			var datastring = 'query-servers=true';
			$.ajax({
				type: "POST",
				url: "query-servers.php",
				data: datastring,
				success: function(data) {
					$('.server-data').show().html(data);
				}
			});

		});
	</script>

	<script>
		$.get("res/nav.php", function(data) {
			$("#nav-placeholder").replaceWith(data);
		});
	</script>
	<title><?php echo "$groupname"; ?> Missions</title>
</head>

<body>
<script>
$(document).ready(function() {
    $('#livemissions').DataTable( {
        "order": [[ 7, "desc" ]]
    } );
} );
</script>
<div id="nav-placeholder"></div>

<div class="container">
  <div class="row">
		<div class="col-md-10">
			<h1><?php echo "$groupname";?> Mission Management</h1>

			<?php

			// Call the class, and add your servers.
    $gq = \GameQ\GameQ::factory();
    $gq->addServers($servers);
    // You can optionally specify some settings
    $gq->setOption('timeout', 3); //in seconds
    // Send requests, and parse the data
    $results = $gq->process();

			foreach ($results as $key => $server) {
				if ($key == 'SRV1') {
					if ($server['gq_mapname'] == '') {
						$locked = 'False';
					} else {
						$locked = 'True';
					};
					if ($server['gq_numplayers'] > '0') {
						$unlockable = 'True';
					} else {
						$unlockable = 'False';
					}
				}
				}

			 ?>


			<h4>Server locked: <?php echo $locked ?> </h4>
			<?php if ($fd_enabled == 'True') {
				echo "<button name='btn-unlock' class='btn btn-success btn-unlock'";
				if ($unlockable == 'True') {echo "disabled title='Cannot unlock as there are players connected.'";};
				echo ">Unlock</button>";
			} ?>
		</div>
		<div class="col-md-2">
			<a class="btn btn-primary" href="addMission.php" role="button">Upload a mission</a>
		</div>
	</div>
	<hr/>
	<h2>Live Missions</h2>
	<div class="row">
		<div class="col-md-12">
			<table id='livemissions' class='table display'>
				<thead>
					<!--<th>Filename</th>
					<th>ID</th>-->
					<th>Mission Name</th>
					<th>Map</th>
					<th>Author</th>
					<th>Game Mode</th>
					<th>Min. Players</th>
					<th>Max. Players</th>
					<th>Description</th>
					<th>Last Updated</th>
					<th>Manage</th>
				</thead>
				<tbody>
					<?php
						try {
									$conn = new PDO("mysql:host=$servername;dbname=$dbname", "$username", "$password");
									$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
									$stmt = $conn->prepare("SELECT `filename`, `dateupdated`, `id`, `name`, `terrain`, `author`, `gamemode`, `minplayers`, `maxplayers`, `description`, `broken` FROM `missions` WHERE `broken`='0'");
									$stmt->execute();
									$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
								while($row = $stmt->fetch(/* PDO::FETCH_ASSOC */)) { ?>
									<tr>
										<!--<td><?php echo $row['filename'] ?></td>
										<td><?php echo $row['id'] ?></td>-->
										<td><a href="<?php echo "http://srv1missions.$groupsite/".$row['filename'] ?>"><?php echo $row['name'] ?></a></td>
									  <td><?php echo $row['terrain'] ?></td>
									  <td><?php echo $row['author'] ?></td>
									  <td><?php echo $row['gamemode'] ?></td>
									  <td><?php echo $row['minplayers'] ?></td>
									  <td><?php echo $row['maxplayers'] ?></td>
									  <td><?php echo $row['description'] ?></td>
										<td><?php echo $row['dateupdated'] ?></td>
										<td>
											<button type="button" name="btn-broken-modal" class="btn btn-warning btn-sm btn-broken-modal" <?php if ($locked == 'True') {echo "disabled";} ?> data-toggle="modal" data-target="#broken-modal" title="Report as broken" data-map="<?php echo($row['id']); ?>" data-name="<?php echo($row['name']); ?>" data-filename="<?php echo($row['filename']); ?>"><span class="glyphicon glyphicon-warning-sign"></span></button>
											<button type="button" name="btn-update-modal" class="btn btn-info btn-sm btn-update-modal" <?php if ($locked == 'True') {echo "disabled";} ?> data-toggle="modal" data-target="#update-modal" title="Upload new version (WIP)" data-map="<?php echo($row['id']); ?>" data-name="<?php echo($row['name']); ?>" data-filename="<?php echo($row['filename']); ?>"><span class="glyphicon glyphicon-upload"></span></button>
											<button type="button" name="btn-delete-modal" class="btn btn-danger btn-sm btn-delete-modal" <?php if ($locked == 'True') {echo "disabled";} ?> data-toggle="modal" data-target="#delete-modal" title="Delete (WIP)" data-name="<?php echo($row['name']); ?>" data-map="<?php echo($row['id']); ?>" data-filename="<?php echo($row['filename']); ?>"><span class="glyphicon glyphicon-trash"></span></button>
										</td>
									</tr>
							<?php }
						}
						catch (PDOException $e) {
										echo "Error: " . $e->getMessage();
						}

						$conn = null;
				?>
			</tbody>
			</table>
    </div>
  </div>
			<div class="modal fade" id="broken-modal">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Flag Mission as Broken</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						X
						</button>
					</div>
					<div class="modal-body">
						<p>Please explain why you think this mission is broken:</p>
						<p>Mission name: <?php echo "MISSION NAME" ?></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary">Save changes</button>
					</div>
				</div>
			</div>
</div>





<script>
$(document).ready(function() {
    $('#brokenmissions').DataTable( {

    } );
} );
</script>
<div class="container">
	<hr/>
	<div class="row">
		<div class="col-md-12">
			<h2>Broken Missions</h2>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<table id="brokenmissions" class='table'>
				<thead>
						<th>Mission Name</th>
						<th>Author</th>
						<th>Failure Category</th>
						<th>Failure Description</th>
						<th>Report</th>
				</thead>
				<tbody>
					<?php
						try {
									$conn = new PDO("mysql:host=$servername;dbname=$dbname", "$username", "$password");
									$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
									$stmt = $conn->prepare("SELECT `filename`, `id`, `name`, `author`, `brokentype`, `brokendes`, `broken` FROM `missions` WHERE `broken`='1'");
									$stmt->execute();
									$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
								while($row = $stmt->fetch(/* PDO::FETCH_ASSOC */)) { ?>
									<tr>
										<td><a href="<?php echo "http://broken.$groupsite/".$row['filename'] ?>"><?php echo $row['name'] ?></a></td>
									  <td><?php echo $row['author'] ?></td>
									  <td><?php echo $row['brokentype'] ?></td>
									  <td><?php echo $row['brokendes'] ?></td>
									  <td>
											<button type="button" name="btn-fixed" class="btn btn-success btn-sm btn-fixed" data-map="<?php echo($row['id']); ?>" data-filename="<?php echo($row['filename']); ?>"><span class="glyphicon glyphicon-ok"></span></button>
											<button type="button" name="btn-update-modal" class="btn btn-info btn-sm btn-update-modal" <?php if ($locked == 'True') { echo "disabled";} ?> data-toggle="modal" data-target="" title="Upload new version (WIP)" data-map="<?php echo($row['id']); ?>" data-filename="<?php echo($row['filename']); ?>"><span class="glyphicon glyphicon-upload"></span></button>
											<button type="button" name="btn-delete-modal" class="btn btn-danger btn-sm btn-delete-modal" <?php if ($locked == 'True') { echo "disabled";} ?> data-toggle="modal" data-target="" title="Delete (WIP)" data-map="<?php echo($row['id']); ?>" data-filename="<?php echo($row['filename']); ?>"><span class="glyphicon glyphicon-trash"></span></button>
										</td>
									</tr>
							<?php }
						}
						catch (PDOException $e) {
										echo "Error: " . $e->getMessage();
						}

						$conn = null;
				?>
			</tbody>
			</table>
    </div>
  </div>
</div>

<script type="text/javascript">
$('.btn-unlock').click(function(){

	var user = "<?php echo "$fd_user" ?>";
	var pass = "<?php echo "$fd_pass" ?>";
	var url = "<?php echo "$fd_URL" ?>";

	$.ajax({
		url: url+"/login",
		type: "POST",
		data: {
			username: user,
			password: pass
		},
		success : function(data) {
	 location.reload();
}
	});

});
</script>

<script type="text/javascript">
$('.btn-broken').click(function(){
    var id = $(this).data('map');
		var filename = $(this).data('filename');
    $.ajax({
     url: 'broken.php',
     type: "POST",
     data: {id: id,
		 				filename: filename
					},
		 success : function(data) {

		location.reload();
}
});
});
</script>

<script type="text/javascript">
$('.btn-delete').click(function(){
    var id = $(this).data('map');
		var filename = $(this).data('filename');
    $.ajax({
     url: 'delete.php',
     type: "POST",
     data: {id: id,
		 				filename: filename
					},
		 success : function(data) {

		location.reload();
}
});
});
</script>

<script type="text/javascript">
$('.btn-fixed').click(function(){
    var id = $(this).data('map');
		var filename = $(this).data('filename');
    $.ajax({
     url: 'fixed.php',
     type: "POST",
     data: {id: id,
		 				filename: filename
					},
		 success : function(data) {

		location.reload();
}
});
});
</script>
 </body>
 </html>
