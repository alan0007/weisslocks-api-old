<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'gantry_access' && $_REQUEST['id'] != '')
{
	if($_REQUEST['id'] != 1)
	{
		$collection = new MongoCollection($app_data, 'gantry_access');
		$collection->remove( array( 'gantry_access' =>(int) $_REQUEST['id'] ) );
		$msg = "Deleted Sucessfully!!";
	}
}
if(isset($_REQUEST['gantry_access']) && $_REQUEST['gantry_access'] == 'approve' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'gantry_access');
	
	$criteria = array('gantry_access_id'=>(int)$_REQUEST['id']);
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
                    <h1 class="page-header">IDS Access and Alarm Management</h1>
                </div>
                <div class="col-lg-12">
				
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>ID</th>
											<th>Time</th>
											<th>Location</th>
											<th>Status</th>											
											
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										$open = 0;
										$close = 0;										
										
										if($_SESSION['role'] == 1)
										{											
											//$collection = new MongoCollection($app_data, 'gantry_access');
											$gantry_access_collection = $app_data->gantry_access;
											//$cursor = $collection->find()->sort(array('gantry_access_id'=>-1));
											$cursor = $gantry_access_collection->find();
											//$collection_access = new MongoCollection($app_data, 'gantry_access');
											
											if($cursor->count() > 0)
											{
												foreach($cursor as $gantry_access)
												{
													//var_dump($gantry_access);
													
													$gantry_access_id = $gantry_access['gantry_access_id'];
													$open_close = $gantry_access['open_close'];
													$location = $gantry_access['location'];
													$datetime = $gantry_access['time'];
													//$datetime_new = new DateTime($datetime); //TODO: fix bug
													
													if($open_close == 'open'){
														$open = $open+1;
														$status = 'Disarm/open';
													}
													else if($open_close == 'close'){
														$close = $close+1;
														$status = 'Armed/close';
													}
													
													?>
													<tr>
														<td><?php echo $gantry_access_id; ?></td>
														<td><?php echo $datetime; ?></td>
														<td><?php echo $location; ?></td>										
														<td><?php echo $status; ?></td>													
														
													</tr>
											<?php 
												} 
											}
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Access Log Found </td></tr>';
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
											
											$gantry_access = $app_data->gantry_access;
											$cursor = $gantry_access->find()->sort(array('gantry_access_id'=>-1));
										
											if($cursor->count() > 0)
											{
												foreach($cursor as $gantry_access)
												{
													$gantry_access_id = $gantry_access['gantry_access_id'];
													$open_close = $gantry_access['open_close'];
													$location = $gantry_access['location'];
													$datetime = $gantry_access['time'];
													$datetime_new = new DateTime($datetime);
													$datetime_new = $datetime_now->format('Y-m-d H:i:s');

													?>
													<tr>
														<th><?php echo $gantry_access_id; ?></th>
														<th><?php echo $datetime_new ; ?></th>
														<th><?php echo $location; ?></th>										
														<th><?php echo $open_close; ?></th>													
														
														<!--<th>
															<a href="showusers.php?user_ID=<?php echo $qrcode['user_id']; ?>">  Show </a>
														<?php 
														if($_SESSION['role'] == 1) { ?>
															<a href="manageusers.php?user_ID=<?php echo $qrcode['user_id']; ?>">  Edit </a>
															<?php 
															if($qrcode['user_id'] != 1){ ?>
																<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $qrcode['user_id']; ?>">  Delete </a>
														<?php 
															} 
														} 
														?>
														</th>-->
													</tr>
											<?php } 
											}
											else
											{
												 echo '<tr class="odd gradeX"><td colspan="5">No Access Log Found </td></tr>';
											}
											
										}
									  ?>
									    <tr>
											<th>Total</th>											
											<th>Open: <?php echo $open; ?></th>
											<th>Close: <?php echo $close; ?></th>
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
