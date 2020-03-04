<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'keys' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'keys');
	$collection->remove( array( 'key_ID' =>(int) $_REQUEST['id'] ) );
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
                    <h1 class="page-header">Keys</h1>
                </div>
                <div class="col-lg-12">
					<div style="width:150px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="managekeys.php"> Add Keys </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Keys Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
							
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
										<?php if($_SESSION['role'] == 1) { ?>
											<th>Key ID</th> <?php } ?>
											<th>Key Name</th>
											<th>Company</th>
											<th>Serial Number</th>
											<!--<th>Lock</th>
                                            <th>Serial Number</th>
											<th>status</th>-->
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									  if($_SESSION['role'] == 1) 
										{
									    $keys = $app_data->keys;
										$cursor = $keys->find();
									    if($cursor->count() > 0)
									    {
										  foreach($cursor as $keys)
										  { ?>
											 <tr>
												<th><?php echo $keys['key_ID']; ?></th>
												<th><?php echo $keys['key_name']; ?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'company');
												$check = $collection->find(array('company_ID'=>(int) $keys['company_id']));
												if($check->count() > 0) {
												foreach($check as $users)
												{
													echo $users['company_name'];
												}
												}
												?></th>
												<th><?php echo $keys['key_serial_number']; ?></th>
												<!--<th><?php
												$collection = new MongoCollection($app_data, 'locks');
												$check = $collection->find(array('lock_ID'=>(int) $keys['lock_id']));
												foreach($check as $locks)
												{
													echo $locks['lock_name'];
												}
												?></th>
												<th><?php echo $keys['key_serial_number']; ?></th>
												<th><?php echo $keys['status'] == 1 ? 'Activated' : 'Deactivated'; ?></th>-->
												<th>
												<a href="managekeys.php?key_ID=<?php echo $keys['key_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="keys.php?delete=keys&id=<?php echo $keys['key_ID']; ?>">  Delete </a>
												</th>
											</tr>
											  <?php } }
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="5">No Keys Founds </td></tr>';
										}
										}
										else
											{
												$com = array();
$collection1 = new MongoCollection($app_data, 'company');
	$companies = $collection1->find();
	if($companies->count() > 0) 
	{
		foreach($companies as $company)
		{
			$users = json_decode($company['user_id']);
			if(in_array($current_user,$users))
			{
				$com[] = $company['company_ID'];
			}
		}
	}
										$keys = $app_data->keys;
										$cursor = $keys->find();
										if($cursor->count() > 0)
										{
										  foreach($cursor as $keys)
										  {
										   if(in_array($keys['company_id'],$com))
										  {
										  ?>
											 <tr>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $keys['key_ID']; ?></th> <?php } ?>
												<th><?php echo $keys['key_name']; ?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'company');
												$check = $collection->find(array('company_ID'=>(int) $keys['company_id']));
												if($check->count() > 0) {
													foreach($check as $users)
													{
														echo $users['company_name'];
													}
												}
												?></th>
												<th><?php echo $keys['key_serial_number']; ?></th>
												<!--<th><?php
												$collection = new MongoCollection($app_data, 'locks');
												$check = $collection->find(array('lock_ID'=>(int) $keys['lock_id']));
												foreach($check as $locks)
												{
													echo $locks['lock_name'];
												}
												?></th>
												<th><?php echo $keys['key_serial_number']; ?></th>
												<th><?php echo $keys['status'] == 1 ? 'Activated' : 'Deactivated'; ?></th>-->
												<th>
												<a href="managekeys.php?key_ID=<?php echo $keys['key_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="keys.php?delete=keys&id=<?php echo $keys['key_ID']; ?>">  Delete </a>
												</th>
											</tr>
											  <?php } } } } ?>
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
