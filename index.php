<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Matthew Cross">
    <meta name="description" content="My Friend System Homepage">
    <meta name="keywords" content="My Friend System, Social Network, Homepage">
    <title>My Friend System | Homepage</title>

    <link rel="stylesheet" href="style/style.css">
    <?php include_once "functions/sql.php" ?>
</head>

<body>

    <?php

    // Initialize Outcome Status and Message
    $status = "success";
    $message = "Connection Successful!";

    try {

        // ==> Connect to SQL Database
        $conn = @new mysqli($host, $user, $pwd, $sql_db);
        if ($conn->connect_error) {
            throw new Exception(
                "Connection Failed: Error " 
                . $conn->connect_errno . " - " . $conn->connect_error
            );
        }

        // ==> Create Table : "friends"
        $result = $conn->query($SQL['create-friends']);
        if ($result === FALSE) {
            throw new Exception(
                "Error creating friends table: Error " 
                . $conn->errno . " - " . $conn->error
            );
        }

        // ==> Create Table : "myfriends"
        $result = $conn->query($SQL['create-myfriends']);
        if ($result === FALSE) {
            throw new Exception(
                "Error creating myfriends table: Error " 
                . $conn->errno . " - " . $conn->error
            );
        }

        // ==> Check if Data Exists : "friends"
        $resultCheckFriends = $conn->query($SQL['select-first-friends']);
        if ($resultCheckFriends === FALSE) {
            throw new Exception(
                "Error checking friends table: Error " 
                . $conn->errno . " - " . $conn->error
            );
        } 
        
        // ==> Check if Data Exists : "myfriends"
        $resultCheckMyFriends = $conn->query($SQL['select-first-myfriends']);
        if ($resultCheckMyFriends === FALSE) {
            throw new Exception(
                "Error checking myfriends table: Error " 
                . $conn->errno . " - " . $conn->error
            );
        }

        // ==> Initialize Data if either table is Empty : "friends" and 
        //     "myfriends"
        if (
            $resultCheckFriends->num_rows === 0 || 
            $resultCheckMyFriends->num_rows === 0
        ) {

            // Get Data
            include_once "functions/initialize_data.php";

            // Populate friends table.
            foreach ($friends as $friend) {

                // Prepare statement.
                $query = $SQL['insert-new-friend'];
                $stmt = $conn->prepare($query);
                $email = $friend[0];
                $password = $friend[1];
                $username = $friend[2];
                $stmt->bind_param("ssss", $email, $password, $username, $date);

                // Execute statement.
                if (!$stmt->execute()) {
                    throw new Exception(
                        "Error populating friends table: Error " 
                        . $conn->errno . " - " . $conn->error
                    );
                }
            }

            // Populate myfriends table.
            foreach ($friendships as $pair) {

                // Prepare statement.
                $query = $SQL['add-myfriends'];
                $stmt = $conn->prepare($query);
                $id1 = $pair[0];
                $id2 = $pair[1];
                $stmt->bind_param("ii", $id1, $id2);

                // Execute statement.
                if (!$stmt->execute()) {
                    throw new Exception(
                        "Error populating myfriends table: Error " 
                        . $conn->errno . " - " . $conn->error
                    );
                }
            }

            // Update number of friends for each friend.
            $query = $SQL['update-num-of-friends'];
            $stmt = $conn->prepare($query);

            // Prepare a list of unique_ids for each friend.
            $unique_ids = array_unique(call_user_func_array('array_merge', $friendships));

            // Execute statement for each friend.
            foreach ($unique_ids as $friend_id) {
                $stmt->bind_param("i", $friend_id);
                if (!$stmt->execute()) {
                    throw new Exception(
                        "Error updating number of friends for friend_id: " . 
                        $friend_id
                    );
                }
            }

            // Update outcome message to show data successfully initialized.
            $message = "Connection Successful and Data Initialized!";
        }

    } catch (Exception $e) {
        
        // ==> Set status and message for caught error.
        $status = "error";
        $message = $e->getMessage();

    } 

    // ==> Close SQL Connection
    //     Note: Finally block is not used to close the connection as they are
    //           not supported in PHP 5.4.16, which is the version of PHP
    //           running on the mercury server.
    if (isset($conn) && $conn->connect_error === null) {
        $conn->close();
    }

    ?>

    <main>
        <div class="wide">

            <!-- Hero -->
            <div id="banner-homepage">
                <img src="images/logo.gif" alt="My Friend System Logo">
                <div class="text">
                    <div id="founder-box">
                        <p><strong>Matthew Cross</strong></p>
                        <p>101828627@student.swin.edu.au</p>
                        <p>101 828 627</p>
                    </div>
                    <div class="links-wrapper">
                        <a class="link" href="signup.php">Sign Up</a>
                        <a class="link" href="login.php">Log In</a>
                        <a class="link" href="about.php">About</a>
                    </div>
                    <p class="declaration">
                        "I declare that this assignment is my individual work. 
                        I have not worked collaboratively nor have I copied 
                        from any other student’s work or from any other source."
                    </p>
                </div>

                <!-- Outcome Message -->
                <p class=<?php echo $status ?>>
                    <?php echo $message ?>
                </p>
            </div>

            <!-- Login / Sign Up -->
            <div class="credentials-wrapper">

                <!-- Login Form -->
                <div class="credentials" id="login">
                    <form 
                        action="login.php" 
                        method="post" 
                        id="homepage-login-form" 
                        novalidate
                    >
                        <h2>User Login</h2>

                        <!-- Email -->
                        <div class="form-input">
                            <label class="form-label" for="email">
                                <img 
                                    src="images/icons/letter-black.png" 
                                    alt="Email icon"
                                >
                            </label>
                            <input 
                                type="email" 
                                name="login-email" 
                                id="login-email" 
                                title="Please enter a valid email address" 
                                placeholder="Email Address" 
                                autocomplete="off"
                            >
                        </div>

                        <!-- Password -->
                        <div class="form-input">
                            <label class="form-label" for="password">
                                <img 
                                    src="images/icons/lock-black.png" 
                                    alt="Password icon"
                                >
                            </label>
                            <input 
                                type="password" 
                                name="login-password" 
                                id="login-password" 
                                title="Please enter your password" 
                                placeholder="Password" 
                                autocomplete="off"
                            >
                        </div>

                        <!-- Link to login page -->
                        <div class="form-row">
                            <p>
                                <a href="login.php">Login Page</a>
                            </p>
                            <label class="reset-wrapper">
                                <input type="reset" value="Clear Form">
                                <img 
                                    src="images/icons/refresh-blue.png" 
                                    alt="Refresh Icon"
                                >
                            </label>
                        </div>

                        <!-- Submit button -->
                        <input type="submit" value="Login">
                    </form>
                </div>

                <!-- Register Form -->
                <div class="credentials" id="register">
                    <img 
                        src="images/icons/arrow-circle-left-black.png" 
                        alt="Left Arrow Button" 
                        id="register-button"
                    >
                    <form 
                        action="signup.php" 
                        method="post" 
                        id="homepage-register-form" 
                        novalidate
                    >
                        <h2>New User</h2>

                        <!-- Email -->
                        <div class="form-input">
                            <label class='form-label' for='email'>
                                <img 
                                    src="images/icons/letter-black.png" 
                                    alt="Email icon"
                                >
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                title="Please enter a valid email address" 
                                placeholder="Email Address" 
                                autocomplete="off"
                            >
                        </div>

                        <!-- Profile Name -->
                        <div class="form-input">
                            <label class="form-label" for="profile-name">
                                <img 
                                    src="images/icons/customer-black.png" 
                                    alt="Username icon"
                                >
                            </label>
                            <input 
                                type="text" 
                                name="profile-name" 
                                id="profile-name" 
                                title="Please enter a profile name" 
                                placeholder="Profile Name" 
                                autocomplete="off"
                            >
                        </div>

                        <!-- Password -->
                        <div class="form-input">
                            <label class="form-label" for="password">
                                <img 
                                    src="images/icons/lock-black.png" 
                                    alt="Password icon"
                                >
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                title="Please enter your password" 
                                placeholder="Password" 
                                autocomplete="off"
                            >
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-input">
                            <label class="form-label" for="confirm-password">
                                <img
                                    src="images/icons/lock-black.png" 
                                    alt="Password icon"
                                >
                            </label>
                            <input 
                                type="password" 
                                name="confirm-password" 
                                id="confirm-password" 
                                title="Please enter your password" 
                                placeholder="Confirm Password" 
                                autocomplete="off"
                            >
                        </div>

                        <!-- Link to login page -->
                        <div class="form-row">
                            <p>
                                <a href="signup.php">Registration Page</a>
                            </p>
                            <label class="reset-wrapper">
                                <input type="reset" value="Clear Form">
                                <img 
                                    src="images/icons/refresh-blue.png" 
                                    alt="Refresh Icon"
                                >
                            </label>
                        </div>
                        
                        <!-- Submit button -->
                        <input type="submit" value="Register">
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>

</html>