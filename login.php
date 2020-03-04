<?php
include(dirname(__FILE__).'/configurations/config.php');
$msg = 0;
if(isset($_REQUEST['users_logs']) && $_REQUEST['users_logs'] == 'Log In')
{
	$collection = new MongoCollection($app_data, 'users');
	$Login_Query = array('username' => $_POST['uname'], 'password' => md5($_POST['psw']),'approved'=>1,'role' => array( '$gte' => 1, '$lte' => 3 ) );
	$cursor = $collection->find( $Login_Query );
	if($cursor->count() == 1)
	{
		foreach ( $cursor as $lg_details) 
		{
			$_SESSION['user_id'] = $lg_details['user_id'];
			$_SESSION['username'] = $lg_details['username'];
			$_SESSION['role'] = $lg_details['role'];
			$_SESSION['company_id'] = $lg_details['company_id'];
			// Update Last Ligin Details
			$collection = new MongoCollection($app_data, 'users');
			$criteria = array('user_id'=>(int) $lg_details['user_id'] );
			$collection->update( $criteria ,array('$set' => array(
				'last_login'  => date('H:i A,d F Y'),
			)));
		}
		header('Location:index.php');
	}
	else
	{
		$msg = 1;
	}
}

include("header.php");
?>
<title> Weiss Locks Login</title>
<body class="login">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
             <h1> <a class="navbar-brand" href="index.php"><img src="images/weisslocks-logo.png" width="121" title="Weiss Locks" alt="Weiss Locks"></a></h1>
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign In</h3>
                    </div>
                    <div style="color:red; text-align: center; width: 100%">
                        <?php
						if($msg == 1)
						{
							echo '<span style="color:red;">  Invalid Credentials  </span>';
						}
			?>
                    </div>
                    <div class="panel-body" >
                        <form method="POST" autocomplete="off">
                            <fieldset>
                                <div class="form-group">
									<input class="form-control" type="text" placeholder="Enter Username" name="uname" autocomplete="off" autofocus/>
                                </div>
                                <div class="form-group">
									<input class="form-control" type="password" placeholder="Enter Password" name="psw" autocomplete="off"/>
                                </div>
                                <input type="submit" class="btn btn-lg btn-primary btn-block" name="users_logs" Value="Log In"/>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<?php include("footer.php");?>