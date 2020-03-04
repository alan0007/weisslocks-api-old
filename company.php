<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();

if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'company' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'company');
	$collection->remove( array( 'company_ID' =>(int) $_REQUEST['id'] ) );
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
                    <h1 class="page-header">Company</h1>
                </div>
                <div class="col-lg-12">
                    <?php echo $_SESSION['role'] == 1 ? '<div style="width:180px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="managecompany.php"> Add Company </a></div>' : ''; ?>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Company Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<?php  if($_SESSION['role'] == 1){ ?>
												<th>Company ID</th>
											<?php } ?>
											<th>Company Ref ID</th>
                                            <th>Company Name</th>
                                            <th>Company Address</th>
                                            <th>Company Contact</th>
											<?php  if($_SESSION['role'] == 1){ ?>
												<th>User</th>
												<th>Action</th>
											<?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									  if($_SESSION['role'] == 1)
										{
											$user_companies = $app_data->company;
											$cursor = $user_companies->find();
										
											if($cursor->count() > 0)
											{
												foreach($cursor as $user_company)
												{ ?>
													<tr>
														<th><?php echo $user_company['company_ID']; ?></th>
														<th><?php echo $user_company['company_ref']; ?></th>
														<th><?php echo $user_company['company_name']; ?></th>
														<th><?php echo $user_company['company_address']; ?></th>
														<th><?php echo $user_company['company_contact']; ?></th>
														<th><?php
														/* //Currently bot showing user list for some company
														$company_users = json_decode($user_company['user_id']);
														$collection = new MongoCollection($app_data, 'users');
														if ($company_users > 0 && $company_users != null){
															for($i=0;$i<count($company_users);$i++)
															{
																$check = $collection->find(array('user_id'=>(int) $company_users[$i]));
																foreach($check as $users)
																{
																	echo $i+1 . '. ' . $users['username'] . '<br/>';
																}
															}
														}*/
														?></th>
														<th>
															<a href="managecompany.php?company_id=<?php echo $user_company['company_ID']; ?>">  Edit </a>
															<a onclick="return confirm('Are you sure?')" href="company.php?delete=company&id=<?php echo $user_company['company_ID']; ?>">  Delete </a>
														</th>
													</tr>
												<?php } 
											}
											else
											{
											echo '<tr class="odd gradeX"><td colspan="5">No Company Founds </td></tr>';
											}
										}
										else
										{
											$user_companies = $app_data->company;
											$cursor = $user_companies->find();
											 if($cursor->count() > 0)
											  {
												  foreach($cursor as $user_company)
												  {
													$company_users = json_decode($user_company['user_id']);
													if(in_array($_SESSION['user_id'],$company_users))
													{ 
												  ?>
													 <tr>
														<th><?php echo $user_company['company_ref']; ?></th>
														<th><?php echo $user_company['company_name']; ?></th>
														<th><?php echo $user_company['company_address']; ?></th>
														<th><?php echo $user_company['company_contact']; ?></th>
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
