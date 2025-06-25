<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Matthew Cross">
    <meta name="description" content="View and remove your current friends">
    <meta name="keywords" content="My Friend System, Friend List, Remove">
    <title>My Friend System | Friend List</title>

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
    $outcome = "";
    $num_of_friends = 0;
    $friends_list = [];
    $remove_friend_id = null;
    $hasNextPage = false;
    $pagination_limit = 5;

    // ==> Retrieve pagination offset.
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["pagination_offset"])) {
            $pagination_offset = sanitize($_POST["pagination_offset"]);
        } else {
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

    // ==> Check if friend is being removed.
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["remove_friend_id"])) {
            $remove_friend_id = sanitize($_POST["remove_friend_id"]);
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

        // ==> Set the session friend_id to a variable to use in SQL queries.
        $id = $_SESSION["friend_id"];

        // ==> Remove friend from 'myfriends' table.
        if ($remove_friend_id !== null) {
            $query = @$conn->prepare($SQL["remove-myfriends"]);
            $query->bind_param("iiii", $id, $remove_friend_id, $remove_friend_id, $id);
            $query->execute();
            if ($query->errno) {
                throw new Exception(
                    "<p>Friend could not be removed. Please try again.</p>"
                );
            }
            $query->close();

            // ==> Update number of friends for user.
            $query = @$conn->prepare($SQL["update-num-of-friends"]);
            $query->bind_param("i", $id);
            $query->execute();
            if ($query->errno) {
                throw new Exception(
                    "<p>Friend count could not be updated.</p>"
                );
            }
            $query->close();

            // ==> Update number of friends for removed friend.
            $query = @$conn->prepare($SQL["update-num-of-friends"]);
            $query->bind_param("i", $remove_friend_id);
            $query->execute();
            if ($query->errno) {
                throw new Exception(
                    "<p>Friend count could not be updated.</p>"
                );
            }
            $query->close();

        }

        // ==> Retrieve users friends.
        //     (Add 1 to the limit to check if there is a next page.)
        $pagination_limit_buffered = $pagination_limit + 1;
        $query = @$conn->prepare($SQL["select-my-friends-pagination"]);
        $query->bind_param("iiii", $id, $id, $pagination_offset, $pagination_limit_buffered);
        $query->execute();
        if ($query->errno) {
            throw new Exception(
                "<p>Potential friends could not be retrieved. Please try again.</p>"
            );
        }
        $result = $query->get_result();
        $query->close();

        // ==> Check if there is a next page.
        if ($result->num_rows == $pagination_limit_buffered) {
            $hasNextPage = true;
        }

        // ==> Retrieve friends list.
        if ($result->num_rows > 0) {
            $count = 0;
            while ($row = $result->fetch_assoc()) {
                if ($count < $pagination_limit) {
                    $friends_list[] = $row;
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
                "<p>Friend count could not be retrieved.</p>"
            );
        }
        $result = $query->get_result();
        $query->close();
        $row = $result->fetch_assoc();
        $num_of_friends = $row["num_of_friends"];

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
            <nav>
                <a href="friendadd.php">Add Friends</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <!-- Friend List -->
        <div class="table-wrapper">
            <h1>Friend List</h1>

            <!-- Outcome Message if Present -->
            <?php if (!empty($outcome)): ?>
                <div class="outcome">
                    <?php echo $outcome; ?>
                </div>
            
            <!-- Friend List Table -->
            <?php else: ?>
            <table>
                <tbody>
                    <?php if (!empty($friends_list)): ?>
                        <?php foreach ($friends_list as $friend): ?>
                            <tr>

                                <!-- Profile Name -->
                                <td>
                                    <?php echo $friend["profile_name"]; ?>
                                </td>

                                <!-- Remove Friend Button -->
                                <td>
                                    <form
                                        action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                                        method="post"
                                        id="unfriend-<?php echo $friend["friend_id"] ?>"
                                    >
                                        <input
                                            type="hidden"
                                            name="remove_friend_id"
                                            value="<?php echo $friend["friend_id"]; ?>"
                                        >
                                        <input
                                            class="remove-button"
                                            type="submit"
                                            value="Unfriend"
                                        >
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <!-- If no friends -->
                    <?php else: ?>
                        <tr class="no-friends">
                            <td colspan="2">
                                Let's make some friends! 
                                <a href="friendadd.php">Add Friends</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-buttons">

                <!-- Pagination: Previous Page Button -->
                <?php if ($pagination_offset > 0): ?>
                    <form
                        action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                        method="post">
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
                        method="post">
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