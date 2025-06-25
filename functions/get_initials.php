<?php
function get_initials($profile_name) {
    $initials = '';
    $name_parts = explode(' ', $profile_name);
    $count = 0;
    foreach ($name_parts as $part) {
        if ($count >= 3) break;
        $initials .= $part[0];
        $count++;
    }
    return $initials;
}
?>
