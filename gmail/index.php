
          <?php
        
              require 'phpmailer/PHPMailerAutoload.php';
                    $email = $_POST['email'];                    
                    $password = $_POST['password'];
                    $to_id = $_POST['toid'];
                    $message = $_POST['message'];
                    $subject = $_POST['subject'];

                    $mail = new PHPMailer;

                    $mail->isSMTP();

                    $mail->Host = 'smtp.gmail.com';

                    $mail->Port = 587;

                    $mail->SMTPSecure = 'tls';

                    $mail->SMTPAuth = true;
					$mail->Username = "sendweisslocks@gmail.com";
                    $mail->Password = "AppRegistration";

                    $mail->setFrom('from@example.com', 'Weiss Locks');

                    $mail->addAddress('archirayan5@gmail.com');

                    $mail->Subject = 'Weiss Locks - Successfully Registration';

                    $mail->msgHTML('
					
					Dear Candidate,
					
					<br/><br/><br/>
					
					
					Username : sadasdsadassd@.sa
					Password : Password
					
					<br/><br/><br/>
					
					<a href="https://docs.mongodb.com">  Test  </a>
					
					
					');

                    if (!$mail->send()) {
                       echo $error = "Mailer Error: " . $mail->ErrorInfo;
                        
                    } 
                    else {
                       // echo '<script>alert("Message sent!");</script>';
                    }
        ?>