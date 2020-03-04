<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'keylockgrppair' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'KeyLockGroup');
	$collection->remove( array( 'keyLockGroup_ID' =>(int) $_REQUEST['id'] ) );
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
                    <h1 class="page-header"><!--Key Group and Lock Group Pairing--> Access Control </h1>
                </div>
                <div class="col-lg-12">
                    <div style="width:250px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="manage_key_and_lock_group_pairing.php"> Add <!--Key Group and Lock Group Pairing--> Access Control </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Access Control updated Sucessfully!!</div>
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
											<th>Pair ID</th>
										<?php } ?>
											<th>Pairing Name</th>
											<th>Lock Group</th>
											<th>Key Group </th>
											<th>Company</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									    if($_SESSION['role'] == 1) 
										{
										$KeyLockGroup = $app_data->KeyLockGroup;
											$cursor = $KeyLockGroup->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $KeyLockGroup)
										  {
										  ?>
											 <tr>
												<th><?php echo $KeyLockGroup['keyLockGroup_ID']; ?></th>
												<th><?php echo $KeyLockGroup['pairing_name']; ?></th>
												<th><?php $lockgroup = $app_data->lockgroup;
														  $cursor = $lockgroup->find(array('lock_group_ID'=>(int)$KeyLockGroup['lock_group_id']));
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $lockgroup)
															  { 
																echo $lockgroup['lock_group_name'];
															  }
														  }
												?></th>
												<th>
												<?php $keygroup = $app_data->keygroup;
														  $cursor = $keygroup->find(array('key_group_ID'=>(int)$KeyLockGroup['key_group_id']));
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keygroup)
															  { 
																echo $keygroup['key_group_name'];
															  }
														  }
												?>
												</th>
												<th>
												<?php $company = $app_data->company;
														  $cursor = $company->find(array('company_ID'=>(int)$KeyLockGroup['company_id']));
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $company)
															  { 
																echo $company['company_name'];
															  }
														  }
												?>
												</th>
												<th>
												<a href="manage_key_and_lock_group_pairing.php?keyLockGroup_ID=<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="key_and_lock_group_pairing.php?delete=keylockgrppair&id=<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>">  Delete </a>
												</th>
											</tr>
									  <?php } } 
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="5">No Key Group and Lock Group Pair Founds </td></tr>';
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
	
											$KeyLockGroup = $app_data->KeyLockGroup;
											$cursor = $KeyLockGroup->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $KeyLockGroup)
										  {
											  if(in_array($KeyLockGroup['company_id'],$com))
										  {
										  ?>
											 <tr>
											 <th><?php echo $KeyLockGroup['pairing_name']; ?></th>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $KeyLockGroup['keyLockGroup_ID']; ?></th>
											 <?php } ?>
												<th><?php $lockgroup = $app_data->lockgroup;
														  $cursor = $lockgroup->find(array('lock_group_ID'=>(int)$KeyLockGroup['lock_group_id']));
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $lockgroup)
															  { 
																echo $lockgroup['lock_group_name'];
															  }
														  }
												?></th>
												<th>
												<?php $keygroup = $app_data->keygroup;
														  $cursor = $keygroup->find(array('key_group_ID'=>(int)$KeyLockGroup['key_group_id']));
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keygroup)
															  { 
																echo $keygroup['key_group_name'];
															  }
														  }
												?>
												</th>
												<th>
												<?php $company = $app_data->company;
														  $cursor = $company->find(array('company_ID'=>(int)$KeyLockGroup['company_id']));
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $company)
															  { 
																echo $company['company_name'];
															  }
														  }
												?>
												</th>
												<th>
												<a href="manage_key_and_lock_group_pairing.php?keyLockGroup_ID=<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="key_and_lock_group_pairing.php?delete=keylockgrppair&id=<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>">  Delete </a>
												</th>
											</tr>
										<?php } }  }
											
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
