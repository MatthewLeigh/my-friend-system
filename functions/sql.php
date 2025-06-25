<?php
// SQL Database Credentials 
$host = "feenix-mariadb.swin.edu.au";
$user = "s101828627";
$pwd = "securepassword";
$sql_db = "s101828627_db";

// SQL Queries
$SQL = [

    // ==> Create Table : 'friends'
    "create-friends" =>
        "CREATE TABLE IF NOT EXISTS friends (
            friend_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            friend_email VARCHAR(50) NOT NULL,
            password VARCHAR(20) NOT NULL,
            profile_name VARCHAR(30) NOT NULL,
            date_started DATE NOT NULL,
            num_of_friends INT UNSIGNED DEFAULT 0
        )",

    // ==> Create Table : 'myfriends'
    "create-myfriends" =>
        "CREATE TABLE IF NOT EXISTS myfriends (
            friend_id1 INT NOT NULL,
            friend_id2 INT NOT NULL,
            PRIMARY KEY (friend_id1, friend_id2),
            FOREIGN KEY (friend_id1) REFERENCES friends(friend_id),
            FOREIGN KEY (friend_id2) REFERENCES friends(friend_id)
        )",

    // ==> Select First : 'friends' (To Check Not Empty)
    "select-first-friends" =>
        "SELECT * FROM friends LIMIT 1",

    // ==> Select All : 'friends'
    "select-all-friends" =>
        "SELECT * FROM friends",

    // ==> Select All : 'friends' : Where email
    "select-all-friends-where-email" =>
        "SELECT * FROM friends WHERE friend_email = ?",

    // ==> Insert New User : 'friends'
    "insert-new-friend" =>
        "INSERT INTO friends (friend_email, password, profile_name, date_started)
            VALUES (?, ?, ?, ?)",

    // Select First : 'myfriends' (To Check Not Empty)
    "select-first-myfriends" =>
        "SELECT * FROM myfriends LIMIT 1",

    // Select Number of Friends : 'friends'
    "select-num-of-friends" =>
        "SELECT num_of_friends FROM friends WHERE friend_id = ?",

    // ==> Add Friend : 'myfriends'
    "add-myfriends" =>
        "INSERT INTO myfriends (friend_id1, friend_id2)
            VALUES (?, ?)",

    // ==> Remove Friend : 'myfriends'
    "remove-myfriends" =>
        "DELETE FROM myfriends 
            WHERE (friend_id1 = ? AND friend_id2 = ?)
            OR (friend_id1 = ? AND friend_id2 = ?)",

    // ==> Update Number of Friends for a Provided Friend ID : 'friends'
    "update-num-of-friends" =>
        "UPDATE friends f
            SET num_of_friends = (
                SELECT COUNT(*)
                FROM myfriends mf
                WHERE mf.friend_id1 = f.friend_id OR mf.friend_id2 = f.friend_id
            )
            WHERE f.friend_id = ?",

    // ==> Select My Friends : 'friends', 'myfriends'
    "select-my-friends" => 
        "SELECT friends.*
         FROM friends
         JOIN myfriends 
         ON friends.friend_id = myfriends.friend_id1 
         OR friends.friend_id = myfriends.friend_id2
         WHERE (myfriends.friend_id1 = ? AND friends.friend_id = myfriends.friend_id2)
            OR (myfriends.friend_id2 = ? AND friends.friend_id = myfriends.friend_id1)
         ORDER BY friends.profile_name",

    // ==> Select My Friends with Pagination : 'friends', 'myfriends'
    "select-my-friends-pagination" => 
        "SELECT friends.*
         FROM friends
         JOIN myfriends 
         ON friends.friend_id = myfriends.friend_id1 
         OR friends.friend_id = myfriends.friend_id2
         WHERE (myfriends.friend_id1 = ? AND friends.friend_id = myfriends.friend_id2)
            OR (myfriends.friend_id2 = ? AND friends.friend_id = myfriends.friend_id1)
         ORDER BY friends.profile_name
         LIMIT ?, ?",

    // ==> Select Not My Friends : 'friends', 'myfriends'
    "select-not-my-friends" =>
        "SELECT *
            FROM friends
            WHERE friend_id NOT IN (
                SELECT friend_id1
                FROM myfriends
                WHERE friend_id2 = ?
                UNION
                SELECT friend_id2
                FROM myfriends
                WHERE friend_id1 = ?
            )
            AND friend_id != ?
            ORDER BY profile_name",

    // ==> Select Not My Friends with Pagination : 'friends', 'myfriends'
    "select-not-my-friends-pagination" =>
        "SELECT *
            FROM friends
            WHERE friend_id NOT IN (
                SELECT friend_id1
                FROM myfriends
                WHERE friend_id2 = ?
                UNION
                SELECT friend_id2
                FROM myfriends
                WHERE friend_id1 = ?
            )
            AND friend_id != ?
            ORDER BY profile_name
            LIMIT ?, ?",

    // ==> Select Mutual Friends : 'friends', 'myfriends'
    "select-mutual-friends" =>
        "SELECT *
            FROM friends
            JOIN myfriends mf1 ON friends.friend_id = mf1.friend_id2
            JOIN myfriends mf2 ON friends.friend_id = mf2.friend_id2
            WHERE mf1.friend_id1 = ? AND mf2.friend_id1 = ?
            ORDER BY friends.profile_name;",

    // ==> Count Mutual Friends : 'friends', 'myfriends'
    "count-mutual-friends" =>
    "SELECT COUNT(*) AS num_mutual_friends
        FROM friends
        JOIN myfriends mf1 ON friends.friend_id = mf1.friend_id2
        JOIN myfriends mf2 ON friends.friend_id = mf2.friend_id2
        WHERE mf1.friend_id1 = ? AND mf2.friend_id1 = ?;",
];
