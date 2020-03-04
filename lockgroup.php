<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'company' && $_REQUEST['lock_group_ID'] != '')
{
	$collection = new MongoCollection($app_data, 'lockgroup');
	$collection->remove( array( 'lock_group_ID' =>(int) $_REQUEST['lock_group_ID'] ) );
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
                    <h1 class="page-header">Lock Group</h1>
                </div>
                <div class="col-lg-12">
                    <div style="width:200px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="manage_lockgroup.php"> Add LockGroup </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Lock Group Added or updated Sucessfully!!</div>
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
											<th>Lock Group ID</th>
										<?php } ?>
                                            <th>Locks Group Name </th>
                                            <th>User</th>
                                            <th>Company </th>
                                            <th>Lock</th>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									   if($_SESSION['role'] == 1) 
										{
											$lg = $app_data->lockgroup;
											$cursor = $lg->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $lockgroup)
										  { ?>
											 <tr>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $lockgroup['lock_group_ID']; ?></th>
											 <?php } ?>
												<th><?php echo $lockgroup['lock_group_name']; ?></th>
												<th><?php 
													$collection = new MongoCollection($app_data, 'users');
													 $check = $collection->find(array('user_id'=>(int) $lockgroup['lock_grp_user_id']));
													 foreach($check as $users)
													 {
														echo $users['username'];
													 }
												?></th>
												<th><?php
													$collection = new MongoCollection($app_data, 'company');
													 $companys = $collection->find(array('company_ID'=>(int) $lockgroup['company_id']));
													 foreach($companys as $company)
													 {
														echo $company['company_name'];
													 }
												?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'locks');
												$locks_lockgroup =  json_decode($lockgroup['lock_id']);
												if(is_array($locks_lockgroup))
												{
													for($i=0;$i<count($locks_lockgroup);$i++)
													{
														$locks = $collection->find(array('lock_ID'=>(int) $locks_lockgroup[$i]));
														foreach($locks as $lock)
														 {
															echo $lock['lock_name'] . '<br/>';
														 }
													}													
												}
												?></th>
												<th>
												<a href="manage_lockgroup.php?lock_group_ID=<?php echo $lockgroup['lock_group_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="lockgroup.php?delete=company&lock_group_ID=<?php echo $lockgroup['lock_group_ID']; ?>">  Delete </a>
												</th>
											</tr>
											  <?php } }
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="6">No Lock Group Founds </td></tr>';
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
	
											$lg = $app_data->lockgroup;
											$cursor = $lg->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $lockgroup)
										  {
										if(in_array($lockgroup['company_id'],$com))
										  {
										  ?>
											 <tr>
												<?php /*<th><?php echo $lockgroup['lock_group_ID']; ?></th>*/?>
												<th><?php echo $lockgroup['lock_group_name']; ?></th>
												<th><?php 
													$collection = new MongoCollection($app_data, 'users');
													 $check = $collection->find(array('user_id'=>(int) $lockgroup['lock_grp_user_id']));
													 foreach($check as $users)
													 {
														echo $users['username'];
													 }
												?></th>
												<th><?php
													$collection = new MongoCollection($app_data, 'company');
													 $companys = $collection->find(array('company_ID'=>(int) $lockgroup['company_id']));
													 foreach($companys as $company)
													 {
														echo $company['company_name'];
													 }
												?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'locks');
												$locks_lockgroup =  json_decode($lockgroup['lock_id']);
												if(is_array($locks_lockgroup))
												{
													for($i=0;$i<count($locks_lockgroup);$i++)
													{
														$locks = $collection->find(array('lock_ID'=>(int) $locks_lockgroup[$i]));
														foreach($locks as $lock)
														 {
															echo $lock['lock_name'] . '<br/>';
														 }
													}													
												}
												?></th>
												<th>
												<a href="manage_lockgroup.php?lock_group_ID=<?php echo $lockgroup['lock_group_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="lockgroup.php?delete=company&lock_group_ID=<?php echo $lockgroup['lock_group_ID']; ?>">  Delete </a>
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
