<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'qrcode' && $_REQUEST['id'] != '')
{
	if($_REQUEST['id'] != 1)
	{
		$collection = new MongoCollection($app_data, 'qrcode');
		$collection->remove( array( 'qrcode_id' =>(int) $_REQUEST['id'] ) );
		$msg = "Deleted Sucessfully!!";
	}
}
if(isset($_REQUEST['qrcode']) && $_REQUEST['qrcode'] == 'approve' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'qrcode');
	
	$criteria = array('user_id'=>(int)$_REQUEST['id']);
	$collection->update( $criteria ,array('$set' => array(
				'approved'  => 1
		)));
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
                    <h1 class="page-header">Active User Who Scanned QR Code Report</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">							
							<?php
								
								//Temporary exclusion: 265,279,361
								$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364,265,279,361);
								$user_qrcode_list = array();
								
								$qrcode_count = $app_data->qrcode;
								$cursor_count = $qrcode_count->find();
								if($cursor_count->count() > 0){
									foreach($cursor_count as $qrcode){
										$user_id = $qrcode['user_id'];
										if ( !in_array($user_id, $user_exclude_list) )
										{
											if (!in_array($user_id, $user_qrcode_list)){
												array_push($user_qrcode_list,$user_id);
											}
										}
									}
								}
								$user_qrcode_count = sizeof($user_qrcode_list);
								$user_qrcode_list = $user_qrcode_list;
									
								$qrcode = $app_data->qrcode;
								//$cursor = $qrcode->find();
								
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
										array( 'user_id'=> array('$ne'=>255) )
									)
								);											
								$retval = $qrcode->distinct("user_id",$search);
								//var_dump($retval);
								
								$arrayCount = count($retval);
								echo "Total Active Users: " . $user_qrcode_count . "<br><br>";
								$i = 0;
								?>
							
								<table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Number</th>
											<th>Active Username</th>
											<th>Role</th>											
											<th>First Access Time</th>	
											<th>Last Access Time</th>												
											<!--<th>Action</th>-->
                                        </tr>
                                    </thead>
									<tbody>									
									<?php 										
										for ($i; $i<=$arrayCount-1; $i++){
											//echo $retval[$i];	
											$qrcode = $app_data->qrcode;
											$query = array("user_id"=>$retval[$i]);
											$cursor = $qrcode->find($query);
											
											$number = $i +1;
											$previous_user = "";
											$first_known_time = "";
											$last_known_time ="";
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $qrcode)
												{
													$current_user_id = $qrcode['user_id'];
													$current_user = $qrcode['user_name'];
													
													//Check first value and update until last value
													if ($first_known_time == ""){
														$first_known_time = $qrcode['access_time'];
													}
													//Get Last Known Time
													$qrcode_last = $app_data->qrcode;
													$query_last = array("user_id"=>$current_user_id);
													$cursor_last = $qrcode_last->find($query_last);
													$cursor_last = $cursor_last->sort(array('qrcode_id' => -1));
													if($cursor_last->count() > 0)
													{
														foreach($cursor_last as $qrcode)
														{
															if ($last_known_time == ""){
																$last_known_time = $qrcode['access_time'];
															}
														}
													}
													
													
													if ($current_user != $previous_user){
														
													?>												
														<tr>
															<th><?php echo $number; ?></th>															
															<th><?php echo $qrcode['user_name']; ?></th>
															<th><?php echo $qrcode['role']; ?></th>															
															<th><?php echo $first_known_time; ?></th>
															<th><?php echo $last_known_time; ?></th>
														</tr>
													
												<?php
													}												
													$previous_user = $qrcode['user_name'];
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
				
				<div class="col-lg-12">
                    <h3 class="page-header">Active User Full List</h3>
                </div>
				
				<div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">							
							<?php
									
								
									
									$qrcode = $app_data->qrcode;									
									$cursor_full = $qrcode->find();
									$cursor_full = $cursor_full->sort(array('qrcode_id' => -1));
									/*
									$search = array(	
										'$and' => array( 
											array( 'user_id'=> array('$ne'=>1) ),
											array( 'user_id'=> array('$ne'=>179) ),
											array( 'user_id'=> array('$ne'=>185) ),
											array( 'user_id'=> array('$ne'=>186) ), 
											array( 'user_id'=> array('$ne'=>187) ),
											array( 'user_id'=> array('$ne'=>188) ),
											array( 'user_id'=> array('$ne'=>189) ),
											array( 'user_id'=> array('$ne'=>196) ),
											array( 'user_id'=> array('$ne'=>252) ),
											array( 'user_id'=> array('$ne'=>237) ),
											array( 'user_id'=> array('$ne'=>238) ),
											array( 'user_id'=> array('$ne'=>239) ),
											array( 'user_id'=> array('$ne'=>201) ),
											array( 'user_id'=> array('$ne'=>255) )
										)
									);											
									$retval = $qrcode->distinct("user_id",$search);
									//var_dump($retval);
									
									$arrayCount = count($retval);
									//echo "Total Active Users: " . $arrayCount . "<br>";
									$i = 0;
									*/
									
									if($cursor_full->count() > 0)
									{
									$number = 1;
								?>
							
									<table class="table table-striped table-bordered table-hover" id="dataTables-example">
									<thead>
										<tr>
											<th>Number</th>
											<th>QR Code ID</th>
											<th>User Name</th>
											<th>Permit ID</th>
											<th>Role</th>
											<th>Location</th>
											<th>Access (In/Out)</th>
											<th>Access Time</th>
											<th>Valid from</th>
											<th>Valid to</th>
											<th>Company Ref. ID</th>
											<th>Count</th>
											<th>Time</th>
											<th>Visitor Company Name</th>											
											<!--<th>Action</th>-->
										</tr>
									</thead>
                                    <tbody>
										<?php 
										foreach($cursor_full as $qrcode)
										{
											$user_id=$qrcode['user_id'];
											if ( !in_array($user_id, $user_exclude_list) && in_array($user_id, $user_qrcode_list)){												
											?>											
												<tr>
													<th><?php echo $number; ?></th>
													<th><?php echo $qrcode['qrcode_id']; ?></th>
													<th><?php echo $qrcode['user_name']; ?></th>
													<th><?php echo $qrcode['permit_id']; ?></th>
													<th><?php echo $qrcode['role']; ?></th>
													
													<th><?php echo $qrcode['location']; ?></th>
													<th><?php echo $qrcode['access_in_out']; ?></th>
													<th><?php echo $qrcode['access_time']; ?></th>
													<th><?php echo $qrcode['valid_from']; ?></th>
													<th><?php echo $qrcode['valid_to']; ?></th>
													<th><?php echo $qrcode['company_ref_id']; ?></th>
													<th><?php echo $qrcode['count']; ?></th>
													<th><?php echo $qrcode['time']; ?></th>
													<!-- Approved Status -->										
													<th><?php  echo $qrcode['visitor_company_name'];?></th> <!-- == 2 ? 'Disapproved' : ($qrcode['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>-->
													<!--<th><?php  echo $qrcode['subadmin_approved'] == 2 ? 'Disapproved' : ($qrcode['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
													<th><?php  echo $qrcode['admin_approved'] == 2 ? 'Disapproved' : ($qrcode['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>-->
													<!-- Action -->
													<!--<th>
														<a href="showusers.php?user_ID=<?php echo $qrcode['user_id']; ?>">  Show </a>
													<?php if($_SESSION['role'] == 1) { ?>
														<a href="manageusers.php?user_ID=<?php echo $qrcode['user_id']; ?>">  Edit </a>
														<?php if($qrcode['user_id'] != 1){ ?>
															<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $qrcode['user_id']; ?>">  Delete </a>
													<?php } } 
													if ($qrcode['access_in_out'] == "in") {
													$in = $in+$qrcode['count'];
													}
													else {
														$out = $out+$qrcode['count'];
													}
													?>
													</th>-->
												</tr>
											<?php
											//Increase number
											$number++;
											}
										}
									
									}
									/*
										for ($i; $i<=$arrayCount-1; $i++){
											//echo $retval[$i];	
											$qrcode = $app_data->qrcode;
											$query = array("user_id"=>$retval[$i]);
											$cursor = $qrcode->find($query);
											
											$number = $i +1;
											$previous_user = "";
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $qrcode)
												{
													$current_user = $qrcode['user_name'];
													//if ($current_user != $previous_user){
													?>												
														<tr>
															<th><?php echo $number; ?></th>
															<th><?php echo $qrcode['qrcode_id']; ?></th>
															<th><?php echo $qrcode['user_name']; ?></th>
															<th><?php echo $qrcode['permit_id']; ?></th>
															<th><?php echo $qrcode['role']; ?></th>
															
															<th><?php echo $qrcode['location']; ?></th>
															<th><?php echo $qrcode['access_in_out']; ?></th>
															<th><?php echo $qrcode['access_time']; ?></th>
															<th><?php echo $qrcode['valid_from']; ?></th>
															<th><?php echo $qrcode['valid_to']; ?></th>
															<th><?php echo $qrcode['company_ref_id']; ?></th>
															<th><?php echo $qrcode['count']; ?></th>
															<th><?php echo $qrcode['time']; ?></th>
															<!-- Approved Status -->										
															<th><?php  echo $qrcode['visitor_company_name'];?></th> <!-- == 2 ? 'Disapproved' : ($qrcode['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>-->
															<!--<th><?php  echo $qrcode['subadmin_approved'] == 2 ? 'Disapproved' : ($qrcode['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
															<th><?php  echo $qrcode['admin_approved'] == 2 ? 'Disapproved' : ($qrcode['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>-->
															<!-- Action -->
															<!--<th>
																<a href="showusers.php?user_ID=<?php echo $qrcode['user_id']; ?>">  Show </a>
															<?php if($_SESSION['role'] == 1) { ?>
																<a href="manageusers.php?user_ID=<?php echo $qrcode['user_id']; ?>">  Edit </a>
																<?php if($qrcode['user_id'] != 1){ ?>
																	<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $qrcode['user_id']; ?>">  Delete </a>
															<?php } } 
															if ($qrcode['access_in_out'] == "in") {
															$in = $in+$qrcode['count'];
															}
															else {
																$out = $out+$qrcode['count'];
															}
															?>
															</th>-->
														</tr>
													
												<?php
													//}												
													$previous_user = $qrcode['user_name'];
												} 
											}
										}
									*/	
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
