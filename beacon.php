<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];

if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'beacon' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$collection->remove( array( 'beacon_id' =>(int) $_REQUEST['beacon_id'] ) );
	$msg = "Deleted Sucessfully!!";
}

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
                    <h1 class="page-header">Beacon</h1>
                </div>
                <div class="col-lg-12">
				<div style="width:180px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="managebeacon.php"> Add Beacon </a></div>
                    <?php if(isset($_REQUEST['success'])){?>
                    <div style="color:green;text-align: center;">Beacon Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Beacon ID</th>
											<th>Beacon Name</th>
											<th>Color</th>
											<?php if($_SESSION['role'] == 1) { ?>
												<th>Eddystone Namespace</th>
												<th>Eddystone Instance</th>												
											<?php } ?>
											<th>iBeacon UUID</th>
											<th>iBeacon Major</th>
											<th>iBeacon Minor</th>
											<?php if($_SESSION['role'] == 1) { ?>												
												<th>Company</th>
											<?php } ?>
											<th>Building</th>
											<th>Beacon Location</th>											
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
										<?php
										if($_SESSION['role'] == 1)
										{
											$beacon = $app_data->beacon;
											$cursor = $beacon->find();
											//$collection = new MongoCollection($app_data, 'beacon');
											//$cursor = $collection->find();

											if($cursor->count() > 0)
											{
												foreach($cursor as $beacon)
												{
													$beacon_id = $beacon['beacon_id'];
													$beacon_name = $beacon['beacon_name'];
													$beacon_color = $beacon['beacon_color'];
													$beacon_type = $beacon['beacon_type'];
													$beacon_geo_location = $beacon['beacon_geo_location'];
													$eddystone_uid = $beacon['eddystone_uid'];
													$eddystone_namespace_id = $beacon['eddystone_namespace_id'];
													$eddystone_instance_id = $beacon['eddystone_instance_id'];
													$iBeacon_UUID = $beacon['iBeacon_UUID'];
													$iBeacon_major = $beacon['iBeacon_major'];
													$iBeacon_minor = $beacon['iBeacon_minor'];
													$company_id = $beacon['company_id'];
													//Find company name and ref ID
													
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
																									
													$building_id = $beacon['building_id'];
													$building_name = $beacon['building_name'];
													$location_id = $beacon['location_id'];
													$location_name = $beacon['location_name'];
													$beacon_location_id = $beacon['beacon_location_id'];
													$beacon_location_name = $beacon['beacon_location_name'];
													$battery_lifetime = $beacon['battery_lifetime'];
													$battery_lifetime_end = $beacon['battery_lifetime_end'];												
												?>
												<tr>
													<td><?php echo $beacon_id; ?></td>
													<td><?php echo $beacon_name; ?></td>
													<td><?php echo $beacon_color; ?></td>
													<td><?php echo $eddystone_namespace_id; ?></td>
													<td><?php echo $eddystone_instance_id; ?></td>
													<td><?php echo $iBeacon_UUID; ?></td>
													<td><?php echo $iBeacon_major; ?></td>
													<td><?php echo $iBeacon_minor; ?></td>
													<td><?php echo $company_name; ?></td>
													<td><?php echo $building_name; ?></td>
													<td><?php echo $beacon_location_name; ?></td>
													<!-- Action -->
													<td>													
														<a href="managebeacon.php?beacon_id=<?php echo $beacon['beacon_id']; ?>">  Edit </a>
														<a onclick="return confirm('Are you sure?')" href="beacon.php?delete=beacon&id=<?php echo $beacon['beacon_id']; ?>">  Delete </a>
													</td>
												</tr>
												<?php
												}
											}												
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Beacon Found </td></tr>';
											}
										}
										else {
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
											
											$collection_beacon = $app_data->beacon;
											$cursor_beacon = $collection_beacon->find(array('company_id' => $user_company_id ));
											//$query_beacon = array('company_id' => $user_company_id );
											//$cursor_beacon = $collection_beacon->find($query_beacon);
										
											if($cursor_beacon->count() > 0)
											{												
												foreach($cursor_beacon as $beacon)
												{
													$beacon_id = $beacon['beacon_id'];
													$beacon_name = $beacon['beacon_name'];
													$beacon_color = $beacon['beacon_color'];
													$beacon_type = $beacon['beacon_type'];
													$beacon_geo_location = $beacon['beacon_geo_location'];
													$eddystone_uid = $beacon['eddystone_uid'];
													$eddystone_namespace_id = $beacon['eddystone_namespace_id'];
													$eddystone_instance_id = $beacon['eddystone_instance_id'];
													$iBeacon_UUID = $beacon['iBeacon_UUID'];
													$iBeacon_major = $beacon['iBeacon_major'];
													$iBeacon_minor = $beacon['iBeacon_minor'];
													$company_id = $beacon['company_id'];
													//Find user's company, company name and ref ID
													$collection_company = $app_data->company;
													$query_company = array( 'company_id' => $company_id ); 
													$cursor_company = $collection_company->find( $query_company );
													if($cursor_company->count() > 0) { 
														foreach($cursor_company as $company)
														{
															$company_name = $company['company_name'];
															$company_ref_id = $company['company_ref'];														
														}
													}
													
													$building_id = $beacon['building_id'];
													$building_name = $beacon['building_name'];
													$location_id = $beacon['location_id'];
													$location_name = $beacon['location_name'];
													$beacon_location_id = $beacon['beacon_location_id'];
													$beacon_location_name = $beacon['beacon_location_name'];
													$battery_lifetime = $beacon['battery_lifetime'];
													$battery_lifetime_end = $beacon['battery_lifetime_end'];
												?>
												<tr>
													<td><?php echo $beacon_id; ?></td>
													<td><?php echo $beacon_name; ?></td>
													<td><?php echo $beacon_color; ?></td>
													<?php /*
													<td><?php echo $eddystone_namespace_id; ?></td>
													<td><?php echo $eddystone_instance_id; ?></td>
													<td><?php echo $company_name; ?></td>
													*/ ?>
													<td><?php echo $iBeacon_UUID; ?></td>
													<td><?php echo $iBeacon_major; ?></td>
													<td><?php echo $iBeacon_minor; ?></td>
													<td><?php echo $building_name; ?></td>
													<td><?php echo $beacon_location_name; ?></td>
													<!-- Action -->
													<td>													
													<?php if($_SESSION['role'] == 1) { ?>
														<a href="managebeacon.php?user_ID=<?php echo $beacon['user_id']; ?>">  Edit </a>
														<?php if($beacon['beacon_id'] != 1){ ?>
															<a onclick="return confirm('Are you sure?')" href="managebeacon.php?delete=beacon&id=<?php echo $beacon['beacon_id']; ?>">  Delete </a>
													<?php }
													} ?>
													</td>
												</tr>
												<?php
												}
											}											
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Beacon Found </td></tr>';
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
