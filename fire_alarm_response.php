<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'fire_alarm_response' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'fire_alarm_response');
	$collection->remove( array( 'fire_alarm_response_id' =>(int) $_REQUEST['id'] ) );
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
</style>

<?php include("header.php");?>

<body>

    <div id="wrapper">

       <?php include("menu.php");?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Fire Alarm Response</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>ID</th>
											<th>Fire Alarm ID</th>
											<th>User</th>
											<th>Role</th>
											<?php if($_SESSION['role'] == 1) { ?>
												<th>Company</th>
											<?php } ?>
											<th>Response</th>
											<th>Message</th>
											<th>Time</th>											
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										if($_SESSION['role'] == 1)
										{
											$fire_alarm_response = $app_data->fire_alarm_response;
											$cursor = $fire_alarm_response->find();
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $fire_alarm_response)
												{												
  													$fire_alarm_response_id = $fire_alarm_response['fire_alarm_response_id'];
													$fire_alarm_id = $fire_alarm_response['fire_alarm_id'];
													//Find Fire Alarm
													$collection_fire_alarm = new MongoCollection($app_data, 'fire_alarm');												
													$query_fire_alarm = array( 'fire_alarm_id' => $fire_alarm_id ); 
													$cursor_fire_alarm = $collection_fire_alarm->find( $query_fire_alarm );
													if($cursor_fire_alarm->count() > 0) { 
														foreach($cursor_fire_alarm as $fire_alarm)
														{
															$fire_alarm_trigger_user_id = $fire_alarm['trigger_user_id'];
															$collection_users = new MongoCollection($app_data, 'users');
															$cursor_user = $collection_users->find(array('user_id'=>$fire_alarm_trigger_user_id));
															if($cursor_user->count() > 0)
															{
																foreach($cursor_user as $user)
																{
																	$fire_alarm_trigger_username = $user['username'];
																	$fire_alarm_trigger_user_role = $user['role'];														
																}
															}
															
															$fire_alarm_location_name = $fire_alarm['location_name'];
															$fire_alarm_time = $fire_alarm['time'];
															$fire_alarm_purpose = $fire_alarm['purpose'];															
														}										
													}
													
													$company_id = $fire_alarm_response['company_id'];
													//Find Company name
													$collection_company = new MongoCollection($app_data, 'company');												
													$query_company = array( 'company_ID' => $company_id ); 
													$cursor_company = $collection_company->find( $query_company );
													if($cursor_company->count() > 0) { 
														foreach($cursor_company as $company)
														{
															$company_name = $company['company_name'];
															$company_ref_id = $company['company_ref'];
														}										
													}
														
													$user_id = $fire_alarm_response['user_id'];
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
													
													$response = $fire_alarm_response['response'];															
													$message = $fire_alarm_response['message'];
													$time = $fire_alarm_response['time'];
												  
													?>
													<tr>
														<td><?php echo $fire_alarm_response_id; ?></td>
														<td><?php echo $fire_alarm_id; ?></td>
														<td><?php echo $username; ?></td>														
														<td><?php
															echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
														?></td>									
														<?php if($_SESSION['role'] == 1) { ?>
															<td><?php echo $company_name; ?></td>
														<?php } ?>														
														<td><?php
															echo $response == 1 ? 'Safe' : ( $response == 2 ? 'Responded, Not Safe' : ( $response == 3 ? 'Help' : '' ) );
														?></td>
														<td><?php echo $message; ?></td>
														<td><?php echo $time; ?></td>																												
														<td>
															<a href="manage_fire_alarm_response.php?fire_alarm_response_id=<?php echo $fire_alarm_response_id; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="fire_alarm_response.php?delete=fire_alarm_response&id=<?php echo $fire_alarm_response_id; ?>">  Delete </a>
														</td>
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
											
											$collection_fire_alarm_response = new MongoCollection($app_data, 'fire_alarm_response');	
											//$fire_alarm_response = $app_data->fire_alarm_response;
											$cursor = $collection_fire_alarm_response->find( array('company_id' => (int)$user_company_id ) );
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $fire_alarm_response)
												{												
  													$fire_alarm_response_id = $fire_alarm_response['fire_alarm_response_id'];
													$fire_alarm_id = $fire_alarm_response['fire_alarm_id'];
													//Find Fire Alarm
													$collection_fire_alarm = new MongoCollection($app_data, 'fire_alarm');												
													$query_fire_alarm = array( 'fire_alarm_id' => $fire_alarm_id ); 
													$cursor_fire_alarm = $collection_fire_alarm->find( $query_fire_alarm );
													if($cursor_fire_alarm->count() > 0) { 
														foreach($cursor_fire_alarm as $fire_alarm)
														{
															$fire_alarm_trigger_user_id = $fire_alarm['trigger_user_id'];
															$collection_users = new MongoCollection($app_data, 'users');
															$cursor_user = $collection_users->find(array('user_id'=>$fire_alarm_trigger_user_id));
															if($cursor_user->count() > 0)
															{
																foreach($cursor_user as $user)
																{
																	$fire_alarm_trigger_username = $user['username'];
																	$fire_alarm_trigger_user_role = $user['role'];														
																}
															}
															
															$fire_alarm_location_name = $fire_alarm['location_name'];
															$fire_alarm_time = $fire_alarm['time'];
															$fire_alarm_purpose = $fire_alarm['purpose'];															
														}										
													}
													
													$company_id = $fire_alarm_response['company_id'];
													//Find Company name
													$collection_company = new MongoCollection($app_data, 'company');												
													$query_company = array( 'company_ID' => $company_id ); 
													$cursor_company = $collection_company->find( $query_company );
													if($cursor_company->count() > 0) { 
														foreach($cursor_company as $company)
														{
															$company_name = $company['company_name'];
															$company_ref_id = $company['company_ref'];
														}										
													}
														
													$user_id = $fire_alarm_response['user_id'];
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
													
													$response = $fire_alarm_response['response'];															
													$message = $fire_alarm_response['message'];
													$time = $fire_alarm_response['time'];
												  
													?>
													<tr>
														<td><?php echo $fire_alarm_response_id; ?></td>
														<td><?php echo $fire_alarm_id; ?></td>
														<td><?php echo $username; ?></td>														
														<td><?php
															echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
														?></td>									
														<?php if($_SESSION['role'] == 1) { ?>
															<td><?php echo $company_name; ?></td>
														<?php } ?>														
														<td><?php
															echo $response == 1 ? 'Safe' : ( $response == 2 ? 'Responded, Not Safe' : ( $response == 3 ? 'Help' : '' ) );
														?></td>
														<td><?php echo $message; ?></td>
														<td><?php echo $time; ?></td>																												
														<td>
															<a href="manage_fire_alarm_response.php?fire_alarm_response_id=<?php echo $fire_alarm_response_id; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="fire_alarm_response.php?delete=fire_alarm_response&id=<?php echo $fire_alarm_response_id; ?>">  Delete </a>
														</td>
													</tr>
												<?php
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Fire Alarm Response Found </td></tr>';
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