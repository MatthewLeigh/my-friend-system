<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Matthew Cross">
    <meta name="description" content=" My Friend System Add Friends Page">
    <meta name="keywords" content="My Friend System, Social Network, Add">
    <title>My Friend System | Add Friends</title>

    <link rel="stylesheet" href="style/style.css">
    <?php
        include_once "functions/sanitize.php";
        include_once "functions/sql.php";
        include_once "functions/get_initials.php";
    ?>
</head>

<body>

    <?php

    // ==> Initialize variables.
    $potential_friends = [];
    $num_of_friends = 0;
    $add_friend_id = null;
    $hasNextPage = false;
    $pagination_limit = 5;

    // ==> Retrieve pagination offset.
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["pagination_offset"])) {
            $pagination_offset = sanitize($_POST["pagination_offset"]);
        }
        else {
            $pagination_offset = 0;
        }
    }

    // ==> Start the session.
    session_start();

    // ==> Redirect to 'login.php' if not logged in.
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
        header("Location: login.php");
        die();
    }

    // ==> Redirect to 'login.php' if required session variables are not set.
    if (
        !isset($_SESSION["email"]) ||
        !isset($_SESSION["password"]) ||
        !isset($_SESSION["profile_name"]) ||
        !isset($_SESSION["friend_id"])
    ) {
        header("Location: login.php");
        die();
    }

    // ==> Check if friend is being added.
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["add_friend_id"])) {
            $add_friend_id = sanitize($_POST["add_friend_id"]);
        }
    }

    try {

        // ==> Connect to the SQL Database.
        $conn = @new mysqli($host, $user, $pwd, $sql_db);
        if ($conn->connect_error) {
            throw new Exception(
                "<p>Database Connection Failed: $conn->connect_error</p>"
            );
        }
        
        // ==> Set the current user's ID for easier parameter binding.
        $id = $_SESSION["friend_id"];

        // ==> Add friend to 'myfriends' table.
        if ($add_friend_id !== null) {
            $query = @$conn->prepare($SQL["add-myfriends"]);
            $query->bind_param("ii", $id, $add_friend_id);
            $query->execute();
            if ($query->errno) {
                throw new Exception(
                    "<p>Failed to add friend: $query->error</p>"
                );
            }
            $query->close();

            // ==> Update 'num_of_friends' in 'friends' table.
            $query = @$conn->prepare($SQL["update-num-of-friends"]);
            $query->bind_param("i", $id);
            $query->execute();  
            if ($query->errno) {
                throw new Exception(
                    "<p>Failed to update friend count: $query->error</p>"
                );
            }
            $query->close();

            // ==> Update 'num_of_friends' in 'friends' table.
            $query = @$conn->prepare($SQL["update-num-of-friends"]);
            $query->bind_param("i", $add_friend_id);
            $query->execute();
            if ($query->errno) {
                throw new Exception(
                    "<p>Failed to update friend count: $query->error</p>"
                );
            }
            $query->close();
        }

        // ==> Retrieve users who are not the current user's friends.
        //     (Add 1 to the limit to check if there is a next page.)
        $pagination_limit_buffered = $pagination_limit + 1;
        $query = @$conn->prepare($SQL["select-not-my-friends-pagination"]);
        $query->bind_param("iiiii", $id, $id, $id, $pagination_offset, $pagination_limit_buffered);
        $query->execute();
        if ($query->errno) {
            throw new Exception(
                "<p>Failed to retrieve potential friends: $query->error</p>"
            );
        }
        $result = $query->get_result();
        $query->close();

        // ==> Check if there is a next page.
        if ($result->num_rows == $pagination_limit_buffered) {
            $hasNextPage = true;
        }
        
        // ==> Retrieve potential friends.
        if ($result->num_rows > 0) {
            $count = 0;
            while ($row = $result->fetch_assoc()) {
            if ($count < $pagination_limit) {
                $potential_friends[] = $row;
            }
            $count++;
            }
        }

        // ==> Retrieve friend count from 'friends' table.
        $query = @$conn->prepare($SQL["select-num-of-friends"]);
        $query->bind_param("i", $id);
        $query->execute();
        if ($query->errno) {
            throw new Exception(
                "<p>Failed to retrieve friend count: $query->error</p>"
            );
        }
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $num_of_friends = $row["num_of_friends"];

        // ==> Add 'num_mutual_friends' to each potential friend.
        foreach ($potential_friends as &$friend) {
            $friend_id = $friend["friend_id"];
            $query = @$conn->prepare($SQL["count-mutual-friends"]);
            $query->bind_param("ii", $id, $friend_id);
            $query->execute();
            if ($query->errno) {
                throw new Exception(
                    "<p>Failed to retrieve mutual friends: $query->error</p>"
                );
            }
            $result = $query->get_result();
            $row = $result->fetch_assoc();
            $friend["num_mutual_friends"] = $row["num_mutual_friends"];
            $query->close();
        }
        unset($friend);
        
    } catch (Exception $e) {

        // ==> Error Message
        $outcome = $e->getMessage();
    } 

    // ==> Close SQL Connection
    if (isset($conn) && $conn->connect_error === null) {
        $conn->close();
    }

    ?>

    <main class="content-wrapper">

        <!-- Header -->
        <header>
            <img src="images/logo.gif" alt="My Friend System Logo">
        </header>

        <!-- User Banner -->
        <div class="user">

            <!-- Profile Circle -->
            <div class="profile-circle">
                <p><?php echo get_initials($_SESSION["profile_name"]); ?></p>
            </div>

            <!-- User Information -->
            <div class="user-info">
                <p><strong><?php echo $_SESSION["profile_name"] ?></strong></p>
                <p><?php echo $_SESSION["email"] ?></p>
                <p class="friend-count"><?php echo $num_of_friends ?> friends</p>
            </div>

            <!-- Navigation -->
            <nav>
                <a href="friendlist.php">Friend List</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <!-- Add Friends List -->
        <div class="table-wrapper">
            <h1>Add Friends</h1>

            <!-- Outcome Message if Present -->
            <?php if (!empty($outcome)): ?>
                <div class="outcome">
                    <?php echo $outcome; ?>
                </div>

            <!-- Add Friends Table -->
            <?php else: ?>
            <table>
                <tbody>
                    <?php if (!empty($potential_friends)): ?>
                        <?php foreach ($potential_friends as $friend): ?>
                            <tr>

                                <!-- Profile Name -->
                                <td>
                                    <?php echo $friend["profile_name"]; ?>
                                </td>

                                <!-- Mutual Friends -->
                                <td>
                                    <?php 
                                        echo $friend["num_mutual_friends"]; 
                                        echo $friend["num_mutual_friends"] === 1 
                                            ? " Mutual Friend" 
                                            : " Mutual Friends";    
                                    ?>
                                </td>

                                <!-- Add Friend Button -->
                                <td>
                                    <form
                                        action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                                        method="post"
                                        id="add-friend-<?php echo $friend["friend_id"]; ?>"
                                    >
                                        <input
                                            type="hidden"
                                            name="add_friend_id"
                                            value="<?php echo $friend["friend_id"]; ?>"
                                        >
                                        <input
                                            type="submit"
                                            value="Add Friend"
                                        >
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <!-- If No Potential Friends -->
                    <?php else: ?>
                        <tr class="no-friends">
                            <td colspan="3">You're already friends with everyone!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination: Previous Page Button -->
            <div class="pagination-buttons">

                <?php if ($pagination_offset > 0): ?>
                    <form 
                        action="<?php echo $_SERVER["PHP_SELF"]; ?>" 
                        method="post"
                    >
                        <input 
                            type="hidden" 
                            name="pagination_offset" 
                            value="<?php echo $pagination_offset - $pagination_limit; ?>"
                        >
                        <input 
                            type="submit" 
                            value="Previous Page"
                            class="pagination-button"
                        >
                    </form>
                <?php endif; ?>

                <!-- Pagination: Next Page Button -->
                <?php if ($hasNextPage): ?>
                    <form 
                        action="<?php echo $_SERVER["PHP_SELF"]; ?>" 
                        method="post"
                    >
                        <input 
                            type="hidden" 
                            name="pagination_offset" 
                            value="<?php echo $pagination_offset + $pagination_limit; ?>"
                        >
                        <input 
                            type="submit" 
                            value="Next Page"
                            class="pagination-button"
                        >
                    </form>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>