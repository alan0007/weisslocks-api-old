<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'lock' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'locks');
	$collection->remove( array( 'lock_ID' =>(int) $_REQUEST['id'] ) );
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
                    <h1 class="page-header">Locks</h1>
                </div>
                <div class="col-lg-12">
                    <div style="width:150px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="managelocks.php"> Add Locks </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Locks Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
											<th>Company</th>
                                            <th>Lock Name</th>
                                            <th>Display Name</th>
                                            <th>Type</th>
                                            <th>Model</th>
                                            <th>Mechanism</th>
                                            <th>Visibility</th>
                                            <th>Brand</th>
                                            <th>Serial Number</th>
                                            <th>Log Number</th>
                                            <th>Site ID</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									  if($_SESSION['role'] == 1) 
										{
									  
										  $locks = $app_data->locks;
										  $cursor = $locks->find();
										  if($cursor->count() > 0)
										  {
										  foreach($cursor as $lock)
										  { ?>
											 <tr>
                                                 <th><?php echo $lock['lock_ID']; ?></th>
                                                 <th><?php
                                                $collection = new MongoCollection($app_data, 'company');
                                                $check = $collection->find(array('company_ID'=>(int) $lock['company_id']));
                                                foreach($check as $users)
                                                {
                                                    echo $users['company_name'];
                                                }
                                                ?></th>
                                                 <th><?php echo $lock['lock_name']; ?></th>
                                                 <th><?php echo $lock['display_name']; ?></th>
                                                 <th><?php echo $lock['lock_type']; ?></th>
                                                 <th><?php echo $lock['lock_model']; ?></th>
                                                 <th><?php echo $lock['lock_mechanism']; ?></th>
                                                 <th><?php echo $lock['entrance_visibility']; ?></th>
                                                 <th><?php echo $lock['brand']; ?></th>
                                                 <th><?php echo $lock['serial_number']; ?></th>
                                                 <th><?php echo $lock['log_number']; ?></th>
                                                 <th><?php echo $lock['site_id']; ?></th>
                                                <th>
                                                <a href="managelocks.php?lock_ID=<?php echo $lock['lock_ID']; ?>">  Edit </a>
                                                <a onclick="return confirm('Are you sure?')" href="locks.php?delete=lock&id=<?php echo $lock['lock_ID']; ?>">  Delete </a>
                                                </th>
											</tr>
											  <?php } }
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="5">No Locks Founds </td></tr>';
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
											$locks = $app_data->locks;
										  $cursor = $locks->find();
										  if($cursor->count() > 0)
										  {
										  foreach($cursor as $lock)
										  { 
										  if(in_array($lock['company_id'],$com))
										  {
										  ?>
											 <tr>
												<th><?php
												$collection = new MongoCollection($app_data, 'company');
												$check = $collection->find(array('company_ID'=>(int) $lock['company_id']));
												foreach($check as $users)
												{
													echo $users['company_name'];
												}
												?></th>
												<th><?php echo $lock['lock_name']; ?></th>
												<th><?php echo $lock['serial_number']; ?></th>
												<th>
												<a href="managelocks.php?lock_ID=<?php echo $lock['lock_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="locks.php?delete=lock&id=<?php echo $lock['lock_ID']; ?>">  Delete </a>
												</th>
											</tr>
										<?php } } }
											  
											  
											
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
