<?php
require_once('../php/setup.php');
require_once('../php/QuizParser.php');

header('Content-Type: application/javascript');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $quizTimestamp).' GMT');

// Parse the plain text quiz file into JSON
$debug = ($_REQUEST['debug'] == 'true');
$pureJSON = ($_REQUEST['pure'] == 'true');

$quizParser = new QuizParser();
$quizParser->debug = $debug;
$quizParser->parse($quizDataFilePath);
?>
<?=($pureJSON) ? '' : 'window.quizData = '?><?=$quizParser->buildJSON($quizName, $quizTimestamp)?><?=($pureJSON) ? '' : ';'?>
