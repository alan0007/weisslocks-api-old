<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if($_SESSION['role'] == 3)
{
	header("Location:index.php");
}
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$selected_users = isset($_REQUEST['selected_users']) ? $_REQUEST['selected_users'] : array();
	$allow_saturday_sunday = isset($_REQUEST['allow_saturday_sunday']) ? 'Yes' : 'No';
	if($current_user == 1)
	{
		$ids = 1;
		$criteria = array('setting_id'=>(int) $ids);
		$collection = new MongoCollection($app_data, 'settings');
		$collection->update( $criteria ,array('$set' => array(
					'send_notification' => $_REQUEST['send_noti'],
					'users'  => json_encode($selected_users),
					'allow_saturday_sunday' => $allow_saturday_sunday
		)));
	}
	else
	{
		
	}
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
								echo '<pre>';
								$company_id = 0;
								$users = $app_data->company;
								$cursor = $users->find();
								if($cursor->count() > 0)
								{
									foreach($cursor as $com)
									{
										$users_ids = json_decode($com['user_id']);
										if(in_array($current_user,$users_ids))
										{
											$company_id = $com['company_ID'];
										}
									}
								}
								
								
								$users = $app_data->settings;
								$cursor_1 = $users->find(array('company_id'=>(int)$company_id));
								$settings_id = 0;
								if($cursor_1->count() > 0)
								{
									foreach($cursor_1 as $sett)
									{
										$send_notification = $added_sett['send_notification'];
										$settings_users = $added_sett['users'];
									}
								}
								else 
								{
									$settings = $app_data->settings;
									$post = array(
										'setting_id' => getNext_users_Sequence('settings'),
										'send_notification' => $_REQUEST['send_noti'],
										'users'  => json_encode(array()),
										'company_id'  => (int)$company_id,
										
										);
										echo 'Add';
									 $settings->insert($post);
									 $Query = array('_id' => $post['_id'] ) ;
									 $cursor = $settings->find( $Query ); 
									 if($cursor->count() > 0)
									 {
										 foreach($cursor as $added_sett) { $settings_id = $added_sett['settings_id']; 
										 
										 $send_notification = $added_sett['send_notification'];
										$settings_users = $added_sett['users'];
										 
										 }
									 }
									
								}
									echo '</pre>';
									
										$users = $app_data->settings;
										$cursor = $users->find(array('setting_id'=>(int)$settings_id));
										foreach($cursor as $settings)
										{
												$send_notification = $settings['send_notification'];
												$settings_users = $settings['users'];
										}
								?>
								<input type="hidden" name="company_id" value="<?php echo $settings_id ?>" />
								
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
												$users = $app_data->users;
												$rangeQuery = array('role' => array( '$gte' => '1', '$lte' => '3' ));
												$cursor = $users->find( $rangeQuery );
												
												if($cursor->count() > 0)
												{
													foreach($cursor as $users)
													{ 
														$selected= '';
														$saved_users = json_decode($settings_users);
														if( in_array( $users['user_id'] , $saved_users))
														{
															$selected= 'checked';
														}
													?>
														<input class="user_custom" <?php echo $selected; ?> type="checkbox" name="selected_users[]" value="<?php echo $users['user_id']; ?>" />
														<?php echo $users['username']; ?><br/>
													<?php } } ?>
											</div>
										</td>
									</tr>
									<?php if($_SESSION['role'] == 1) { ?>
									<tr>
										<td>Allow Staurday and Sunday <br/> to Get Access code :</td>
										<td>
											<input type="checkbox" <?php echo $allow_saturday_sunday == 'Yes'  ? 'checked="checked"' : '';?> name="allow_saturday_sunday" value="Yes" />
										</td>
									</tr>
									<?php } ?>
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
