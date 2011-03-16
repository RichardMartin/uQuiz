<?php
if ($_REQUEST['phpInfo'] == 'TRUE') {
	phpInfo();
}

define('QUIZ_VERSION', '1.1');
define('FAQ_QUIZ_NAME', 'faq');

$root = $_SERVER['DOCUMENT_ROOT'];

$quizTitle = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['quiz']);
$quizTitle = ($quizTitle) ? $quizTitle : 'Rob';
$quizName = strToLower($quizTitle);

$editMode = preg_match('/\/edit.php$/', $_SERVER['SCRIPT_FILENAME']);

$quizDataFilePath = $root.'/data/'.$quizName.'.quiz.txt';
$faqDataFilePath = $root.'/data/'.FAQ_QUIZ_NAME.'.quiz.txt';
$cssFilePath = $root.'/css/main.css';
$jsFilePath = $root.'/js/quiz.js';

if (file_exists($quizDataFilePath)) {
	$quizDataTimestamp = filemtime($quizDataFilePath);
	$quizTimestamp = max($quizDataTimestamp, filemtime($cssFilePath), filemtime($jsFilePath));
} else if (!$editMode) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/edit/'.$quizTitle);
	exit();
}
?>