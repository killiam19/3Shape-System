<?php
// General function to sanitize input data
function sanitize_input($data) {
    // Trim whitespace
    $data = trim($data);
    // Remove HTML tags
    $data = strip_tags($data);
    // Escape special characters
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_input_length($input, $field_name, $max_length) {
    if (strlen($input) > $max_length) {
        $_SESSION["error"] = "The $field_name must not exceed the $max_length characters.";
        header("Location: ../index.php");
        exit();
    }
}

?>
