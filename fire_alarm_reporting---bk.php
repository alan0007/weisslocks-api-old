<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'fire_alarm_reporting' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'fire_alarm_reporting');
	$collection->remove( array( 'fire_alarm_reporting_id' =>(int) $_REQUEST['id'] ) );
	$msg = "Deleted Sucessfully!!";
}
/*
if(isset($_REQUEST['user_location']) && $_REQUEST['user_location'] == 'approve' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'user_location');
	
	$criteria = array('user_id'=>(int)$_REQUEST['id']);
	$collection->update( $criteria ,array('$set' => array(
				'approved'  => 1
		)));
}
*/
?>
<style>
.navwrap li{
	list-style: none;
	display: inline;
}
.pag-selected {
	font-weight: bold;
	text-decoration: underline;
}
.navwrap a{
	color:black;
}

.help_list{
	min-width:300px;
}
h4{
	text-decoration:underline;
}
	
a{
		cursor:pointer;
}


</style>	
<!--
<style>
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 100px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

/* The Close Button */
.close {
    color: #aaaaaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}
</style>
-->

<?php include("header.php");?>

<body>

    <div id="wrapper">

       <?php include("menu.php");?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Emergency Alarm Reporting</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Fire Alarm ID</th>
											<th>Purpose</th>
											<?php if($_SESSION['role'] == 1) { ?>
												<th>Company</th>
											<?php } ?>
											<th>Triggered by</th>
											<th>User Role</th>
											<th>Location</th>
											<th>Time</th>											
											<th>Message</th>
											<th>Response Statistics</th>	
											<th>Attendance Statistics</th>	
											<!-- <th>Action</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
										<?php
										//All Count
										//For Fire Alarm Response Count
										$total_user_count=0;
										$participant_count=0;
										$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364);
										//User Detected = distinct user detected in building + user scanned qr code
										$user_detected_list = array();
										$user_beacon_list = array();
										$user_qrcode_list = array();
										$user_full_list = array();
										
										$user_beacon_count=0;
										$user_qrcode_count=0;
										$user_detected_count=0;
										
										//Response List
										$user_safe_list = array();
										$user_not_safe_list = array();
										$user_help_list = array();
										
										//Location List
										$location_list = array();
										$location_user_list = array();
										
										$safe_count=0;
										$not_safe_count=0;
										$responded_count=0;
										$help_count=0;
										
										//For Fire Alarm Attendance Checking
										$present_count=0;
										$absent_count=0;
										//Attendance List
										$user_present_list = array();
										$user_absent_list = array();
										
										//$response_user_id = Array();
										//$help_user_id = Array();
										
										$date='';
										$time='';
										
										$demo_company_id = 12;
														
										if($_SESSION['role'] == 1 || $_SESSION['role'] == 2 || $_SESSION['role'] == 3)
										{
											//Find Total Participant Count
											$colletion_user = $app_data->users;
											$criteria_user = array(	
												'$and' => array( 
													array( 'participant'=> 1 ), 
													array( 'company_id'=> $demo_company_id )													
												)
											);	
											$cursor_user = $colletion_user->find($criteria_user);
											$participant_count = $cursor_user->count();											
											
											//Find user's company		
											//If superadmin, default company = 12
											if ($_SESSION['role'] == 1){
												$user_company_id = 12;
											}
											else{
												$collection_user = $app_data->users;
												$query_user = array('user_id' => $_SESSION['user_id'] );
												$cursor_user = $collection_user->find($query_user);
												if($cursor_user->count() > 0)
												{
													foreach($cursor_user as $uu){
														$user_company_id = $uu['company_id'];
													}
												}
											}
											
											//Get Fire Alarm
											$fire_alarm = $app_data->fire_alarm;
											$cursor = $fire_alarm->find();
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $fire_alarm)
												{												
													$fire_alarm_id = $fire_alarm['fire_alarm_id'];
													$company_id = $fire_alarm['company_id'];
													$date = $fire_alarm['time'];
													$date = strstr($date, ' ', true);											
													
													
													//Find Company name
													$collection_company = new MongoCollection($app_data, 'company');												
													$query_company = array( 'company_ID' => (int)$company_id ); 
													$cursor_company = $collection_company->find( $query_company );
													if($cursor_company->count() > 0) { 
														foreach($cursor_company as $company)
														{
															$company_name = $company['company_name'];
															$company_ref_id = $company['company_ref'];
														}										
													}
														
													$user_id = $fire_alarm['trigger_user_id'];
													//Find Username
													$collection_users = new MongoCollection($app_data, 'users');
													$cursor_user = $collection_users->find(array('user_id'=>$user_id));
													if($cursor_user->count() > 0)
													{
														foreach($cursor_user as $user)
														{
															$username = $user['username'];
															$role = $user['role'];														
														}
													}
													
													//-------------------------------
													// Start Detected User
													//-------------------------------
													//Find User detected Beacon & push user_id into array
													/*
													$collection_user_location = $app_data->user_location;
													//Exclude Users
													$criteria_exclude_user = array(	
														'$and' => array(
															array( 'user_id'=> array('$ne'=>185) ), 
															array( 'user_id'=> array('$ne'=>186) ),
															array( 'user_id'=> array('$ne'=>196) ),
															array( 'user_id'=> array('$ne'=>252) ),
															array( 'user_id'=> array('$ne'=>237) ),
															array( 'user_id'=> array('$ne'=>238) ),
															array( 'user_id'=> array('$ne'=>239) ),
															array( 'user_id'=> array('$ne'=>201) ),
															array( 'user_id'=> array('$ne'=>255) ),
															array( 'user_id'=> array('$ne'=>363) )
														)
													);
													
													$distinct_user_location = $collection_user_location->distinct("user_id",$criteria_exclude_user);
													//$user_beacon_count = $distinct_user_location->count();
													//print_r($distinct_user_location);
													*/
													
													//Find User detected Beacon & push user_id into array
													//Include Today's Date.
													//echo $date;
													$collection_user_location_date = $app_data->user_location;													
													$criteria_location_date = array( 
														//'time'=> array('$in'=>array('27/11/2018')
														//'location_time'=> array('$in'=>array('27/11/2018'))
														'location_time'=> new MongoRegex('/' . $date. '/i')
													);
													$distinct_user_location_date = $collection_user_location_date->find($criteria_location_date);
													//$distinct_user_location_date = $collection_user_location_date->distinct("location_time",$criteria_location_date);
													//echo $date .": " . $distinct_user_location_date->count() . " ";
													
													if($distinct_user_location_date->count() > 0)
													{
														foreach($distinct_user_location_date as $location)
														{
															//echo $location['user_id'] . " ";
															$user_id=$location['user_id'];
															if ( !in_array($user_id, $user_exclude_list) )
															{
																if (!in_array($user_id, $user_detected_list)){
																	array_push($user_detected_list,$user_id);
																}		
															}															
														}
														$user_beacon_count = sizeof($user_detected_list);
														$user_beacon_list = $user_detected_list;
														//echo $date .": " . $user_beacon_count . "<br>";
														//for ($i=0;$i<=$user_beacon_count;$i++){
														//	echo $user_beacon_list[$i] . " ";
														//}
														//echo "<br>";
														
													}
													
													//Find User scanned QR Code & push user_id into array
													//Include Today's Date.
													//echo $date;
													$collection_qrcode = $app_data->qrcode;													
													$criteria_qrcode = array( 
														//'time'=> array('$in'=>array('27/11/2018')
														//'location_time'=> array('$in'=>array('27/11/2018'))
														'access_time'=> new MongoRegex('/' . $date. '/i')
													);
													$distinct_qrcode = $collection_qrcode->find($criteria_qrcode);
													//$distinct_user_location_date = $collection_user_location_date->distinct("location_time",$criteria_location_date);
													//echo $date .": " . $distinct_user_location_date->count() . " ";
													
													if($distinct_qrcode->count() > 0)
													{
														foreach($distinct_qrcode as $qrcode)
														{
															//echo $location['user_id'] . " ";
															$user_id=$qrcode['user_id'];
															if ( !in_array($user_id, $user_exclude_list) ){
																//Push into QR Code Array
																if (!in_array($user_id, $user_qrcode_list)){
																	array_push($user_qrcode_list,$user_id);
																}
																//Push into Merged Array
																if (!in_array($user_id, $user_detected_list)){
																	array_push($user_detected_list,$user_id);
																}		
															}															
														}
														$user_qrcode_count = sizeof($user_qrcode_list);
														$user_qrcode_list = $user_qrcode_list;
														//echo $date .": " . $user_qrcode_count . "<br>";
														//for ($i=0;$i<=$user_qrcode_count;$i++){
														//	echo $user_qrcode_list[$i] . " ";
														//}
														//echo "<br>";
														
													}
													
													//Push into User Count List - Correct
													$combined_list = array_merge($user_beacon_list, $user_qrcode_list);
													
													// array_unique removed last unique value for no reason
													//$user_full_list =  array_unique($combined_list);
													// remove duplicate values by using array_flip
													$user_full_list = array_keys(array_flip($combined_list)); 
												   
													//Count
													$user_detected_count = sizeof($user_full_list);
													
													
													//Check All Detected User list
													echo "Beacon: ";
													for ($i=0;$i<=$user_beacon_count-1;$i++){
															echo $user_beacon_list[$i] . " ";
													}
													echo "<br>";
													echo "QR Code: ";
													for ($i=0;$i<=$user_qrcode_count-1;$i++){
															echo $user_qrcode_list[$i] . " ";
													}
													echo "<br>";
													
													
													echo "Merged: ";
													for ($i=0;$i<=$user_detected_count-1;$i++){
															echo $user_full_list[$i] . " ";
													}
													echo "<br>";
													
													
													//-------------------------------
													// Start Response
													//-------------------------------
													$collection_response = $app_data->fire_alarm_response;													
													$criteria_response = array( 'fire_alarm_id'=> $fire_alarm_id);
													$cursor_response = $collection_response->find($criteria_response);
													if($cursor_response->count() > 0)
													{
														foreach($cursor_response as $response)
														{															
															$user_id=$response['user_id'];
															if($response['response'] == 1){
																if ( !in_array($user_id, $user_exclude_list) ){																
																	if (!in_array($user_id, $user_safe_list)){
																		array_push($user_safe_list,$user_id);
																	}		
																}
															}
															if($response['response'] == 2){
																if ( !in_array($user_id, $user_exclude_list) ){																
																	if (!in_array($user_id, $user_not_safe_list)){
																		array_push($user_not_safe_list,$user_id);
																	}		
																}
															}
															if($response['response'] == 3){
																if ( !in_array($user_id, $user_exclude_list) ){																
																	if (!in_array($user_id, $user_help_list)){
																		array_push($user_help_list,$user_id);
																	}		
																}
															}
														}
														$safe_count = sizeof($user_safe_list);
														$not_safe_count = sizeof($user_not_safe_list);
														$help_count = sizeof($user_help_list);
														
														$responded_list = array_merge($user_safe_list, $user_not_safe_list);
														$responded_list =  array_unique($responded_list);
														$responded_count = sizeof($responded_list);
														
														/*
														//Check Values														
														echo "Help " . $date .": " . $help_count . "<br>";
														for ($i=0;$i<=$help_count;$i++){
															echo $user_help_list[$i] . " ";
														}
														echo "<br>";
														*/
														
														//Final No Response Count
														$not_responded_count = $user_detected_count - $responded_count;
													}
													
													//-------------------------------
													//Get Location List
													//-------------------------------
													$collection_help_location = new MongoCollection($app_data, 'fire_alarm_help');												
													$query_help_location = array( 'fire_alarm_id' => $fire_alarm_id ); 
													$cursor_help_location = $collection_help_location->find( $query_help_location );
													if($cursor_help_location->count() > 0) {
														foreach( $cursor_help_location as $help){
															$location = $help['location'];
															if (!in_array($location, $location_list)){
																array_push($location_list,$location);
															}
														}
													}
													$location_count =  sizeof($location_list);
													//Check Values														
													//echo "Location " . $date .": " . $location_count . "<br>";
													//for ($i=0;$i<=$location_count;$i++){
													//	echo $location_list[$i] . " ";
													//}
													//echo "<br>";
													
													
													//Variables for Fire Alarm
													$location_id = $fire_alarm['location_id'];
													$location_name = $fire_alarm['location_name'];
													$time = $fire_alarm['time'];
													$purpose = $fire_alarm['purpose'];
													$location = $fire_alarm['location'];
													$message = $fire_alarm['message'];	
												  
													?>
													<tr>														
														<td><?php echo $fire_alarm_id; ?></td>
														<td><?php echo $purpose; ?></td>
														<td><?php echo $username; ?></td>														
														<td><?php
															echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
														?></td>									
														<?php if($_SESSION['role'] == 1) { ?>
															<td><?php echo $company_name; ?></td>
														<?php } ?>
														<td><?php echo $location_name; ?></td>
														<td><?php echo $time; ?></td>
														<td><?php echo $message; ?></td>

														<!-- User Response Statistics -->
														<td class="help_list">
															<?php
															
															//Find User in Company
															$collection_user_total = new MongoCollection($app_data, 'users');												
															$query_user_total = array( 'company_id' => $company_id ); 
															$cursor_user_total = $collection_user_total->find( $query_user_total );
															$total_user_count = $cursor_user_total->count();															
															if($cursor_user_total->count() > 0) { 
																foreach($cursor_user_total as $user_total)
																{
																	$user_total_username = $user_total['username'];
																	//echo $user_total_username . "<br/>";
																	//$total_user_count++;
																}										
															}
															
															/*
															//Find Fire Alarm Response
															$collection_fire_alarm_response = new MongoCollection($app_data, 'fire_alarm_response');												
															$query_fire_alarm_response = array( 'fire_alarm_id' => $fire_alarm_id ); 
															$cursor_fire_alarm_response = $collection_fire_alarm_response->find( $query_fire_alarm_response );
															if($cursor_fire_alarm_response->count() > 0) { 
																foreach($cursor_fire_alarm_response as $fire_alarm_response)
																{																
																	$response_user_id[] = $fire_alarm_response['user_id'];
																	$response = $fire_alarm_response['response'];
																	
																	if ($response == 1){
																		$safe_count++;
																		$responded_count++;																	
																	}
																	if ($response == 2){
																		$not_safe_count++;
																		$responded_count++;																	
																	}
																	if ($response == 3){
																		$help_count++;
																		$responded_count++;																
																	}
																	//TODO: check for repeated response from same user
																	
																}										
															}
															$safe_query = array( '$and' => array( array('fire_alarm_id' => $fire_alarm_id ), array('response'=>1) ) );	
															$cursor_safe = $collection_fire_alarm_response->find( $safe_query );
															$safe_count = $cursor_safe->count();
															$not_responded_count = $user_detected_count - $responded_count;
															*/
															?>
															<?php /*<p>Total Users Registered: <?php echo $total_user_count;?></p>*/ ?>
															
															<!-- Tooltips -->
															
															
															<!-- Statistics -->
															<p>Total Participant 
																<i class="fa fa-user fa-fw tooltips">
																  <span class="tooltiptext">Tooltip text</span>
																</i>
																 :<?php echo $participant_count;?>
															</p>	
															<!-- tooltip block -->
															
															
															<p>Users Detected by Beacon Today: <?php echo $user_beacon_count;?></p>
															<p>Users Scanned QR Code Today: <?php echo $user_qrcode_count;?></p>
															<p>Total Users Detected in Building: <?php echo $user_detected_count;?></p>
															<p>Users Received Notification: <?php echo $user_detected_count;?></p>
															<hr>
															<h4>Response</h4>
															<p>User Responded: <?php echo $responded_count;?></p>
															<p>User Not Responded: <?php echo $not_responded_count;?></p>
															<hr>
															<h4>Responded Details</h4>
															<p>Safe: <?php echo $safe_count;?></p>
															<p>Not Safe Yet: <?php echo $not_safe_count;?></p>
															<p>Help: <?php echo $help_count;?></p>
															<hr>
															<h4>Help List</h4>
															<div id="list_by_location">
																<p>List by <?php echo $location_count; ?> Location (Click to see full list):</p>
																<?php 
																//For Loop for each location
																for ($i=0;$i<=$location_count-1;$i++){
																	?>
																	<div>
																		<ol>
																			<li>
																			<?php
																			$collection_help_location = $app_data->fire_alarm_help;												
																			$query_help_location = array( 'location' => $location_list[$i] ); 
																			$cursor_help_location = $collection_help_location->find( $query_help_location );
																			if($cursor_help_location->count() > 0) { 
																				foreach($cursor_help_location as $location)
																				{
																					$location_name = $location['username'];
																					//echo $user_total_username . "<br/>";
																					//$total_user_count++;																		
																					$user_id = $location['user_id'];																		
																					if (!in_array($user_id, $location_user_list)){
																						array_push($location_user_list,$user_id);
																					}
																				}	
																			}
																			$location_user_count =  sizeof($location_user_list);																
																			echo $location_list[$i] . ': <a href="#openModal-' . $fire_alarm_id . "" . $i . '">' . $location_user_count . ' people</a>';
																			?>	
																			</li>																		
																		</ol>
																	</div>
																	<div id="openModal-<?php echo $fire_alarm_id . "" . $i;?>" class="modalDialog">
																		<div>
																			<a href="#close" title="Close" class="close">X</a>
																			<h2>List of people in <?php echo $location_list[$i];?></h2>
																			<table class="table table-striped table-bordered table-hover" id="dataTables-example">
																				<thead>
																					<tr>
																						<th>No.</th>
																						<th>Username</th>
																						<th>Phone</th>
																						<th>Location</th>
																						<th>Last detected by Beacon Today</th>
																						<th>Last detected Time Today</th>
																					</tr>
																				</thead>
																				<tbody>																				
																				<?php 
																				$list_number = 1;
																				for ($x=0;$x<=$location_user_count-1;$x++){ 
																					$user_id = $location_user_list[$x];
																					//Find Username
																					$collection_username = new MongoCollection($app_data, 'users');
																					$cursor_username = $collection_username->find(array('user_id'=>$user_id));
																					if($cursor_username->count() > 0)
																					{
																						foreach($cursor_username as $user)
																						{
																							$username = $user['username'];
																							$full_name = $user['full_name'];
																							$phone_number = $user['phone_number'];														
																						}
																					}
																					
																					//Find last known location
																					$collection_user_location = new MongoCollection($app_data, 'user_location');
																					$criteria_user_location = array(	
																						'$and' => array(
																							array( 'user_id' => $help_user_id ), 
																							array( 'location_time'=> new MongoRegex('/' . $date. '/i') )
																						)
																					);
																					$query_user_location = array( 'user_id' => $help_user_id );
																					$cursor_user_location = $collection_user_location->find( $criteria_user_location )->sort(array("user_location_id"=>-1))->limit(-1);
																					//$user_last_beacon = $cursor_user_location['beacon_name'];
																					if($cursor_user_location->count() > 0)
																					{
																						foreach($cursor_user_location as $location)
																						{
																							$user_last_beacon_name = $location['beacon_name'];
																							$user_last_beacon_time = $location['location_time'];
																						}
																					}
																					else{																					
																						$user_last_beacon = "No Last know location";																			
																						//$last_known_location = $collection_user_location->find($query_user_location)->sort(array("user_location_id"=>-1))->limit(1);																
																					}
																				?>
																					<!-- Display User Details -->
																					<tr>
																						<td><?php echo $list_number;?></td>
																						<td><?php echo $username;?></td>
																						<td><?php echo $phone_number;?></td>
																						<td><?php echo $location_list[$i];?></td>
																						<td><?php echo $user_last_beacon_name;?></td>
																						<td><?php echo $user_last_beacon_time;?></td>
																					</tr>
																					<?php /*<li><?php echo $username;?> , Phone: <?php echo $phone_number;?>, Location: <?php echo $location_list[$i];?>, Last Detected by Beacon Today: <?php echo $user_last_beacon_name;?> at <?php echo $user_last_beacon_time;?></li>*/?>																	
																				<?php 
																				
																					//Add List Number
																					$list_number++;
																				}
																				?>
																				
																				
																				</tbody>
																			</table>
																		</div>
																	</div>
																	<?php
																	//Unset Location User List
																	$location_user_list = array();																
																}
																
																?>
															</div>
															<div id="list_full">																
																<p><a href="#openModal-<?php echo $fire_alarm_id . "-full";?>">See Full List of User Here</a></p>
																<div id="openModal-<?php echo $fire_alarm_id . "-full";?>" class="modalDialog">
																	<div>
																		<a href="#close" title="Close" class="close">X</a>
																		<p>List all users requested for help:</p>
																		<?php
																		$collection_help = new MongoCollection($app_data, 'fire_alarm_response');
																		$criteria_help = array(	
																			'$and' => array(
																				array( 'response' => 3 ), 
																				array( 'fire_alarm_id'=> $fire_alarm_id )
																			)
																		);																		
																		//$query_help = array( 'response' => 3 ); 
																		$cursor_help = $collection_help->find( $criteria_help );
																		if($cursor_help->count() > 0) { 
																		?>
																			<table class="table table-striped table-bordered table-hover" id="dataTables-example">
																				<thead>
																					<tr>
																						<th>No.</th>
																						<th>Username</th>
																						<th>Phone</th>
																						<th>Location</th>
																						<th>Last detected by Beacon Today</th>
																						<th>Last detected Time Today</th>
																					</tr>
																				</thead>
																				<tbody>
																				
																				<?php
																				$full_list_number = 1;
																				foreach($cursor_help as $help)
																				{
																					$help_user_id = $help['user_id'];
																					//Find Username
																					$collection_username = new MongoCollection($app_data, 'users');
																					$cursor_username = $collection_username->find(array('user_id'=>$user_id));
																					if($cursor_username->count() > 0)
																					{
																						foreach($cursor_username as $user)
																						{
																							$username = $user['username'];
																							$full_name = $user['full_name'];
																							$role = $user['role'];
																							$phone_number = $user['phone_number'];
																						}
																					}																		
																					
																					//Find Help Location
																					$collection_help_location = $app_data->fire_alarm_help;												
																					$query_help_location = array( 'user_id' => $help_user_id ); 
																					$cursor_help_location = $collection_help_location->find( $query_help_location );
																					if($cursor_help_location->count() > 0) { 
																						foreach($cursor_help_location as $location)
																						{
																							$help_location = $location['location'];
																							$help_message = $location['message'];
																							$help_time = $location['time'];
																						}	
																					}																					
																					
																					//Find last known location
																					$collection_user_location = new MongoCollection($app_data, 'user_location');
																					$criteria_user_location = array(	
																						'$and' => array(
																							array( 'user_id' => $help_user_id ), 
																							array( 'location_time'=> new MongoRegex('/' . $date. '/i') )
																						)
																					);
																					$query_user_location = array( 'user_id' => $help_user_id );
																					$cursor_user_location = $collection_user_location->find( $criteria_user_location )->sort(array("user_location_id"=>-1))->limit(-1);
																					//$user_last_beacon = $cursor_user_location['beacon_name'];
																					if($cursor_user_location->count() > 0)
																					{
																						foreach($cursor_user_location as $location)
																						{
																							$user_last_beacon_name = $location['beacon_name'];
																							$user_last_beacon_time = $location['location_time'];
																						}
																					}
																					else{																					
																						$user_last_beacon = "No Last know location";																			
																						//$last_known_location = $collection_user_location->find($query_user_location)->sort(array("user_location_id"=>-1))->limit(1);																
																					}																					
																					
																					?>
																					<!-- Display User Details -->
																					<tr>
																						<td><?php echo $full_list_number;?></td>
																						<td><?php echo $username;?></td>
																						<td><?php echo $phone_number;?></td>
																						<td><?php echo $help_location;?></td>
																						<td><?php echo $user_last_beacon_name;?></td>
																						<td><?php echo $user_last_beacon_time;?></td>
																					</tr>
																					
																					<?php
																					$full_list_number++;
																				}
																				
																				/* <li><?php echo $username;?> , Phone: <?php echo $phone_number;?>, Location: <?php echo $help_location;?>, Last Detected by Beacon Today: <?php echo $user_last_beacon_name;?> at <?php echo $user_last_beacon_time;?></li>	*/
																				?>
																				
																				</tbody>
																			</table>
																			<?php
																		} else {
																			echo "No User requested for help";
																		}																
																		?>
																	</div>
																</div>
															</div>
														</td>
																
														
														<!------------------------------------------>
														<!-- Fire Drill/Emergency Attendance List -->
														<!------------------------------------------>
														<td class="help_list">
														<?php
															//Find user exited building
															$beacon_exit_list = array("AA 1","AA 2","AA 3","AA 4","AA 5","AA 6","AA 7","AA 8");
															$user_absent_list = $user_full_list;
															
															$collection_user_exit = new MongoCollection($app_data, 'user_location');
															$criteria_user_exit = array(	
																'$and' => array(
																	array( 'user_id' => $user_full_list ), 
																	array( 'beacon_name' => $beacon_exit_list ),
																	array( 'location_time'=> new MongoRegex('/' . $date. '/i') )
																)
															);
															$query_user_exit = array( 'user_id' => $help_user_id );
															$cursor_user_exit = $collection_user_exit->find( $criteria_user_exit )->sort(array("user_location_id"=>-1))->limit(-1);
															//$user_last_beacon = $cursor_user_location['beacon_name'];
															if($cursor_user_exit->count() > 0)
															{
																foreach($cursor_user_exit as $exit)
																{
																	$user_id = $exit['user_id'];
																	$user_last_beacon_name = $exit['beacon_name'];
																	$user_last_beacon_time = $exit['location_time'];
																	if ( !in_array($user_id, $user_exclude_list) ){
																		if (!in_array($user_id, $user_present_list)){
																			array_push($user_present_list,$user_id);
																		}
																		
																		if (in_array($user_id, $user_absent_list)){																		
																			unset($user_absent_list[$user_id]);
																			$user_absent_list = array_values($user_absent_list);
																		}
																	}
																	
																}
															}
															$present_count = sizeof($user_present_list);
															$absent_count = sizeof($user_absent_list);;
															
															/*
															//Check Values - Present													
															echo "Exit " . $date .": " . $present_count . "<br>";
															for ($i=0;$i<=$present_count-1;$i++){
																echo $user_present_list[$i] . " ";
															}
															echo "<br>";
															*/
															/*
															//Check Values - Absent														
															echo "Absent " . $date .": " . $absent_count . "<br>";
															for ($i=0;$i<=$absent_count-1;$i++){
																echo $user_absent_list[$i] . " ";
															}
															echo "<br>";
															*/
															
															//if ( $absent_count == 0 ){
															//	$absent_count = $user_detected_count - $present_count;
															//}
															
															?>
														
															<p>Total Users Detected in Building: <?php echo $user_detected_count;?></p>
															<p>Users Received Notification: <?php echo $user_detected_count;?></p>
															<hr>
															<p>Users Detected at Assembly Area (Exited building): <?php echo $present_count;?></p>															
															<p>Users Absent (Still in building): <?php echo $absent_count;?></p>													
															<hr>
															<h4>Absent User List:</h4>
															
															<div id="list_full">																
																<p><a href="#openModal-<?php echo $fire_alarm_id . "-absent-full";?>">See Full List of User Here</a></p>
																<div id="openModal-<?php echo $fire_alarm_id . "-absent-full";?>" class="modalDialog">
																	<div>
																		<a href="#close" title="Close" class="close">X</a>
																		<p>List all users in absent list:</p>
																		
																		<table class="table table-striped table-bordered table-hover" id="dataTables-example">
																			<thead>
																				<tr>
																					<th>No.</th>
																					<th>Username</th>
																					<th>Phone</th>
																					<th>Location</th>
																					<th>Last detected by Beacon Today</th>
																					<th>Last detected Time Today</th>
																				</tr>
																			</thead>
																			<tbody>
																			
																			<?php
																			// For loop: based on users listed in $user_absent_list
																			for ($c=0; $c<=$absent_count; $c++){														
																				$collection_absent = new MongoCollection($app_data, 'users');
																				//$criteria_absent = array(	
																				//	'$and' => array(
																				//		array( 'user_id' => $user_absent_list ), 
																				//		array( 'fire_alarm_id'=> $fire_alarm_id )
																				//	)
																				//);																		
																				$query_absent = array( 'user_id' => $user_absent_list[$c] ); 
																				$cursor_absent = $collection_absent->find( $query_absent );
																				if($cursor_absent->count() > 0) {
																				
																						$absent_list_number = $c + 1;
																						foreach($cursor_absent as $absent)
																						{
																							//check values
																							//echo $absent['user_id'];
																							$absent_user_id = $absent['user_id'];
																							$username = $absent['username'];
																							$full_name = $absent['full_name'];
																							$role = $absent['role'];
																							$phone_number = $absent['phone_number'];																																				
																							
																							//Find Help Location
																							$collection_help_location = $app_data->fire_alarm_help;												
																							$query_help_location = array( 'user_id' => $absent_user_id ); 
																							$cursor_help_location = $collection_help_location->find( $query_help_location );
																							if($cursor_help_location->count() > 0) { 
																								foreach($cursor_help_location as $location)
																								{
																									$help_location = $location['location'];
																									$help_message = $location['message'];
																									$help_time = $location['time'];
																								}	
																							}																					
																							
																							//Find last known location
																							$collection_user_location = new MongoCollection($app_data, 'user_location');
																							$criteria_user_location = array(
																								'$and' => array(
																									array( 'user_id' => $absent_user_id ), 
																									array( 'location_time'=> new MongoRegex('/' . $date. '/i') )
																								)
																							);
																							$query_user_location = array( 'user_id' => $help_user_id );
																							$cursor_user_location = $collection_user_location->find( $criteria_user_location )->sort(array("user_location_id"=>-1))->limit(-1);
																							//$user_last_beacon = $cursor_user_location['beacon_name'];
																							if($cursor_user_location->count() > 0)
																							{
																								foreach($cursor_user_location as $location)
																								{
																									$user_last_beacon_name = $location['beacon_name'];
																									$user_last_beacon_time = $location['location_time'];
																								}
																							}
																							else{																					
																								$user_last_beacon = "No Last know location";																			
																								//$last_known_location = $collection_user_location->find($query_user_location)->sort(array("user_location_id"=>-1))->limit(1);																
																							}																					
																							
																							?>
																							<!-- Display User Details -->
																							<tr>
																								<td><?php echo $absent_list_number;?></td>
																								<td><?php echo $username;?></td>
																								<td><?php echo $phone_number;?></td>
																								<td><?php echo $help_location;?></td>
																								<td><?php echo $user_last_beacon_name;?></td>
																								<td><?php echo $user_last_beacon_time;?></td>
																							</tr>
																							
																							<?php
																							
																						}
																						
																						/* <li><?php echo $username;?> , Phone: <?php echo $phone_number;?>, Location: <?php echo $help_location;?>, Last Detected by Beacon Today: <?php echo $user_last_beacon_name;?> at <?php echo $user_last_beacon_time;?></li>	*/
																						
																																											
																					}
																			}?>
																			</tbody>
																		</table>
																	</div>
																</div>
															</div>
															
															
														</td>
														
														<?php /*
														<td>
															<a href="manage_fire_alarm_response.php?fire_alarm_response_id=<?php echo $fire_alarm_response_id; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="fire_alarm_response.php?delete=fire_alarm_response&id=<?php echo $fire_alarm_response_id; ?>">  Delete </a>
														</td>
														*/ ?>
														
													</tr>
												<?php
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Fire Alarm Response Found </td></tr>';
											}
										} else {
											//Find user's company											
											$collection_user = $app_data->users;
											$query_user = array('user_id' => $_SESSION['user_id'] );
											$cursor_user = $collection_user->find($query_user);
											if($cursor_user->count() > 0)
											{
												foreach($cursor_user as $uu){
													$user_company_id = $uu['company_id'];
												}
											}
											
											
										}											
										?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php include("footer.php");?>

