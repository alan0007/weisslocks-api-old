<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
if($_SESSION['role'] != 1)
{
	header('Location:index.php');
}
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$selected_users = isset($_REQUEST['selected_users']) ? $_REQUEST['selected_users'] : array();
	$allow_saturday_sunday = isset($_REQUEST['allow_saturday_sunday']) ? 'Yes' : 'No';
	$super_admin_allow_saturday_sunday = isset($_REQUEST['super_admin_allow_saturday_sunday']) ? 'No' : 'Yes';
	$settings = $app_data->settings;	
	$settings->update( array('setting_id'=>(int) $_REQUEST['setting_id']),
	  array('$set' => array(
				'send_notification' => $_REQUEST['send_noti'],
				'users'  => json_encode($selected_users),
				'allow_saturday_sunday' => $allow_saturday_sunday,
				'super_admin_allow_saturday_sunday' => $super_admin_allow_saturday_sunday
			)
		)
	);
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
                    <h1 class="page-header">Settings</h1>
                </div>
                <div class="col-lg-12">
						<div class="panel-body">
                            <div class="table-responsive">
							
							<script>
										$(document).ready(function() {
										   $('input[type="radio"]').click(function() {
											   if($(this).attr('id') == 'custom') {
													$('.admins').show();           
											   }
											   else {
													$('.user_custom').attr('checked', false);
													$('.admins').hide();
											   }
										   });
										});
									</script>
								<form method="POST">
								<?php
										$users = $app_data->settings;
										$cursor = $users->findOne(array('setting_id'=>(int)$_REQUEST['setting_id']));
										if(!empty($cursor['setting_id']))
										{
											$com = $app_data->company;
											$com_details = $com->findOne(array('company_ID'=>(int)$cursor['company_id']));
											$company_user =  array_map('intval',json_decode($com_details['user_id']));
											$send_notification = $cursor['send_notification'];
											$allow_saturday_sunday = $cursor['allow_saturday_sunday'];
											$super_admin_allow_saturday_sunday = $cursor['super_admin_allow_saturday_sunday'];
								?>
								<input type="hidden" name="setting_id" value="<?php echo $cursor['setting_id']; ?>" name="setting_id" />
								
								<input type="checkbox" value="No" <?php echo $super_admin_allow_saturday_sunday == 'No' ? 'checked' : ''; ?> name="super_admin_allow_saturday_sunday" />
								<strong>Disallows</strong> To Get Access Code for Saturday and Sunday For this Company
								<br/><br/>
								
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
									<tr>
										<td>Send notification to:</td>
										<td>
											<input <?php echo $send_notification == 'all' ? 'checked' : ''; ?> id="all" type="radio"name="send_noti" value="all" />
											All
											<input <?php echo $send_notification == 'Custom' ? 'checked' : ''; ?> id="custom" type="radio"name="send_noti" value="Custom" />
											Selected Admin
											<br/><br/>
											Users List:
											<br/>
											<div class="admins" <?php echo $send_notification == 'Custom' ? '' : 'style="display:none;"'; ?>>
												<?php
												$users1 = $app_data->users;
												$rangeQuery = array('role' => array( '$gte' => '1', '$lte' => '3' ));
												$rangeQuery['user_id'] = array('$in'=> $company_user);	
												$cursor1 = $users1->find( $rangeQuery );
												if($cursor1->count() > 0)
												{
													foreach($cursor1 as $users11)
													{
															$selected= '';
															$saved_users = json_decode($cursor['users']);
															 if( in_array( $users11['user_id'] , $saved_users))
															 {
																 $selected= 'checked';
															 }
													?>
													<input class="user_custom" <?php echo $selected; ?> type="checkbox" name="selected_users[]" value="<?php echo $users11['user_id']; ?>" />
														<?php echo $users11['username']; ?><br/>
														
													<?php } } ?>
											</div>
										</td>
									</tr>
									<tr>
										<td>Allow Staurday and Sunday <br/> to Get Access code :</td>
										<td>
											<input type="checkbox" <?php echo $allow_saturday_sunday == 'Yes'  ? 'checked="checked"' : '';?> name="allow_saturday_sunday" value="Yes" />
										</td>
									</tr>
									<tr>
									<td colspan="2">
									<center>
									<input class="btn btn-primary" value="Save" name="process" type="submit">
									</center>
									</td>
									</tr>
                                </table>
								
								
								<?php } ?>
								</form>
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
