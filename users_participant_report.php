<script>
document.addEventListener('contextmenu', event => event.preventDefault());
</script>
<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'users' && $_REQUEST['id'] != '')
{
	if($_REQUEST['id'] != 1)
	{
		$collection = new MongoCollection($app_data, 'users');
		$collection->remove( array( 'user_id' =>(int) $_REQUEST['id'] ) );
		$msg = "Deleted Sucessfully!!";
	}
}
if(isset($_REQUEST['user']) && $_REQUEST['user'] == 'approve' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'users');
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
                    <h1 class="page-header">Participant List</h1>
                </div>
                <div class="col-lg-12">
				<div style="width:180px;clear:both;padding-left:30px;">
					<!-- <a class="btn btn-lg btn-primary btn-block" href="manageusers.php"> Add User </a> -->
				</div> 
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Users Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
							<?php
								//Temporary exclusion: 265,279,361
								$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364,265,279,361);
								//$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364);
								
								$user_participant_list = array();
								$user_participant_count = 0 ;
								
								$user_participant = $app_data->users;
								$cursor_participant = $user_participant->find();
								if($cursor_participant->count() > 0)
								{
									foreach($cursor_participant as $user)
									{
										$user_id = $user['user_id'];
										$participant = $user['participant'];
										if ( !in_array($user_id, $user_exclude_list) )
										{
											if ($participant == 1){
												if (!in_array($user_id, $user_participant_list)){
													array_push($user_participant_list,$user_id);
												}
											}
										}	
									}
									$user_participant_count = sizeof($user_participant_list);									
								}
									  
							
							?>
								Total Participant = <?php echo $user_participant_count;?>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
										<?php if($_SESSION['role'] == 1) { ?>
											<th>No.</th>
											<th>User ID</th>
										<?php }
											else{ ?>
											<th>No.</th>	
										<?php	}	?>
											<th>User Name</th>
											<th>Email</th>
											<th>role</th>
											<th>Company</th>
											<th>Company Ref ID</th>
											<th>keys</th>
											<th>Key Activated</th>
											<th>Status</th>
											<?php echo in_array($_SESSION['role'],array(1,3)) ? '<th>Approve Action</th>' : ''; ?>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										if($_SESSION['role'] == 1)
										{
											$number=1;
											$users = $app_data->users;
											$cursor = $users->find();											
											//$cursor = $users->find();
										
										// else
										// {
											// $collection = new MongoCollection($app_data, 'users');
											// $Query = array('user_id' => $_SESSION['user_id'] );
											// $cursor = $collection->find( $Query );
											// foreach($cursor as $uu)
											// {
												// $company_id_user = $uu['company_id'];
											// }
											// $users = $app_data->users;
											// $cursor = $users->find(array('company_id'=>$company_id_user));
										// }
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $user)
										  {
											  $user_id = $user['user_id'];
											  if ( in_array($user_id, $user_participant_list) ){
												  
										  ?>
											 <tr>
												<th><?php echo $number; ?></th>
											 <?php if($_SESSION['role'] == 1) { ?>
												<th><?php echo $user['user_id']; ?></th>
											 <?php } ?>
												<th><?php echo $user['username']; ?></th>
												<th><?php echo $user['email']; ?></th>
												<th><?php
													echo $user['role'] == 1 ? 'Super Admin' : ( $user['role'] == 2 ? 'Owner - Admin Company' : ( $user['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $user['role'] == 4 ? 'Contractor' : ( $user['role'] == 5 ? 'Staff' : ( $user['role'] == 6 ? 'Subadmin' : ( $user['role'] == 7 ? 'Visitor' : '' ) ) ) ) ) );
												?></th>
												<th><?php
												$collection = new MongoCollection($app_data, 'company');
												$check = $collection->find(array('company_ID'=>(int) $user['company_id']));
												foreach($check as $companys)
												{
													echo $companys['company_name'];
													$company_ref = $companys['company_ref'];
												}
												?></th>
												<th><?php echo $company_ref; // $user['company_ref_id']; ?></th>
												<th><?php 
												$keys = json_decode($user['key_id']);
												$collection = new MongoCollection($app_data, 'keys');
												for($i=0;$i<=count($keys);$i++)
												{
													$check = $collection->find(array('key_ID'=>(int) $keys[$i]));
													foreach($check as $key)
													{
														$key_status = $key['status'] == 1 ? 'Activated' : ' Deactivate ';
														echo $i+1 . '. ' . $key['key_name'] . ' - ' . $key_status;
														echo '<br/>';
													}
												}
												?></th>
												<th><?php 
												$keys = json_decode($user['key_activated']);
												$collection = new MongoCollection($app_data, 'keys');
												for($i=0;$i<=count($keys);$i++)
												{
													$check = $collection->find(array('key_ID'=>(int) $keys[$i]));
													foreach($check as $key)
													{
														echo $i+1 . '. ' . $key['key_name'];
														echo '<br/>';
													}
												}
												?></th>
												<th><?php  echo $user['approved'] == 2 ? 'Disapproved' : ($user['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
												<?php if(in_array($_SESSION['role'],array(1,3))){  ?>
													<th>
														<a href="users.php?user=approve&id=<?php echo $user['user_id']; ?>"> 
														<?php if($user['approved'] == 2) { ?>
														Approve  
														<?php } // else {echo '-'; } ?>
														</a>
													</th>
												<?php }  ?>
													<th>
														<a href="showusers.php?user_ID=<?php echo $user['user_id']; ?>">  Show </a>
													<?php if($_SESSION['role'] == 1) { ?>
														<a href="manageusers.php?user_ID=<?php echo $user['user_id']; ?>">  Edit </a>
														<?php if($user['user_id'] != 1){ ?>
															<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $user['user_id']; ?>">  Delete </a>
													<?php } } ?>
													</th>
											</tr>
									  <?php
												$number++;
											  }									  
										} }
									  else
										{
											 echo '<tr class="odd gradeX"><td colspan="5">No Keys Founds </td></tr>';
										}
									  } 
									  
									  //For Owner, Admin, Sub Admin only
									  else { 
											$users_lst = array();
											$users = $app_data->company;
											$cursor = $users->find();
											if($cursor->count() > 0)
											{
												foreach($cursor as $com)
												{
													 $ids = json_decode($com['user_id']);
													 if(in_array( $current_user , $ids))
													 {
														 for($i=0;$i<=count($ids);$i++)
														{
															$users_lst[] = $ids[$i];
														}
													 }
												}
											}
											
											$number = 1;
											
									  for($j=0;$j<=count($users_lst);$j++)
										{
											if($users_lst != '')
											{
												$users = $app_data->users;
												$cursor = $users->find(array('user_id'=>(int)$users_lst[$j]));
												if($cursor->count() > 0)
												{
													foreach($cursor as $user) 
													{ 
														$user_id = $user['user_id'];
														if ( in_array($user_id, $user_participant_list) )
														{
													?>
															<tr>
														 <?php if($_SESSION['role'] == 1) { ?>
															<th><?php echo $user['user_id']; ?></th>
															<?php }
																else{ ?>
																<th><?php echo $number; ?></th>	
															<?php	}	?>
															<th><?php echo $user['username']; ?></th>
															<th><?php echo $user['email']; ?></th>
															<th><?php
																// echo $user['role'] == 1 ? 'Super Admin' : ( $user['role'] == 2 ? 'Owner - Admin Company' : ( $user['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $user['role'] == 4 ? 'User Company(Company)' : ( $user['role'] == 5 ? 'Key Holder -User (User of Company)' : '' ) ) ) );
																echo $user['role'] == 1 ? 'Super Admin' : ( $user['role'] == 2 ? 'Owner - Admin Company' : ( $user['role'] == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $user['role'] == 4 ? 'Contractor' : ( $user['role'] == 5 ? 'Staff' : '' ) ) ) );
															?></th>
															<th><?php
															$collection = new MongoCollection($app_data, 'company');
															$check = $collection->find(array('company_ID'=>(int) $user['company_id']));
															foreach($check as $companys)
															{
																echo $companys['company_name'];
															}
															?></th>
															<th><?php echo $user['company_ref_id']; ?></th>
															<th><?php 
															$keys = json_decode($user['key_id']);
															$collection = new MongoCollection($app_data, 'keys');
															for($i=0;$i<=count($keys);$i++)
															{
																$check = $collection->find(array('key_ID'=>(int) $keys[$i]));
																foreach($check as $key)
																{
																	$key_status = $key['status'] == 1 ? 'Activated' : ' Deactivate ';
																	echo $i+1 . '. ' . $key['key_name'] . ' - ' . $key_status;
																	echo '<br/>';
																}
															}
															?></th>
															<th><?php 
															$keys = json_decode($user['key_activated']);
															$collection = new MongoCollection($app_data, 'keys');
															for($i=0;$i<=count($keys);$i++)
															{
																$check = $collection->find(array('key_ID'=>(int) $keys[$i]));
																foreach($check as $key)
																{
																	echo $i+1 . '. ' . $key['key_name'];
																	echo '<br/>';
																}
															}
															?></th>
															<th><?php  echo $user['approved'] == 2 ? 'Disapproved' : ($user['approved'] == 1 ? 'Approved' : 'Pending'); ?></th>
															<?php if(in_array($_SESSION['role'],array(1,3))){  ?>
																<th>
																	<a href="users.php?user=approve&id=<?php echo $user['user_id']; ?>"> 
																	<?php if($user['approved'] == 2) { ?>
																	Approve  
																	<?php } // else {echo '-'; } ?>
																	</a>
																</th>
															<?php }  ?>
																<th>
																	<a href="showusers.php?user_ID=<?php echo $user['user_id']; ?>">  Show </a>
																	<a href="manageusers.php?user_ID=<?php echo $user['user_id']; ?>">  Edit </a>
																	<?php if($user['user_id'] != $current_user){ ?>
																		<a onclick="return confirm('Are you sure?')" href="users.php?delete=users&id=<?php echo $user['user_id']; ?>">  Delete </a>
																<?php } ?>
																</th>
															</tr>
														<?php  
															$number++;
														} 
													} 
												} 
											} 
										} 
										} ?>
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
