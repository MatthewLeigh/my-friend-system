<?php

// Get SQL queries
include_once 'sql.php';

// Data for 'friends' table.
$friends = [
    ['daenerystargaryen@dragonstone.com', 'drogon1!', 'Mother of Dragons'],     // 1. Daenerys
    ['theongreyjoy@pyke.com', 'password123', 'Theon'],                          // 2. Theon
    ['petyrbaelish@harrowhall.com', 'catelynbaelish', 'Lord Baelish'],          // 3. Petyr
    ['tyrionlannister@casterlyrock.com', 'password', 'The Imp'],                // 4. Tyrion
    ['jaimielannister@casterlyrock.com', 'kingslayer', 'Kingslayer'],           // 5. Jaime
    ['cerseilannister@casterlyrock.com', 'Queen1', 'The Lioness of Lannister'], // 6. Cersei
    ['brienneoftarth@tarth.com', 'oathkeeper', 'Brienne of Tarth'],             // 7. Brienne
    ['jonsnow@winterfell.com', 'aegontargaryen', 'Jon Snow'],                   // 8. Jon
    ['eddardstark@winterfell.com', 'winteriscomming', 'Eddard Stark'],          // 9. Eddard
    ['sansastark@winterfell.com', 'lady123', 'The Lady of Winterfell']          // 10. Sansa
];


// Data for 'myfriends' table.
$friendships = [
    [1, 4],   // Daenerys and Tyrion
    [1, 8],   // Daenerys and Jon
    [1, 10],  // Daenerys and Sansa
    [2, 8],   // Theon and Jon
    [2, 9],   // Theon and Eddard
    [2, 10],  // Theon and Sansa
    [3, 4],   // Petyr and Tyrion
    [3, 6],   // Petyr and Cersei
    [4, 5],   // Tyrion and Jaime
    [4, 6],   // Tyrion and Cersei
    [4, 8],   // Tyrion and Jon
    [4, 10],  // Tyrion and Sansa
    [5, 6],   // Jaime and Cersei
    [5, 7],   // Jaime and Brienne
    [7, 8],   // Brienne and Jon
    [7, 9],   // Brienne and Eddard
    [7, 10],  // Brienne and Sansa
    [8, 9],   // Jon and Eddard
    [8, 10],  // Jon and Sansa
    [9, 10]   // Eddard and Sansa
];


// Get current date.
$date = date('Y-m-d');

?>