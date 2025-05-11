<?php
//Include the database configuration file
require_once 'config.php';

//Initialize a variable to store user feedback messages
$message = '';

//Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Sanitize input data 
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];  // Get the raw password input 
    
    //Prepare a SQL statement to find the user by email
$stmt = $conn->prepare("SELECT user_id, password FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password against the stored 
        if (password_verify($password, $user['password'])) {
            // Authentication successful - start session and store user ID
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            
            // Redirect to index page after valid login
            header("Location: index.php");
            exit();
        } else {
            // Password is invaild
            $message = '<div style="color: red; margin-bottom: 15px;">Invalid email or password. Please try again.</div>';
        }
    } else {
        // User no found
        $message = '<div style="color: red; margin-bottom: 15px;">Invalid email or password. Please try again.</div>';
    }
 // Close the prepared statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /*Basic reset and page background styling*/
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
        
        /*Main container for login form styling*/
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        
        /* Page Heading style*/
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        /*Form group group styling*/
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        /* Input Label styl*/
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        /*Input field styling*/
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        /*Login button styling*/
        .login-btn {
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
        
        /*Login button hover*/
        .login-btn:hover {
            background-color: rgb(0, 116, 217);
        }
        
        /*Register hype link container style*/
        .register-link {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
        
        /*Register here hypelink style*/
        .register-link a {
            color:rgb(0, 116, 217);
            text-decoration: none;
        }
        
        /*Register here link hover style*/
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!--Main login container-->
    <div class="login-container">
       
        <!--Everpound company logo display-->
        <img src="logo.png" alt="Company Logo" style="width: 200px; height: auto; ">
        
        <!--Page heading-->
        <h2>-=- Login -=-</h2>
        
        <!--Display error or success msg-->
        <?php echo $message; ?>
        
        <!--Login form-->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!--Form email input-->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <!--Password input field-->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <!--Form submission button-->
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <!--Links to the register page for new users-->
        <div class="register-link">
            Don't have an account?<a href="register.php"> Click here to Register</a>
        </div>
    </div>
</body>
</html>
