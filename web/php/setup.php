<?php
define('QUIZ_VERSION', '1.1');

$root = $_SERVER['DOCUMENT_ROOT'];

$quizTitle = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['quiz']);
$quizName = strToLower($quizTitle);

$quizDataFilePath = $root.'/data/'.$quizName.'.quiz.txt';
$quizTimestamp = filemtime($quizDataFilePath);
?>
