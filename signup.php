<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Matthew Cross">
    <meta name="description" content="My Friend System Sign Up Page">
    <meta name="keywords" content="My Friend System, Social Network, Sign Up">
    <title>My Friend System | Sign Up</title>

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
    $duplicateErrors = [];

    $emailError = false;
    $profileError = false;
    $passwordError = false;
    $confirmError = false;

    $outcome = '';
    $email = '';
    $profileName = '';
    $password = '';
    $confirmPassword = '';

    // Check if the form was submitted.
    if (empty($_POST)) {
        $outcome =
            "<h1>The Future of Social Media</h1>" .
            "<p>Simply fill in the form below to get started!</p>";
    }

    // Retrieve form data and validate fields are not empty.
    if (empty($outcome)) {

        // ==> Email
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = sanitize($_POST['email']);
        } else {
            $formErrors[] = '<p>Please enter an email address</p>';
            $emailError = true;
        }

        // ==> Profile Name
        if (isset($_POST['profile-name']) && !empty($_POST['profile-name'])) {
            $profileName = sanitize($_POST['profile-name']);
        } else {
            $formErrors[] = '<p>Please enter a profile name</p>';
            $profileError = true;
        }

        // ==> Password
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $password = sanitize($_POST['password']);
        } else {
            $formErrors[] = '<p>Please enter a password</p>';
            $passwordError = true;
        }

        // ==> Confirm Password
        if (isset($_POST['confirm-password']) && !empty($_POST['confirm-password'])) {
            $confirmPassword =  sanitize($_POST['confirm-password']);
        } else {
            $formErrors[] = '<p>Please confirm your password</p>';
            $confirmError = true;
        }

        // ==> All
        //     Change message if enough fields are empty.
        if (count($formErrors) > 2) {
            $formErrors = ['<p>Please complete all fields below</p>'];
        }

        // ==> Add to outcome if form errors exist.
        if ($formErrors) {
            $outcome =
                "<h1>Incomplete Form</h1>" .
                implode('', $formErrors);
        }
    }

    // Validate form data if all fields are not empty.
    if (empty($outcome)) {

        // ==> Email : Validation Check 1
        //     Confirm that email is correctly formatted.      
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = '<p>Please enter a valid email address</p>';
            $emailError = true;
        }

        // ==> Email : Validation Check 2
        //     Confirm that email is 50 characters or less.
        if (strlen($email) > 50) {
            $formErrors[] = '<p>Email address must be 50 characters or less</p>';
            $emailError = true;
        }

        // ==> Profile Name : Validation Check 1
        //     Confirm that profile name is 30 characters or less.
        if (strlen($profileName) > 30) {
            $formErrors[] = '<p>Profile name must be 30 characters or less</p>';
            $profileError = true;
        }

        // ==> Password : Validation Check 1
        //     Confirm that password is 20 characters or less.
        if (strlen($password) > 20) {
            $formErrors[] = '<p>Password must be 20 characters or less</p>';
            $passwordError = true;
        }

        // ==> Confirm Password : Validation Check 1
        //     Confirm that both passwords match.
        if ($password !== $confirmPassword) {
            $formErrors[] = '<p>Please ensure that both passwords match</p>';
            $passwordError = true;
            $confirmError = true;
        }

        // ==> Add to outcome if form errors exist.
        if ($formErrors) {
            $outcome =
                "<h1>Invalid Form Data</h1>" .
                implode('', $formErrors);
        }
    }

    // Database connection and sql query.
    //     Check for duplicate email address or profile name.
    //     Insert new user into 'friends' table.
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

            // ==> Database : Retrieve data from 'friends' table
            $queryResult = @$conn->query($SQL['select-all-friends']);
            if ($queryResult === FALSE) {
                throw new Exception(
                    "<h1>Database Query Failed</h1>" .
                    "<p>$conn->error</p>"
                );
            }

            // ==> Database : Check for duplicate user credentials
            while ($row = $queryResult->fetch_assoc()) {

                // ==> Email
                if ($row['friend_email'] === $email) {
                    $emailError = true;
                    throw new Exception(
                        "<h1>Email Address Already Registered</h1>" .
                        "<p>Please enter a unique email address</p>"
                    );
                }

                // ==> Profile Name
                if ($row['profile_name'] === $profileName) {
                    $profileError = true;
                    throw new Exception(
                        "<h1>Profile Name Already Exists</h1>" .
                        "<p>Please enter a unique profile name</p>"
                    );
                }
            }

            // ==> Database : Insert new user into 'friends' table
            $query = $conn->prepare($SQL['insert-new-friend']);
            $date = date('Y-m-d');
            $query->bind_param('ssss', $email, $password, $profileName, $date);
            $query->execute();
            if ($query->affected_rows === 0) {
                throw new Exception(
                    "<h1>Database Insert Failed</h1>" .
                    "<p>$conn->error</p>"
                );
            }

            // ==> Find the new user's ID
            $query = @$conn->prepare($SQL['select-all-friends-where-email']);
            $query->bind_param('s', $email);
            $query->execute();
            $result = $query->get_result();
            $friend_id = $result->fetch_assoc()['friend_id'];

        } catch (Exception $e) {

            // ==> Set outcome to error message.
            $outcome = $e->getMessage();
        } 
    }

    // ==> Close SQL Connection
    if (isset($conn) && $conn->connect_error === null) {
        $conn->close();
    }

    // If registration is successful:
    //     Start a session.
    //     Redirect to friendadd.php.
    if (empty($outcome)) {
        session_start();
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        $_SESSION['profile_name'] = $profileName;
        $_SESSION['friend_id'] = $friend_id;
        $_SESSION['logged_in'] = true;
        header("Location: friendadd.php");
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
                <a class='link' href="index.php">Homepage</a>
            </div>
        </div>

        <!-- Form -->
        <form 
            action="signup.php" 
            method="post" 
            id="register-form" 
            novalidate
        >
            <h2>Registration Form</h2>

            <!-- Email -->
            <div class="form-input">
                <label class="form-label" for="email">
                    <img src="images/icons/letter-black.png" alt="Email Icon">
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
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

            <!-- Profile Name -->
            <div class="form-input">
                <label class="form-label" for="profile-name">
                    <img src="images/icons/customer-black.png" alt="Profile Icon">
                </label>
                <input 
                    type="text" 
                    name="profile-name" 
                    id="profile-name" 
                    title="Please enter a profile name" 
                    placeholder="Profile Name" 
                    value="<?php echo $profileName ?>" 
                    autocomplete="off"
                >
                <?php 
                    if ($profileError) {
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
                    name="password" 
                    id="password" 
                    title="Please enter your password" 
                    placeholder="Password" 
                >
                <?php 
                    if ($passwordError) {
                        echo "<img src='images/icons/error-black.png' alt='Error Icon'>";
                    }
                ?>
            </div>

            <!-- Confirm Password -->
            <div class="form-input">
                <label class="form-label" for="confirm-password">
                    <img src="images/icons/lock-black.png" alt="Password icon">
                </label>
                <input 
                    type="password" 
                    name="confirm-password" 
                    id="confirm-password" 
                    title="Please enter your password" 
                    placeholder="Confirm Password" 
                >
                <?php 
                    if ($confirmError || $passwordError) {
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
            <input type="submit" value="Register">
        </form>
    </div>
</body>

</html>