<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'keygroup' && $_REQUEST['key_group_ID'] != '')
{
	$collection = new MongoCollection($app_data, 'keygroup');
	$collection->remove( array( 'key_group_ID' =>(int) $_REQUEST['key_group_ID'] ) );
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
                    <h1 class="page-header">Key Group</h1>
                </div>
                <div class="col-lg-12">
                    <div style="width:200px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="manage_keygroup.php"> Add Key Group </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Key Group Added or updated Sucessfully!!</div>
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
											<th>Key Group ID</th>
										<?php } ?>	
                                            <th>Key Group Name </th>
                                            <th>User</th>
                                            <th>Company </th>
                                            <th>Key</th>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									  if($_SESSION['role'] == 1) 
										{
											$kg = $app_data->keygroup;
											$cursor = $kg->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $keygroup)
										  { ?>
											 <tr>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $keygroup['key_group_ID']; ?></th>
											 <?php } ?>
												<th><?php echo $keygroup['key_group_name']; ?></th>
												<th><?php 
													$collection = new MongoCollection($app_data, 'users');
													 $check = $collection->find(array('user_id'=>(int) $keygroup['key_grp_user_id']));
													 if($check->count() > 0) {
														 foreach($check as $users)
														 {
															echo $users['username'];
														 } }
												?></th>
												<th><?php
													$collection = new MongoCollection($app_data, 'company');
													 $companys = $collection->find(array('company_ID'=>(int) $keygroup['company_id']));
													 if($companys->count() > 0) {
													 foreach($companys as $company)
													 {
														echo $company['company_name'];
													 } }
												?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'keys');
												//$key_keygroup =  json_decode($keygroup['key_id']); //Old Format
												$key_keygroup =  $keygroup['key_id'];
												if(is_array($key_keygroup))
												{
													for($i=0;$i<count($key_keygroup);$i++)
													{
														$keys = $collection->find(array('key_ID'=>(int) $key_keygroup[$i]));
														foreach($keys as $key)
														 {
															echo $key['key_name'] . '<br/>';
														 }
													}													
												}
												?></th>
												<th>
												<a href="manage_keygroup.php?key_group_ID=<?php echo $keygroup['key_group_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="keygroup.php?delete=keygroup&key_group_ID=<?php echo $keygroup['key_group_ID']; ?>">  Delete </a>
												</th>
											</tr>
											  <?php } }
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="6">No Key Group Founds </td></tr>';
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
	
	
	
		$kg = $app_data->keygroup;
											$cursor = $kg->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $keygroup)
										  { 
										  
										  if(in_array($keygroup['company_id'],$com))
										  {
											  
										  
										  
										  ?>
											 <tr>
												<?php /*<th><?php echo $keygroup['key_group_ID']; ?></th> */?>
												<th><?php echo $keygroup['key_group_name']; ?></th>
												<th><?php 
													$collection = new MongoCollection($app_data, 'users');
													 $check = $collection->find(array('user_id'=>(int) $keygroup['key_grp_user_id']));
													 if($check->count() > 0) {
														 foreach($check as $users)
														 {
															echo $users['username'];
														 } }
												?></th>
												<th><?php
													$collection = new MongoCollection($app_data, 'company');
													 $companys = $collection->find(array('company_ID'=>(int) $keygroup['company_id']));
													 if($companys->count() > 0) {
													 foreach($companys as $company)
													 {
														echo $company['company_name'];
													 } }
												?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'keys');
												$kesy_keygroup =  json_decode($keygroup['key_id']);
												if(is_array($kesy_keygroup))
												{
													for($i=0;$i<count($kesy_keygroup);$i++)
													{
														$locks = $collection->find(array('key_ID'=>(int) $kesy_keygroup[$i]));
														foreach($locks as $lock)
														 {
															echo $lock['key_name'] . '<br/>';
														 }
													}													
												}
												?></th>
												<th>
												<a href="manage_keygroup.php?key_group_ID=<?php echo $keygroup['key_group_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="keygroup.php?delete=keygroup&key_group_ID=<?php echo $keygroup['key_group_ID']; ?>">  Delete </a>
												</th>
											</tr>
											  <?php } }
	
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
