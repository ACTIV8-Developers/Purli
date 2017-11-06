<?php
parse_str(file_get_contents("php://input"), $post_vars);
if (isset($post_vars['foo'])) {
    echo 1;
}