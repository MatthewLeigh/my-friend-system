<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Matthew Cross">
    <meta name="description" content="My Friend System Login Page">
    <meta name="keywords" content="My Friend System, Social Network, Login">
    <title>My Friend System | Login</title>

    <link rel="stylesheet" href="style/style.css">
    <?php 
        include_once "functions/sanitize.php";
        include_once "functions/sql.php";
    ?>
</head>

<body>

    <?php

    // Initialize variables.
    $formErrors = [];
    $databaseErrors = [];
    
    $emailError = false;
    $passwordError = false;

    $outcome = "";
    $email = "";
    $password = "";

    // Check if the form has been submitted.
    if (empty($_POST)) {
        $outcome = 
            "<h1>Welcome to the Login Page</h1>" . 
            "<p>Please enter your email and password to log in.</p>";
    }

    // Retrieve form data and validate fields are not empty.
    if (empty($outcome)) {

        // ==> Email
        if (isset($_POST["login-email"]) && !empty($_POST["login-email"])) {
            $email = sanitize($_POST["login-email"]);
        } else {
            $formErrors[] = "<p>Please enter your email address</p>";
            $emailError = true;
        }

        // ==> Password
        if (isset($_POST["login-password"]) && !empty($_POST["login-password"])) {
            $password = sanitize($_POST["login-password"]);
        } else {
            $formErrors[] = "<p>Please enter your password</p>";
            $passwordError = true;
        }

        // ==> Add to outcome if form errors exist.
        if ($formErrors) {
            $outcome = 
                "<h1>Incomplete Form</h1>" .
                implode("", $formErrors);
        }
    }

    // Validate form data.
    if (empty($outcome)) {

        // ==> Email : Validation Check 1
        //     Confirm that email is correctly formatted.      
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = "<p>Please enter a valid email address</p>";
            $emailError = true;
        }

        // ==> Email : Validation Check 2
        //     Confirm that email is 50 characters or less.
        if (strlen($email) > 50) {
            $formErrors[] = "<p>Email address must be 50 characters or less</p>";
            $emailError = true;
        }

        // ==> Password
        //     Confirm that password is 20 characters or less.
        if (strlen($password) > 20) {
            $formErrors[] = "<p>Password must be 20 characters or less</p>";
            $passwordError = true;
        }

        // ==> Add to outcome if form errors exist.
        if ($formErrors) {
            $outcome = 
                "<h1>Invalid Form Data</h1>" .  
                implode("", $formErrors);
        }
    }

    // Database connection and sql query.
    // ==> Confirm that email is registered, and that password is correct.
    if (empty($outcome)) {
        try {

            // ==> Database : Connect to SQL Database
            $conn = @new mysqli($host, $user, $pwd, $sql_db);
            if ($conn->connect_error) {
                throw new Exception(
                    "<h1>Database Connection Failed</h1>" .
                    "<p>$conn->connect_error</p>"
                );
            }

            // ==> Email : Retrieve data from "friends" table where email matches
            $query = @$conn->prepare($SQL["select-all-friends-where-email"]);
            $query->bind_param("s", $email);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows == 0) {
                $emailError = true;
                throw new Exception(
                    "<h1>Invalid Email</h1>" .
                    "<p>Provided email has not been registered. Please confirm 
                        spelling is correct and try again.</p>"
                );
            }

            if ($result->num_rows > 1) {
                throw new Exception(
                    "<h1>Database Error</h1>" . 
                    "<p>Multiple users registered to the provided email address. 
                        Please contact support, as there is likely an error with 
                        the database.</p>"
                );
            }

            // ==> Password : Confirm password is correct.
            $row = @$result->fetch_assoc();
            if ($row["password"] !== $password) {
                $passwordError = true;
                throw new Exception(
                    "<h1>Incorrect Email or Password</h1>" . 
                    "<p>The provided email or password is incorrect. Please 
                        confirm spelling is correct and try again</p>"
                );
            }

            // ==> ID and Username : Retrieve other relevant data from "friends" 
            //     table where email matches. Data to be stored in session 
            //     later.
            $friend_id = $row["friend_id"];
            $profile_name = $row["profile_name"];

        } catch (Exception $e) {

            // ==> Set outcome to error message.
            $outcome = $e->getMessage();
    
        } 

        // ==> Close SQL Connection
        if (isset($conn) && $conn->connect_error === null) {
            $conn->close();
        }
    }

    // If log in is successful, retrieve user data, start a session, and 
    // redirect to friendlist.php
    if (empty($outcome)) {
        session_start();
        $_SESSION["email"] = $email;
        $_SESSION["password"] = $password;
        $_SESSION["friend_id"] = $friend_id;
        $_SESSION["profile_name"] = $profile_name;
        $_SESSION['logged_in'] = true;
        header("Location: friendlist.php");
    }

    ?>

    <div class="form-wrapper">

        <!-- Form Banner -->
        <div class="form-banner">
            <img src="images/logo.png" alt="Site logo">
            <div>
                <?php echo $outcome ?>
            </div>
            <div class="links-wrapper">
                <a class="link" href="index.php">Homepage</a>
            </div>
        </div>

        <!-- Form -->
        <form 
            action="login.php" 
            method="post" 
            id="login-form" 
            novalidate
        >
            <h2>User Login</h2>

            <!-- Email -->
            <div class="form-input">
                <label class="form-label" for="email">
                    <img src="images/icons/letter-black.png" alt="Email Icon">
                </label>
                <input 
                    type="email" 
                    name="login-email" 
                    id="login-email" 
                    title="Please enter a valid email address" 
                    placeholder="Email Address" 
                    value="<?php echo $email ?>" 
                    autocomplete="off"
                    autofocus 
                >
                <?php 
                    if ($emailError) {
                        echo "<img src='images/icons/error-black.png' alt='Error Icon'>"; 
                    }
                ?>
            </div>

            <!-- Password -->
            <div class="form-input">
                <label class="form-label" for="password">
                    <img src="images/icons/lock-black.png" alt="Password icon">
                </label>
                <input 
                    type="password" 
                    name="login-password" 
                    id="login-password" 
                    title="Please enter your password" 
                    placeholder="Password" 
                    value=""
                >
                <?php 
                    if ($passwordError) {
                        echo "<img src='images/icons/error-black.png' alt='Error Icon'>"; 
                    }
                ?>
            </div>

            <!-- Clear Form -->
            <div class="form-row">
                <label class="reset-wrapper">
                    <input type="reset" value="Clear Form">
                    <img src="images/icons/refresh-blue.png" alt="Refresh Icon">
                </label>
            </div>

            <!-- Submit button -->
            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>