<script>
// Get the modal
var modal = document.getElementById('myModal');
var modal_1 = document.getElementById('myModal1');
var modal_2 = document.getElementById('myModal2');
var modal_3 = document.getElementById('myModal3');
var modal_full = document.getElementById('myModal_full');

// Get the button that opens the modal
var btn = document.getElementById("myBtn");
var btn_1 = document.getElementById("myBtn1");
var btn_2 = document.getElementById("myBtn2");
var btn_3 = document.getElementById("myBtn3");
var btn_full = document.getElementById("myBtn_full");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
}
btn_1.onclick = function() {
    modal_1.style.display = "block";
}
btn_2.onclick = function() {
    modal_2.style.display = "block";
}
btn_3.onclick = function() {
    modal_3.style.display = "block";
}
btn_full.onclick = function() {
    modal_full.style.display = "block";
}


// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
	modal_1.style.display = "none";
	//modal_2.style.display = "none";
	modal_3.style.display = "none";
	modal_full.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
	if (event.target == modal_1) {
        modal_1.style.display = "none";
    }
	if (event.target == modal_2) {
        modal_2.style.display = "none";
    }
	if (event.target == modal_3) {
        modal_3.style.display = "none";
    }
	if (event.target == modal_full) {
        modal_full.style.display = "none";
    }
}
</script>