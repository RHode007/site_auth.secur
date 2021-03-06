<?php
if($_POST){
    session_start();
    //! Set timezone 
    //date_default_timezone_set('Asia/Kolkata');
    
    include_once("../core/user.php");
    include_once("../core/database.php");
    include_once("./mailer.php");
    

    //! Instantiate DB & connect
    $database = new database();
    $db = $database->connect();
    
    //! registration form
    if($_POST['action']=="registration"){
        //! getting data
        $fName = htmlspecialchars(strip_tags($_POST['fname']));
        $photo = "123";
        //$photo = htmlspecialchars(strip_tags($_POST['photo']));
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $pass1 = htmlspecialchars(strip_tags($_POST['password1']));
        $pass2 = htmlspecialchars(strip_tags($_POST['password2']));
        $token = $_POST['g-recaptcha-response'];
        
        //! Validate Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errEmail = "Invalid email Id";
            
        }
        
        //! Validate Password
        if(strlen($pass1)<3){
            $errPass = "Minimum 10 characters.";
        }else{
            
            $uppercase = preg_match('@[A-Z]@', $pass1);
            $lowercase = preg_match('@[a-z]@', $pass1);
            $number    = preg_match('@[0-9]@', $pass1);
            
            if(!$uppercase || !$lowercase || !$number) {
                $errPass = "Must include uppercase, lowercase, and number(s).";
            }
            if($pass1 != $pass2){
                $errPass = "The password and confirmation password do not match.";   
            }
        }
        //! gRecaptcha
        $url = "https://www.google.com/recaptcha/api/siteverify";
		$data = [
			'secret' => "6LfpowEdAAAAALbwY7G0Mzh7N-ow-4DVAMIXwIX1",
			'response' => $token,
			// 'remoteip' => $_SERVER['REMOTE_ADDR']
		];

		$options = array(
		    'http' => array(
		      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		      'method'  => 'POST',
		      'content' => http_build_query($data)
		    )
		  );

		$context  = stream_context_create($options);
  		$response = file_get_contents($url, false, $context);

        $res = json_decode($response, true);

        //! captcha check
        if($res['success'] == false && @$res['score'] < 0.5){
            @$errCaptcha = "You are a Robot";
        }
        //! Errors
        $err = 
            array(
                "errEmail"=>@$errEmail,
                "errPass"=>@$errPass,
                "errCaptcha"=>@$errCaptcha
            );
        //! After Validate
        if(@$err['errEmail'] != null  || @$err['errPass'] != null || @$err['errCaptcha'] != null){
            echo json_encode(@$err);
        }else{
            //! Instantiate user object
            $user =  new user($db);
            $user->fName = $fName;
            $user->photo = $photo;
            $user->email = $email;
            $user->pass = password_hash($pass1, PASSWORD_ARGON2I);

            //! checking if mail is already existing or not 
            $mailCheck = $user->check_mail_exist();
            if($mailCheck){    
                $result = $user->create_user();
                if($result){
                    echo json_encode(
                        array('message'=>'success')
                    );
                }
                else{
                    echo json_encode(array('message'=>'error'));
                }
            }
            else{
                echo json_encode(array('extMail'=>'This email is already registered with another user...!'));
            }
        }
    }
     //! login form
     if($_POST['action']=="login"){
         //! getting data
         $email = htmlspecialchars(strip_tags($_POST['email'])); 
         $pass  = htmlspecialchars(strip_tags($_POST['pass']));
         
        //! validate form data
        if($pass !=null && $email !=null){
            $user = new user($db);
            $user->email = $email;
            $result = $user->select_user();
            if($result){
                //! verify password 
                $hash = $result['uPass'];
                if (password_verify($pass, $hash)) {
                    $_SESSION['email'] = $result['uEmail'];
                    echo json_encode(array(
                        "message" => "success",
                        "email" => $user->email
                    ), JSON_THROW_ON_ERROR);
                    include './sessions.php';
                }
                else {

                    echo json_encode(array(
                        "message"=> "Invalid Email or password!!!"
                    ));

                }
            }
            else{
                echo json_encode(array(
                    "message"=> "Invalid Email or password!!!"
                ));
            }
        }
     }
     
     //! otp validation 
     if($_POST['action']=="verify otp"){

        $otp = htmlspecialchars(strip_tags($_POST['otp']));
        if($otp && $otp !=""){

            //! Instantiate user
            $user = new user($db);
            
            //! set properties
            $user->otp = $otp;
            $user->email = $_SESSION['email'];

            //! check otp
            $result = $user->validate_otp();
            if($result){
                if($result['isExpired']==0){

                    $origin = new DateTime($result["createdAt"]);
                    $current = new DateTime();
                    $interval =$origin->diff($current);
                    // echo json_encode($interval);
                    if($interval->y  || $interval->m || $interval->d || $interval->h || $interval->i > 5 ){
                        echo json_encode(
                            array(                        
                                "message"=>"Otp Expired!!!"
                                )
                            );
                        $user->update_otp();
                    } else{
                        echo json_encode(array(
                            "message" => "success!!!"
                        ), JSON_THROW_ON_ERROR);
                        $user->update_otp();
                        //TODO user->setkey();
                        //$_SESSION['userId'] = $uid;
                        include './sessions.php';
                    }
                } else{
                    echo json_encode(
                        array(
                            "message"=>"Otp Expired!!!"
                            )
                        );
                }
            } else{
                echo json_encode(
                    array(
                        "message"=>"Invalid Otp!!!"
                        )
                    );
            }
        }
    }

     //Send mail
    if($_POST['action'] === "send_mail"){
        $email = $_SESSION['email'];
        if($email) {
            $user = new user($db);
            $user->email = $email;
            $result = $user->select_user();
            if ($result) {
                $user->otp = random_int(000000, 999999);
                $user->createdAt = date("y-m-d H:i:s");
                //! insert and validate the insert operation
                if ($user->insert_otp()) {
                    if (send_mail($user->otp, $user->email)) {
                        echo json_encode(array(
                            "message" => "Mail send",
                            "email" => $user->email
                        ), JSON_THROW_ON_ERROR);
                        include './sessions.php';
                    } else {
                        echo json_encode(array(
                            "message" => "Unable to send Mail!!!"
                        ), JSON_THROW_ON_ERROR);
                    }
                } else {
                    echo json_encode(array(
                        "message" => "error"
                    ), JSON_THROW_ON_ERROR);
                }
            } else {
                echo json_encode(array(
                    "message" => "No user in db?"
                ), JSON_THROW_ON_ERROR);
            }
        }
    }

}
else{
    header('location:../login.php');
}
