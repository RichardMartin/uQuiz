<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/php/setup.php');

if (is_readable($quizDataFilePath)) {
	$quizData = file_get_contents($quizDataFilePath);
} else {
	$quizData = 'New Quiz!';
}
?>