<?php
//database connection
require_once 'config.php';

//Initialize variable to store feedback
$message = '';

//Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Sanitize input data 
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; 
    
    //Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    //Checks email if already registered
    $check_user = $conn->query("SELECT * FROM Users WHERE email = '$email'");
    
    if ($check_user->num_rows > 0) {
        //Show error message if failure
        $message = '<div style="color: red; margin-bottom: 15px;">Email already in use. Please try another.</div>';
    } else {
        //Prepare SQL query
        $sql = "INSERT INTO Users (firstname, surname, email, password) 
                VALUES ('$firstname', '$surname', '$email', '$hashed_password')";
        
        //Execute the query
        if ($conn->query($sql) === TRUE) {
            // Registration successful - redirect to login page with success parameter
            header("Location: login.php?registered=true");
            exit(); 
        } else {
            //Display error message if there is an error in the database
            $message = '<div style="color: red; margin-bottom: 15px;">Error: ' . $conn->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /*Basic reset and page background*/
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(165, 165, 165);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 95vh;
        }
        
        /*Main container for registration form*/
        .register-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        
        /*Heading style*/
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        /*Form group container style*/
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        /*Label style*/
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        /*Input field style*/
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        /*Register button style*/
        .register-btn {
            background-color:rgb(0, 116, 217);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        
        /*Register button hover*/
        .register-btn:hover {
            background-color: rgb(0, 116, 217);
        }
        
        /*Login hype link container style*/
        .login-link {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
        
        /*Login here hypelink style*/
        .login-link a {
            color:rgb(0, 116, 217);
            text-decoration: none;
        }
        
        /*Login here link hover style*/
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!--Main container-->
    <div class="register-container">
       
        <!--Everpound company logo-->
        <img src="logo.png" alt="Company Logo" style="width: 200px; height: auto; ">
        
        <!--Form/page title-->
        <h2>-=- Register an account -=-</h2>
        
        <!--Display error or success msg-->
        <?php echo $message; ?>
        
        <!--Register form-->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!--First name input-->
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            
            <!--Form Surname input-->
            <div class="form-group">
                <label for="surname">Surname</label>
                <input type="text" id="surname" name="surname" required>
            </div>
            
            <!--Form email input-->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <!--Form password input-->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <!--Form submit button-->
            <button type="submit" class="register-btn">Register</button>
        </form>
        
        <!--Links to the login page if an existing user-->
        <div class="login-link">
            Already have an account?<a href="login.php"> Click here to Log in</a>
        </div>
    </div>
</body>
</html>
