<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'fire_alarm' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'fire_alarm');
	$collection->remove( array( 'fire_alarm_id' =>(int) $_REQUEST['id'] ) );
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
                    <h1 class="page-header">Emergency Alarm</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>ID</th>
											<?php if($_SESSION['role'] == 1) { ?>
												<th>Company</th>
											<?php } ?>
											<th>Triggered by</th>
											<th>User Role</th>
											<th>Location</th>
											<th>Time</th>
											<th>Purpose</th>
											<th>Message</th>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										if($_SESSION['role'] == 1)
										{
											$fire_alarm = $app_data->fire_alarm;
											$cursor = $fire_alarm->find();
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $fire_alarm)
												{													
  													$fire_alarm_id = $fire_alarm['fire_alarm_id'];
													$company_id = $fire_alarm['company_id'];
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
													
													$location_id = $fire_alarm['location_id'];
													$location_name = $fire_alarm['location_name'];
													$time = $fire_alarm['time'];
													$purpose = $fire_alarm['purpose'];
													$message = $fire_alarm['message'];												  
													?>
													<tr>
														<td><?php echo $fire_alarm_id; ?></td>														
														<?php if($_SESSION['role'] == 1) { ?>
															<td><?php echo $company_name; ?></td>
														<?php } ?>
														<td><?php echo $username; ?></td>
														<td><?php
															echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
														?></td>
														<td><?php echo $location_name; ?></td>
														<td><?php echo $time; ?></td>
														<td><?php echo $purpose; ?></td>
														<td><?php echo $message; ?></td>														
														<td>
															<a href="manage_fire_alarm.php?fire_alarm_id=<?php echo $fire_alarm_id; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="fire_alarm.php?delete=fire_alarm&id=<?php echo $fire_alarm_id; ?>">  Delete </a>
														</td>
													</tr>
												<?php
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Permit Found </td></tr>';
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
																						
											$fire_alarm = $app_data->fire_alarm;
											$cursor = $fire_alarm->find( array('company_id' => $user_company_id ) );											
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $fire_alarm)
												{													
  													$fire_alarm_id = $fire_alarm['fire_alarm_id'];
													$company_id = $fire_alarm['company_id'];
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
													
													$location_id = $fire_alarm['location_id'];
													$location_name = $fire_alarm['location_name'];
													$time = $fire_alarm['time'];
													$purpose = $fire_alarm['purpose'];
													$message = $fire_alarm['message'];												  
													?>
													<tr>
														<td><?php echo $fire_alarm_id; ?></td>														
														<?php if($_SESSION['role'] == 1) { ?>
															<td><?php echo $company_name; ?></td>
														<?php } ?>
														<td><?php echo $username; ?></td>
														<td><?php
															echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
														?></td>
														<td><?php echo $location_name; ?></td>
														<td><?php echo $time; ?></td>
														<td><?php echo $purpose; ?></td>
														<td><?php echo $message; ?></td>														
														<td>
															<a href="manage_fire_alarm.php?fire_alarm_id=<?php echo fire_alarm_id; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="fire_alarm.php?delete=fire_alarm&id=<?php echo $qrcode['user_id']; ?>">  Delete </a>
														</td>
													</tr>
												<?php
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Permit Found </td></tr>';
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
