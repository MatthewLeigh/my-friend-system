<?php
    function sanitize($data) {
        return htmlspecialchars(stripcslashes(trim($data)));
    };
?>