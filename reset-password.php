<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
require_once('includes/connect.php');
include('includes/header.php');
include('includes/navigation.php');
require_once('includes/smtp.php');
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
$reset_done=false;
if(isset($_POST) & !empty($_POST)){
    if(empty($_POST['password'])){ $errors[] = 'Password field is Required';}else{
        if(empty($_POST['passwordr'])){ $errors[] = 'Repeat Password field is Required';}else{
            // compare both password, if they match. generate the password hash
            if($_POST['password'] == $_POST['passwordr']){
                // create password hash
                $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }else{
                // error message
                $errors[] = 'Both Passwords Should Match';
            }
        }
    }

    // CSRF Token Validation
    if(isset($_POST['csrf_token'])){
        if($_POST['csrf_token'] === $_SESSION['csrf_token']){
        }else{
            $errors[] = "Problem with CSRF Token Verification";
        }
    }else{
        $errors[] = "Problem with CSRF Token Validation";
    }

    // CSRF Token Time Validation
    $max_time = 60*60*24;
    if(isset($_SESSION['csrf_token_time'])){
        $token_time = $_SESSION['csrf_token_time'];
        if(($token_time + $max_time) >= time()){
        }else{
            $errors[] = "CSRF Token Expired";
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }
    }else{
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }

    if(empty($errors)){
        // Update the password after submitting new password
        $count = 0;
        if(!empty($_POST['key']) && !empty($_POST['id'])){
            $sql = "SELECT * FROM password_reset WHERE reset_token=:reset_token AND uid=:uid";
            $result = $db->prepare($sql);
            $values = array(':reset_token'      => $_POST['key'],
                        ':uid'              => $_POST['id']
                         );
            $result->execute($values);
            $count = $result->rowCount();
        }
        if($count == 1){
            // update the password here
            $updsql = "UPDATE users SET password=:password, updated=NOW() WHERE id=:id";
            $updresult = $db->prepare($updsql);
            $values = array(':password' => $pass_hash,
                            ':id'       => $_POST['id']
                            );
            $updres = $updresult->execute($values);

            $usersql = "SELECT * FROM users WHERE id=?";
            $userresult = $db->prepare($usersql);
            $userresult->execute(array($_POST['id']));
            $user = $userresult->fetch(PDO::FETCH_ASSOC);
            if($updres){
                // Inserting activity into DB table
                $actsql = "INSERT INTO user_activity (uid, activity) VALUES (:uid, :activity)";
                $actresult = $db->prepare($actsql);
                $values = array(':uid'          => $_POST['id'],
                                ':activity'     => 'Password Updated with Reset Password'
                                );
                $actresult->execute($values);

                // deleting the reset token from password_reset table
                $delsql = "DELETE FROM password_reset WHERE reset_token=?";
                $delresult = $db->prepare($delsql);
                $delres = $delresult->execute(array($_POST['key']));
                if($delres){
                    $reset_done=true;
                    // send email
                    $mail = new PHPMailer(true);

                    try {
                        //Server settings
                        $mail->isSMTP();                                            // Set mailer to use SMTP
                        $mail->Host       = $smtphost;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = $smtpuser;                     // SMTP username
                        $mail->Password   = $smtppass;                               // SMTP password
                        $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
                        $mail->Port       = 587;                                    // TCP port to connect to

                        //Recipients
                        $mail->setFrom($fromemail, $fromname);
                        $mail->addAddress($user['email'], $user['username']);     // Add a recipient

                        
                        // Content
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = 'Password Updated';
                        $mail->Body    = "Hi {$user['username']},<br><br>"
                                       . "Your account password was just changed. You can now log in with your new password.<br><br>"
                                       . "If you did not make this change, contact the site admin immediately.";
                        $mail->AltBody = 'Your account password was just changed. You can now log in with your new password.';

                        $mail->send();
                        $messages[] = 'Password Updated Successfully! A confirmation email has been sent.';
                        $messages[] = "You can now <a href='login.php'>log in</a> with your new password.";
                    } catch (Exception $e) {
                        // password is already updated at this point - tell the user even if mail fails
                        $messages[] = 'Password Updated Successfully! You can now <a href="login.php">log in</a> with your new password.';
                        $errors[] = "Confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                        
                }

            }
        }else{
            $errors[] = "There is some problem with Reset Token, Contact Site Admin!";
        }
    }
}   
// Create CSRF token
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
$valid_token = false;
if(!$reset_done){
    // fetch the user details from database and display those details in disabled input fields, username & email
    if(!empty($_GET['key']) && !empty($_GET['id'])){
        $sql = "SELECT * FROM password_reset WHERE reset_token=:reset_token AND uid=:uid";
        $result = $db->prepare($sql);
        $values = array(':reset_token'      => $_GET['key'],
                        ':uid'              => $_GET['id']
                        );
        $result->execute($values);
        $count = $result->rowCount();
        if($count == 1){
            // Select SQL query to fetch user details from users table using user id
            $usersql = "SELECT * FROM users WHERE id=? AND activate=1";
            $userresult = $db->prepare($usersql);
            $userresult->execute(array($_GET['id']));
            $usercount = $userresult->rowCount();
            $userres = $userresult->fetch(PDO::FETCH_ASSOC);
            if($usercount == 1){
                $valid_token = true;
            }else{
                $errors[] = "Your Account is not Active, Please activate before resetting the password";
            }
        }else{
            $errors[] = "There is some problem with Reset Token, Contact Site Admin!";
        }
    }else{
        $errors[] = "There is some problem with Reset Token, Contact Site Admin!";
    }
}
?>
<div id="guest-wrapper">
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Update Password</h3>
            </div>
            <div class="panel-body">
                <?php
                    if(!empty($messages)){
                        echo "<div class='alert alert-success'>";
                        foreach ($messages as $message) {
                            echo "<span class='glyphicon glyphicon-ok'></span>&nbsp;". $message ."<br>";
                        }
                        echo "</div>";
                    }
                ?>
                <?php if($valid_token){ ?>
                <form role="form" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                    <input type="hidden" name="key" value="<?php echo htmlspecialchars($_GET['key']); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                    <fieldset>
                        <div class="form-group">
                            <input class="form-control" placeholder="User Name" name="username" type="text" autofocus disabled value="<?php if(isset($userres['username'])){ echo $userres['username']; } ?>">
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="E-mail" name="email" type="email" disabled value="<?php if(isset($userres['email'])){ echo $userres['email']; } ?>">
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="Password" name="password" type="password" >
                        </div>
                        <div class="form-group">
                            <input class="form-control" placeholder="Repeat Password" name="passwordr" type="password">
                        </div>
                        <!-- Change this to a button or input when using this as a form -->
                        <input type="submit" class="btn btn-lg btn-success btn-block" value="Change Password" />
                    </fieldset>
                </form>
                <?php } ?>
                <hr>
                <p class="text-center">
                    <a href="login.php">Back to Sign In</a>
                </p>
             </div>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>
