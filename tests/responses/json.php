<?php
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

header('Content-type: application/json');
echo json_encode(array('foo' => $input['foo']));