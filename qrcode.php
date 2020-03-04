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
                    <h1 class="page-header">Visitor QR Code Management Report</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
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
											<!--<?php echo in_array($_SESSION['role'],array(1,3)) ? '<th>Approve Action</th>' : ''; ?>-->
											<!--<th>Action</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										$in = 0;
										$out = 0;
										if($_SESSION['role'] == 1)
										{
											$qrcode = $app_data->qrcode;
											$cursor = $qrcode->find()->sort(array('qrcode_id'=>-1));
										
										// else
										// {
											// $collection = new MongoCollection($app_data, 'users');
											// $Query = array('user_id' => $_SESSION['user_id'] );
											// $cursor = $collection->find( $Query );
											// foreach($cursor as $uu)
											// {
												// $company_id_user = $uu['company_id'];
											// }
											// $qrcode = $app_data->users;
											// $cursor = $qrcode->find(array('company_id'=>$company_id_user));
										// }
											if($cursor->count() > 0)
											{
											  foreach($cursor as $qrcode)
											  {
												$access_in_out = $qrcode['access_in_out'];
												?>
												 <tr>
												 <?php if($_SESSION['role'] == 1) { ?>
													<th><?php echo $qrcode['qrcode_id']; ?></th>
												 <?php } ?>
													<th><?php echo $qrcode['user_name']; ?></th>
													<th><?php echo $qrcode['permit_id']; ?></th>
													<th><?php
														echo $qrcode['role'];
														//echo $qrcode['role'] == 1 ? 'Super Admin' : ( $qrcode['role'] == 2 ? 'Owner - Admin Company' : ( $qrcode['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $qrcode['role'] == 4 ? 'Contractor' : ( $qrcode['role'] == 5 ? 'Staff' : ( $qrcode['role'] == 6 ? 'Subadmin' : ( $qrcode['role'] == 7 ? 'Visitor' : '' ) ) ) ) ) );
													?></th>
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
														$in = $in+$qrcode['count']+1;
													}
													else if ($qrcode['access_in_out'] == "out") {
														$out = $out+$qrcode['count']+1;
													}
													?>
													</th>-->
												</tr>
											<?php } }
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Permit Found </td></tr>';
											}
										}
										else {
											if ($_SESSION['role'] == 1){
												$user_company_id = $demo_pa_company_id;
											}
											else{
												$collection_user = $app_data->users;
												$query_user = array('user_id' => $_SESSION['user_id'] );
												$cursor_user = $collection_user->find($query_user);
												if($cursor_user->count() > 0)
												{
													foreach($cursor_user as $uu){
														$user_company_id = (int)$uu['company_id'];
														$company_ref_id = $uu['company_ref_id'];
													}
												}
											}
											$query_qrcode = array('company_ref_id' => $company_ref_id );
											
											$qrcode = $app_data->qrcode;
											$cursor = $qrcode->find($query_qrcode)->sort(array('qrcode_id'=>-1));

										// else
										// {
											// $collection = new MongoCollection($app_data, 'users');
											// $Query = array('user_id' => $_SESSION['user_id'] );
											// $cursor = $collection->find( $Query );
											// foreach($cursor as $uu)
											// {
												// $company_id_user = $uu['company_id'];
											// }
											// $qrcode = $app_data->users;
											// $cursor = $qrcode->find(array('company_id'=>$company_id_user));
										// }
											if($cursor->count() > 0)
											{
											  foreach($cursor as $qrcode)
											  {
												$access_in_out = $qrcode['access_in_out'];
												?>
												 <tr>												 
													<th><?php echo $qrcode['qrcode_id']; ?></th>
													<th><?php echo $qrcode['user_name']; ?></th>
													<th><?php echo $qrcode['permit_id']; ?></th>
													<th><?php
														echo $qrcode['role'];
														//echo $qrcode['role'] == 1 ? 'Super Admin' : ( $qrcode['role'] == 2 ? 'Owner - Admin Company' : ( $qrcode['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $qrcode['role'] == 4 ? 'Contractor' : ( $qrcode['role'] == 5 ? 'Staff' : ( $qrcode['role'] == 6 ? 'Subadmin' : ( $qrcode['role'] == 7 ? 'Visitor' : '' ) ) ) ) ) );
													?></th>
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
														$in = $in+$qrcode['count']+1;
													}
													else if ($qrcode['access_in_out'] == "out") {
														$out = $out+$qrcode['count']+1;
													}
													?>
													</th>-->
												</tr>
												
											<?php } }
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Permit Found </td></tr>';
											}
										}
									  ?>
									    <tr>
											<th>Total</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th>In:</th>
											<th><?php echo $in; ?></th>
											<th>Out:</th>
											<th><?php echo $out; ?></th>
											<th></th>
											<!--<?php echo in_array($_SESSION['role'],array(1,3)) ? '<th>Approve Action</th>' : ''; ?>-->
											<!--<th>Action</th>-->
                                        </tr>

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
