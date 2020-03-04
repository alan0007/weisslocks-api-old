<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'user_location' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'user_location');
	$collection->remove( array( 'user_location_id' =>(int) $_REQUEST['id'] ) );
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
		
			<!-- Beacon Tracking -->
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Active User Detected by Beacon Report</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">
                                	  <?php
										//Temporary exclusion: 265,279,361
										$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364,265,279,361);
								
											$previous_user_id = array();
																					
											$user_location = $app_data->user_location;
											//$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['host_name'] ), array('email'=>$_REQUEST['host_email_phone']), array('phone_number'=>$_REQUEST['host_email_phone'])  ) );
											//$query_user_location= array( '$ne' => array( array('user_id' => 185), array('user_id'=>186), array('user_id'=>196), array('user_id'=>252), array('user_id'=>237), array('user_id'=>238), array('user_id'=>239)  ) );
											//$query_user_location= array( '$and' => array( array('user_id' => array( '$ne' => 185, '$ne' => 186)), array('user_id' => array( '$ne' => 186)), array('user_id' => array( '$ne' => 196)), array('user_id' => array( '$ne' => 237)), array('user_id' => array( '$ne' => 238)), array('user_id' => array( '$ne' => 239))  ) );
											
											$search = array(	
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
													//Checking for SP Only
													array( 'company_id'=> 12 )													
												)
											);
											
											$cursor = $user_location->find($search);
											//$cursor = $user_location->find();
											$retval = $user_location->distinct("user_id",$search);
											//var_dump($retval);
											$arrayCount = count($retval);
											echo "Total Active Users: " . $arrayCount . "<br><br>";
											$i = 0;
											?>
											
								<table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Number</th>
											<th>User Name</th>
											<th>First Beacon Name</th>
											<th>First Beacon Time</th>											
											<th>Last Beacon Name</th>
											<th>Last Beacon Time</th>											
											<!--<th>Action</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php 										
										for ($i; $i<=$arrayCount-1; $i++){
											//echo $retval[$i];	
											$user_location = $app_data->user_location;
											$query = array("user_id"=>$retval[$i]);
											$cursor = $user_location->find($query)->sort(array("user_location_id"=>-1))->limit(1);
											
											$number = $i +1;
											$previous_user = "";											
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $tracking)
												{
													$current_user = $tracking['user_id'];
													$user_id = $tracking['user_id'];
													if ( !in_array($user_id, $user_exclude_list) ){
														//Find Username
														$collection_users = new MongoCollection($app_data, 'users');
														$cursor_user = $collection_users->find(array('user_id'=>$current_user));
														if($cursor_user->count() > 0)
														{
															foreach($cursor_user as $user)
															{
																$username = $user['username'];
																$role = $user['role'];														
															}
														}
														//Get Last Known Time
														$first_beacon = "";
														$first_detected_time = "";
														
														$qrcode_first = $app_data->user_location;
														$query_first = array("user_id"=>$current_user);
														$cursor_first = $qrcode_first->find($query_first);
														$cursor_first = $cursor_first->sort(array('qrcode_id' => -1));
														if($cursor_first->count() > 0)
														{
															foreach($cursor_first as $location)
															{
																if ($first_beacon == ""){
																	$first_beacon = $location['beacon_name'];
																	$first_detected_time = $location['location_time'];
																}
															}
														}
														
														if ($current_user != $previous_user){
														?>												
															<tr>
																<th><?php echo $number; ?></th>	
																
																<th><?php echo $username; ?></th>
																
																<th><?php echo $first_beacon; ?></th>															
																<th><?php echo $first_detected_time; ?></th>
																
																<th><?php echo $tracking['beacon_name']; ?></th>															
																<th><?php echo $tracking['location_time']; ?></th>
															</tr>
														
													<?php
														}
													}													
													$previous_user = $tracking['user_id'];
												} 
											}
										}
										
										?>
                                    </tbody>
                                </table>
								
											<?php											
											/*
											if($cursor->count() > 0)
											{
												$cursor->sort(array('user_id' => 1));
												
												foreach($cursor as $user_location)
												{
													$user_location_id = $user_location['user_location_id'];
													$user_id = $user_location['user_id'];
													$previous_user_id[] = $user_location['user_id'];
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
													
													$company_id = $user_location['company_id'];
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
														
													$beacon_id = $user_location['beacon_id'];
													$beacon_name = $user_location['beacon_name'];
													$eddystone_uid = $user_location['eddystone_uid'];
													$eddystone_namespace_id = $user_location['eddystone_namespace_id'];
													$eddystone_instance_id = $user_location['eddystone_instance_id'];
													$iBeacon_UUID = $user_location['iBeacon_UUID'];
													$iBeacon_major = $user_location['iBeacon_major'];
													$iBeacon_minor = $user_location['iBeacon_minor'];
													$location_time = $user_location['location_time'];
												  
													if (in_array($user_id, $previous_user_id, false)){
													?>
														<tr>
															<td><?php echo $user_location_id; ?></td>
															<td><?php echo $username; ?></td>
															<td><?php
																echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
															?></td>
															<td><?php echo $company_name; ?></td>
															<td><?php echo $beacon_name; ?></td>
															<td><?php echo $location_time; ?></td>						
															<!--<td>
																<a href="manageuserlocation.php?user_id=<?php echo $user_location_id; ?>">  Edit </a>
																<a onclick="return confirm('Are you sure?')" href="user_location.php?delete=users&id=<?php echo $qrcode['user_id']; ?>">  Delete </a>
															</td>-->
														</tr>
													<?php	
													}																																						
													
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Tracking Report Found </td></tr>';
											}
											*/
										/*
										} else {
											$collection_user = new MongoCollection($app_data, 'users');
											$query_user = array('user_id' => $_SESSION['user_id'] );
											$cursor_user = $collection_user->find($query_user);
											if($cursor_user->count() > 0)
											{
												foreach($cursor_user as $uu){
													$company_id = $uu['company_id'];
												}
											}
											
/*											
											$user_location = $app_data->user_location;
											$cursor = $user_location->find(array('company_id' => $company_id ));
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $user_location)
												{
													$user_location_id = $user_location['user_location_id'];
													$user_id = $user_location['user_id'];
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
													
													$company_id = $user_location['company_id'];
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
														
													$beacon_id = $user_location['beacon_id'];
													$beacon_name = $user_location['beacon_name'];
													$eddystone_uid = $user_location['eddystone_uid'];
													$eddystone_namespace_id = $user_location['eddystone_namespace_id'];
													$eddystone_instance_id = $user_location['eddystone_instance_id'];
													$iBeacon_UUID = $user_location['iBeacon_UUID'];
													$iBeacon_major = $user_location['iBeacon_major'];
													$iBeacon_minor = $user_location['iBeacon_minor'];
													$location_time = $user_location['location_time'];
												  
													?>
													<tr>
														<td><?php echo $user_location_id; ?></td>
														<td><?php echo $username; ?></td>
														<td><?php
															echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin' : ( $role == 4 ? 'Contractor' : ( $role == 5 ? 'Staff' : ( $role == 6 ? 'Subadmin' : ( $role == 7 ? 'Visitor' : '' ) ) ) ) ) );
														?></td>
														<td><?php echo $company_name; ?></td>
														<td><?php echo $beacon_name; ?></td>
														<td><?php echo $location_time; ?></td>						
														<td>
															<a href="manageuserlocation.php?user_id=<?php echo user_location_id; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="user_location.php?delete=user_location&id=<?php echo $user_location_id; ?>">  Delete </a>
														</td>
													</tr>
												<?php
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Tracking Report Found </td></tr>';
											}

										}
*/										
										?>								
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
