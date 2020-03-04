<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$msg = '';
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$collection = new MongoCollection($app_data, 'users');
	$criteria = array('user_id'=>(int) $_SESSION['user_id']);
	$collection->update( $criteria ,array('$set' => array(
			'phone_number'  => $_REQUEST['phone_number'],
			'full_name' => $_REQUEST['full_name'],
	 )));
	if(isset($_REQUEST['password']) && $_REQUEST['password'] != '')
	{
		$collection->update( $criteria ,array('$set' => array(
			'password'  => md5($_REQUEST['password']),
		)));
	}
	$msg = 'Profile Updated Successfully';
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
                    <h1 class="page-header">Profile</h1>
                </div>
				<?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                <?php } ?>
                <div class="col-lg-6">
                     <div class="panel-body">
						<form role="form" action="" method="post">
                            <div class="table-responsive">
							<?php
										$users = $app_data->users;
										$cursor = $users->find(array('user_id' =>(int) $_SESSION['user_id']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $user)
											{
												$full_name = $user['full_name'];
												$username = $user['username'];
												$email = $user['email'];
												$phone_number = $user['phone_number'];
											}
										}
							?>
								<div class="form-group">
									<label>Username : </label>
									<span> <?php echo $username; ?></span>
								</div>
								<div class="form-group">
									<label>Email : </label>
									<span> <?php echo $email; ?></span>
								</div>
								<div class="form-group">
									<label> Full Name : </label>
									<input type="text" class="form-control" name="full_name" value="<?php echo $full_name; ?>" />
								</div>
								<div class="form-group">
									<label> Phone Number : </label>
									<input type="text" class="form-control" name="phone_number" value="<?php echo $phone_number; ?>" />
								</div>
								<h1 class="page-header">Change Password</h1>
								<div class="form-group">
									<label> Password : </label>
									<input type="password" class="form-control" name="password" />
								</div>
								 <input type="submit" class="btn btn-default" value="Save" name="process">
                            </div>
							</form>
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
