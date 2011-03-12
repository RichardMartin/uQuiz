<?php
define('QUIZ_VERSION', '1.1');

$root = $_SERVER['DOCUMENT_ROOT'];

$quizTitle = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['quiz']);
$quizTitle = ($quizTitle) ? $quizTitle : 'Rob';
$quizName = strToLower($quizTitle);

$quizDataFilePath = $root.'/data/'.$quizName.'.quiz.txt';
$cssFilePath = $root.'/css/main.css';
$jsFilePath = $root.'/js/quiz.js';

if (file_exists($quizDataFilePath)) {
	$quizTimestamp = max(filemtime($quizDataFilePath), filemtime($cssFilePath), filemtime($jsFilePath));
} else {
	exit();
}
?>
