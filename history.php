<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
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
                    <h1 class="page-header">History</h1>
                </div>
                <div class="col-lg-12">
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
										<?php if($_SESSION['role'] == 1) { ?>
											<th>User ID</th>
										<?php } ?>
											<th>User Name</th>
											<th>Company ID</th>
											<th>Key ID</th>
											<th>Last Login</th>
											<th>Access Code</th>
											<th>User Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									   if($_SESSION['role'] == 1) 
										{
										$users = $app_data->users;
									  $cursor = $users->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $user)
										  {
											 if($user['user_id'] != 1) {
										  ?>
											 <tr>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $user['user_id']; ?></th>
												<?php } ?>
												<th><?php echo $user['username']; ?>
												<br/> ( <?php echo $user['full_name']; ?>)
												</th>
												<th><?php $collection = new MongoCollection($app_data, 'company');
													 $companys = $collection->find(array('company_ID'=>(int) $user['company_id']));
													 foreach($companys as $company)
													 {
														echo $company['company_name'];
													 }
												?></th>
												<th><?php
												$key_id = json_decode($user['key_id']);
												$collection = new MongoCollection($app_data, 'keys');
												for($i=0;$i<=count($key_id);$i++)
												{
													 $companys = $collection->find(array('key_ID'=>(int) $key_id[$i]));
													 foreach($companys as $company)
													 {
														echo $company['key_name'] . '<br/>';
													 }
												}
												?></th>
												<th><?php echo isset($user['last_login']) ? $user['last_login'] : ''; ?></th>
												<th><?php
												$user_id = $user['user_id'];
												$collection_histry = new MongoCollection($app_data, 'history_log');
												$cursor = $collection_histry->find(array('user_id'=>$user_id));
												$j=0;
												foreach($cursor as $history)
												{ $j++;
													$his_locks = $app_data->locks;
													$cursor_locks = $his_locks->find(array('lock_ID'=>$history['lock_id']));
													foreach($cursor_locks as $lokcs)
													{
														echo $j .'. Lock : '.$lokcs['lock_name'] . ' ==> Access Code : ' . $history['access_code'];
														echo ' -- Requested Time : '.$history['requested_time'];
														echo '<br/>';
													}
												}
												?></th>
												<th><?php echo $user['approved'] == 1 ? 'Approved' : 'Disapproved'; ?></th>
											</tr>
									  <?php } } }
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="5">No Users Founds </td></tr>';
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

									$users = $app_data->users;
									  $cursor = $users->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $user)
										  {
											    if(in_array($user['company_id'],$com))
										  {
											 if($user['user_id'] != 1) {
										  ?>
											 <tr>
												<?php /*<th><?php echo $user['user_id']; ?></th>*/?>
												<th><?php echo $user['username']; ?>
												<br/> ( <?php echo $user['full_name']; ?>)
												</th>
												<th><?php $collection = new MongoCollection($app_data, 'company');
													 $companys = $collection->find(array('company_ID'=>(int) $user['company_id']));
													 foreach($companys as $company)
													 {
														echo $company['company_name'];
													 }
												?></th>
												<th><?php
												$key_id = json_decode($user['key_id']);
												$collection = new MongoCollection($app_data, 'keys');
												for($i=0;$i<=count($key_id);$i++)
												{
													 $companys = $collection->find(array('key_ID'=>(int) $key_id[$i]));
													 foreach($companys as $company)
													 {
														echo $company['key_name'] . '<br/>';
													 }
												}
												?></th>
												<th><?php echo isset($user['last_login']) ? $user['last_login'] : ''; ?></th>
												<th><?php
												$user_id = $user['user_id'];
												$collection_histry = new MongoCollection($app_data, 'history_log');
												$cursor = $collection_histry->find(array('user_id'=>$user_id));
												$j=0;
												foreach($cursor as $history)
												{ $j++;
													$his_locks = $app_data->locks;
													$cursor_locks = $his_locks->find(array('lock_ID'=>$history['lock_id']));
													foreach($cursor_locks as $lokcs)
													{
														echo $j .'. Lock : '.$lokcs['lock_name'] . ' ==> Access Code : ' . $history['access_code'];
														echo ' -- Requested Time : '.$history['requested_time'];
														echo '<br/>';
													}
												}
												?></th>
												<th><?php echo $user['approved'] == 1 ? 'Approved' : 'Disapproved'; ?></th>
											</tr>
										<?php } } } } } ?>
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
