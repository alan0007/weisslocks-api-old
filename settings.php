<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$allow_saturday_sunday = isset($_REQUEST['allow_saturday_sunday']) ? 'Yes' : 'No';
$user_id = $_SESSION['user_id'];
$settings = $app_data->settings;	
$settingData = array();
$company_id = 0;
$super_user_id = 0;
$users = $app_data->users->findOne(array('user_id'=>$user_id));
$company_user = array();
if(!empty($user_id)){
	$company = $app_data->company->find();
	if($company->count() > 0){
		foreach($company as $val){
			if(!empty($val['user_id'])){
				if(in_array($user_id, json_decode($val['user_id']))){
					$company_id = $val['company_ID'];
					$company_user =  array_map('intval',json_decode($val['user_id']));
				}	
			}	
		}							
	}
	if(!empty($users['role']) && $users['role'] == 2 ){
		if($company_id){
			$settingData = $settings->findOne(array('company_id'=> $company_id));
		}
	}else{
		if($company_id){
			$settingData = $settings->findOne(array('company_id'=> $company_id));
		}else{
			$settingData = $settings->findOne(array('super_user_id' => $user_id));
			$super_user_id = $user_id;
		}
	}
}

if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$selected_users = isset($_REQUEST['selected_users']) ? $_REQUEST['selected_users'] : array();
		if(!empty($settingData)){
			$settings->update( array('setting_id'=>(int) $settingData['setting_id']),
					  array('$set' => array(
								'send_notification' => $_REQUEST['send_noti'],
								'users'  => json_encode($selected_users),
								'company_id' => $company_id,
								'super_user_id' => $super_user_id,
								'allow_saturday_sunday' => $allow_saturday_sunday,
							)
						)
					);
		}else{
			$post = array(
					'setting_id' => getNext_users_Sequence('settings'),
					'send_notification' => $_REQUEST['send_noti'],
					'users'  => json_encode($selected_users),
					'company_id' => $company_id,
					'super_user_id' => $super_user_id,
					'allow_saturday_sunday' => $allow_saturday_sunday,
					'super_admin_allow_saturday_sunday' => 'Yes'
				);
			$settings->insert($post);
		}
}

$settings_users = array();
$send_notification = 'all';
if(!empty($settingData['setting_id'])){
	$settingData = $settings->findOne(array('setting_id'=>$settingData['setting_id']));
	$settings_users = $settingData['users'];
	$allow_saturday_sunday = !empty($settingData['allow_saturday_sunday'])? $settingData['allow_saturday_sunday'] : 'No';
	$send_notification = !empty($settingData['send_notification'])? $settingData['send_notification'] : 'all';
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
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Keys Added or updated Sucessfully!!</div>
                    <?php } ?>
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
										//$send_notification = 'all';
										//$settings_users = array();
										$users = $app_data->settings;
										$cursor = $users->find();
										
										foreach($cursor as $settings)
										{
											if($settings['setting_id'] == 1)
											{
												$send_notification = $settings['send_notification'];
												//$settings_users = $settings['users'];
											}
											if($settings['setting_id'] == 1)
											{
												$allow_saturday_sunday = $settings['allow_saturday_sunday'];
											}
										}
								?>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
								<?php if($_SESSION['role'] != 3) { ?>
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
												$users = $app_data->users;
												
												$rangeQuery = array('role' => array( '$gte' => '1', '$lte' => '3' ));
												if(!empty($company_user)){
													$rangeQuery['user_id'] = array('$in' => $company_user);	
												}
												//print_r($rangeQuery);
												//exit;
												$cursor = $users->find( $rangeQuery );
												
												if($cursor->count() > 0)
												{
													foreach($cursor as $users)
													{ 
														$selected= '';
														if(!empty($settings_users)){
															$saved_users = json_decode($settings_users);
															if( in_array( $users['user_id'] , $saved_users))
															{
																$selected= 'checked';
															}
														}
													?>
														<input class="user_custom" <?php echo $selected; ?> type="checkbox" name="selected_users[]" value="<?php echo $users['user_id']; ?>" />
														<?php echo $users['username']; ?><br/>
													<?php } } ?>
											</div>
										</td>
									</tr>
									<?php } ?>
									<?php //if($_SESSION['role'] == 1) { ?>
									<tr>
										<td>Allow Staurday and Sunday <br/> to Get Access code :</td>
										<td>
											<input type="checkbox" <?php echo $allow_saturday_sunday == 'Yes'  ? 'checked="checked"' : '';?> name="allow_saturday_sunday" value="Yes" />
										</td>
									</tr>
									<?php //} ?>
									<tr>
									<td colspan="2">
									<center>
									<input class="btn btn-primary" value="Save" name="process" type="submit">
									</center>
									</td>
									</tr>
                                </table>
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
