<?php
// Initialize the session
session_start();
include_once './controller/sessions.php';


// Check if the user is logged in, if not then redirect him to login page (login.php)
if(!isset($_SESSION["email"]) || $_SESSION["email"] != true){
    header("location: index.php");
    exit;
}
else {
    include './controller/user_data.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>User Profile</title>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href=https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css >
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<style>
    .page-style{
        margin: 20px;
    }
</style>
</head>
<body>
    <div class="container m-auto">
        <div class="m-5 jumbotron">
            <div class="page-header mt-5 text-primary">
                <h4>Hi, <?php echo $result["uFirstName"]; ?></h4>
            </div>
            <div class="page-header">
                <h4>Your data</h4> <?php if($result["uKey"]===null) {
                    echo '
                    <label for="" class="text-muted"> <strong>Verify Email for API:</strong> </label>
                    <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#otp" onclick="sendMail()">Verify</button>
                ';}?>
                <div class="m-auto">
                <table class="table table-hover table-dark table_sort">
                    <thead>
                    <?php echo "<tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Pass</th>
                            <th>Photo</th>
                            <th>Key</th>
                          </tr>";?>
                    </thead>
                    <tbody>
                    <?php echo "<tr>";
                    foreach ($result as $value){
                        echo "<td>$value</td>";
                    }
                    echo "</tr>";?>
                    </tbody>
                </table>
            </div>
            </div>
            <?php if($result["uKey"]!==null) {
            echo '<div class="m-auto">
                <h4>Another users</h4>
                <table class="table table-hover table-dark table_sort">
                    <thead>
                        <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Photo</th>
                        <th>Key</th>
                      </tr>
                      </thead>
                        <tbody>';
                            foreach ($user_list as $key => $value){
                                echo "<tr>";
                                foreach ($value as $shit) {
                                    echo "<td>$shit</td>";
                                }
                                echo "</tr>";
                            }

                        echo '</tbody>
                </table>
            </div>';}?>
            <p>
                <a href="logout.php" class="btn btn-danger ">Sign Out of Your Account</a>
            </p>
            </div>
        </div>

    <!-- Modal -->
    <div class="modal fade" id="otp" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Otp Validation</h5>
                    <div class="text-danger text-center mx-auto" id="oEstate"></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form action="" class="" id="otpForm" onfocus="focusFunction()">
                        <p>
                            <strong>We have sent a secret code to your email.
                                <br>
                                Please check your email and insert the code in the following input field:
                            </strong>
                        </p>
                        <div class="form-group">
                            <label for="" class="text-muted"> <strong>Two Factor Authentication code:</strong> </label>
                            <input type="hidden" name="email" id="email">
                            <input type="text" class="form-control" maxlength="6" name="sCodeIn" id="sCodeIn" aria-describedby="emailHelpId" placeholder="Enter Otp" required>
                            <input name="sCodeBt" id="sCodeBt" class="btn btn-primary" type="submit" value="Verify Code">

                        </div>
                    </form>
                </div>
            </div>
        </div>
    <script src="./js/main.js"></script>
</body>
</html>
<?php }?>