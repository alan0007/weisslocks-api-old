<?php
			// include dirname(__FILE__).'/gmail/phpmailer/PHPMailerAutoload.php';
			// $mail = new PHPMailer();
			// $mail->isSMTP();
			// $mail->Host = 'smtp.gmail.com';
			// $mail->Port = 587;
			// $mail->SMTPSecure = 'tls';
			// $mail->SMTPAuth = true;
			// $mail->Username = "sendweisslocks@gmail.com";
			// $mail->Password = "AppRegistration";
			// $mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
			// $mail->addAddress( 'archirayan5@gmail.com' );
			// $mail->Subject = 'Weiss Locks - Password Set Successfully';
			// $mail->msgHTML('
			// Dear Candidate,
			// <br/><br/>
			// You are Done with Set Password.
			// <br/><br/> 
			// <a href="http://app.weisslocks.com/login.php"> Click here to Login </a>
			// ');
			// $mail->send();
			
include(dirname(__FILE__).'/configurations/config.php');
$msg = 0;
$error_msg = 0;
$show = 1;
$collection = new MongoCollection($app_data, 'users');
if(isset($_REQUEST['users_pass']) && $_REQUEST['users_pass'] == 'Set Password')
{
	if($_REQUEST['pass'] != '' && $_REQUEST['repass'] != '' && $_REQUEST['pass'] == $_REQUEST['repass'] && $_REQUEST['token'] != '')
	{
		 $Login_Query = array('token' => $_REQUEST['token']);
		 $cursor = $collection->find( $Login_Query );
		 if($cursor->count() == 1)
		 {
			$status = 1;
			  foreach ( $cursor as $pws_details) 
			  {
				 $user_id = $pws_details['user_id'];
				 $email_login = $pws_details['email'];
				 
			  }
		 }
		 
			$criteria = array('user_id'=>(int) $user_id);
			$collection->update( $criteria ,array('$set' => array('password' => md5($_REQUEST['repass']),'token' => time().rand() ) ) );
			
			include dirname(__FILE__).'/gmail/phpmailer/PHPMailerAutoload.php';
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 587;
			$mail->SMTPSecure = 'tls';
			$mail->SMTPAuth = true;
			$mail->Username = "sendweisslocks@gmail.com";
			$mail->Password = "AppRegistration";
			$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
			$mail->addAddress( $email_login );
			$mail->Subject = 'Weiss Locks - Password Set Successfully';
			$mail->msgHTML('
			Dear Candidate,
			<br/><br/>
			You are Done with Set Password.
			<br/><br/> 
			<a href="http://app.weisslocks.com/login.php"> Click here to Login </a>
			');
			$mail->send();
			$msg = 1;
	}
	else
	{
		$msg = 2;
	}
}

include("header.php");
?>
<title> Weiss Locks Login</title>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
             <h1> <a class="navbar-brand" href="index.php">Weiss Locks</a></h1>
                <div class="login-panel panel panel-default">
                    
					<?php 
					$status = 0;
					if(isset($_REQUEST['token']) && $_REQUEST['token'] != '')
					{ ?>
					<div class="panel-heading">
                        <h3 class="panel-title">Set Password</h3>
                    </div>
                    <div style="color:red; text-align: center; width: 100%">
                        <?php
						if($msg == 1)
						{
							echo '<span style="color:green;"> Password Set Successfully <br/><br/>Click <a href="http://app.weisslocks.com/login.php">Here</a> to Login </span>';
							$show = 0;
						}
						else if($msg == 2)
						{
							echo '<span style="color:Red;"> Password Not Matched </span>';
						}
						?>
                    </div>
					<?php
					if($show == 1)
					{
					 $Login_Query = array('token' => $_REQUEST['token']);
					 $cursor = $collection->find( $Login_Query );
					 if($cursor->count() == 1)
					 {
						 $status = 1;
					 }
					if($status == 1 && isset($_REQUEST))
					{
					?>
                    <div class="panel-body">
                        <form method="POST">
                            <fieldset>
                                <div class="form-group">
										<input class="form-control" type="text" placeholder="Enter Password" name="pass" autofocus/>
                                </div>
                                <div class="form-group">
										<input class="form-control" type="text" placeholder="Re-Enter Password" name="repass"/>
                                </div>
                                <input type="submit" class="btn btn-lg btn-success btn-block" name="users_pass" Value="Set Password"/>
                            </fieldset>
                        </form>
                    </div>
					<?php }  else { $error_msg = 1; }
					}
					} else {
						$error_msg = 1; echo 2;
					} 
					if($error_msg == 1)
					{
						echo '<h2>Invalid Token... Please try Again Later </h2>';
					}
					?>
                </div>
            </div>
        </div>
    </div>
    
<?php include("footer.php");?>