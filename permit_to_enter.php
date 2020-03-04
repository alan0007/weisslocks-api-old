<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'permit_to_enter' && $_REQUEST['id'] != '')
{
	if($_REQUEST['id'] != 1)
	{
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		$collection->remove( array( 'permit_id' =>(int) $_REQUEST['id'] ) );
		$msg = "Deleted Sucessfully!!";
	}
}
if(isset($_REQUEST['permit_to_enter']) && $_REQUEST['permit_to_enter'] == 'approve' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'permit_to_enter');
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
                    <h1 class="page-header">Permit To Enter</h1>
                </div>
                <div class="col-lg-12">
				<div style="width:180px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="manageusers.php"> Add User </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Permit Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Permit ID</th>
											<th>User Name</th>
											<th>Visiting Ref Company ID</th>
											<th>Role</th>
											<th>Location</th>
											<th>Host</th>
											<th>Email</th>
											<th>Date_from</th>
											<th>Date_to</th>
											<th>Time_from</th>
											<th>Time_to</th>
											<th>Registered Time</th>
											<th>Approved</th>
											<th>Subadmin Approved</th>
											<th>Admin Approved</th>
											<?php echo in_array($_SESSION['role'],array(1,3)) ? '<th>Approve Action</th>' : ''; ?>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										if($_SESSION['role'] == 1)
										{
											$permit_to_enter = $app_data->permit_to_enter;
											$cursor = $permit_to_enter->find();
										
										// else
										// {
											// $collection = new MongoCollection($app_data, 'users');
											// $Query = array('user_id' => $_SESSION['user_id'] );
											// $cursor = $collection->find( $Query );
											// foreach($cursor as $uu)
											// {
												// $company_id_user = $uu['company_id'];
											// }
											// $permit_to_enter = $app_data->users;
											// $cursor = $permit_to_enter->find(array('company_id'=>$company_id_user));
										// }
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $permit_to_enter)
										  {
										  ?>
											 <tr>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $permit_to_enter['permit_id']; ?></th>
											 <?php } ?>
												<th><?php echo $permit_to_enter['username']; ?></th>
												<th><?php echo $permit_to_enter['company_ref_id']; ?></th>
												<th><?php
													echo $permit_to_enter['role'] == 1 ? 'Super Admin' : ( $permit_to_enter['role'] == 2 ? 'Owner - Admin Company' : ( $permit_to_enter['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $permit_to_enter['role'] == 4 ? 'Contractor' : ( $permit_to_enter['role'] == 5 ? 'Staff' : ( $permit_to_enter['role'] == 6 ? 'Subadmin' : ( $permit_to_enter['role'] == 7 ? 'Visitor' : '' ) ) ) ) ) );
												?></th>
												<th><?php echo $permit_to_enter['location']; ?></th>
												<th><?php echo $permit_to_enter['host_name']; ?></th>
												<th><?php echo $permit_to_enter['host_email_phone']; ?></th>
												<th><?php echo $permit_to_enter['date_from']; ?></th>
												<th><?php echo $permit_to_enter['date_to']; ?></th>
												<th><?php echo $permit_to_enter['time_from']; ?></th>
												<th><?php echo $permit_to_enter['time_to']; ?></th>
												<th><?php echo $permit_to_enter['registered_time']; ?></th>
												<!-- Approved Status -->										
												<th><?php  echo $permit_to_enter['approved'] == 2 ? 'Disapproved' : ($permit_to_enter['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
												<th><?php  echo $permit_to_enter['subadmin_approved'] == 2 ? 'Disapproved' : ($permit_to_enter['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
												<th><?php  echo $permit_to_enter['admin_approved'] == 2 ? 'Disapproved' : ($permit_to_enter['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
												<!-- Action -->
												<th>
													<a href="showusers.php?user_ID=<?php echo $permit_to_enter['user_id']; ?>">  Show </a>
												<?php if($_SESSION['role'] == 1) { ?>
													<a href="manageusers.php?user_ID=<?php echo $permit_to_enter['user_id']; ?>">  Edit </a>
													<?php if($permit_to_enter['user_id'] != 1){ ?>
														<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $permit_to_enter['user_id']; ?>">  Delete </a>
												<?php } } ?>
												</th>
											</tr>
									  <?php } }
									  else
										{
											 echo '<tr class="odd gradeX"><td colspan="5">No Permit Found </td></tr>';
										}
									  } else {
											$permit_to_enter = $app_data->permit_to_enter;
											$cursor = $permit_to_enter->find();
										
										// else
										// {
											// $collection = new MongoCollection($app_data, 'users');
											// $Query = array('user_id' => $_SESSION['user_id'] );
											// $cursor = $collection->find( $Query );
											// foreach($cursor as $uu)
											// {
												// $company_id_user = $uu['company_id'];
											// }
											// $permit_to_enter = $app_data->users;
											// $cursor = $permit_to_enter->find(array('company_id'=>$company_id_user));
										// }
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $permit_to_enter)
										  {
										  ?>
											 <tr>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $permit_to_enter['permit_id']; ?></th>
											 <?php } ?>
												<th><?php echo $permit_to_enter['username']; ?></th>
												<th><?php echo $permit_to_enter['company_ref_id']; ?></th>
												<th><?php echo $permit_to_enter['email']; ?></th>
												<th><?php
													echo $permit_to_enter['role'] == 1 ? 'Super Admin' : ( $permit_to_enter['role'] == 2 ? 'Owner - Admin Company' : ( $permit_to_enter['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $permit_to_enter['role'] == 4 ? 'Contractor' : ( $permit_to_enter['role'] == 5 ? 'Staff' : ( $permit_to_enter['role'] == 6 ? 'Subadmin' : ( $permit_to_enter['role'] == 7 ? 'Visitor' : '' ) ) ) ) ) );
												?></th>
												<th><?php echo $permit_to_enter['location']; ?></th>
												<th><?php echo $permit_to_enter['host_name']; ?></th>
												<th><?php echo $permit_to_enter['host_email_phone']; ?></th>
												<th><?php echo $permit_to_enter['date_from']; ?></th>
												<th><?php echo $permit_to_enter['date_to']; ?></th>
												<th><?php echo $permit_to_enter['time_from']; ?></th>
												<th><?php echo $permit_to_enter['time_to']; ?></th>
												<!-- Approved Status -->										
												<th><?php  echo $permit_to_enter['approved'] == 2 ? 'Disapproved' : ($permit_to_enter['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
												<!-- Action -->
												<th>
													<a href="showusers.php?user_ID=<?php echo $permit_to_enter['user_id']; ?>">  Show </a>
												<?php if($_SESSION['role'] == 1) { ?>
													<a href="manageusers.php?user_ID=<?php echo $permit_to_enter['user_id']; ?>">  Edit </a>
													<?php if($permit_to_enter['user_id'] != 1){ ?>
														<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $permit_to_enter['user_id']; ?>">  Delete </a>
												<?php } } ?>
												</th>
											</tr>
									  <?php } }
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
