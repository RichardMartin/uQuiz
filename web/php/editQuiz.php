<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/php/setup.php');
require_once($root.'/php/QuizParser.php');

$quizParser = new QuizParser();

if (is_readable($quizDataFilePath)) {
	$quizData = file_get_contents($quizDataFilePath);
	$quizParser->parseText($quizData);

	// Enforce password-protection of quiz edits
	if ($quizParser->isPasswordProtected()) {
		$password = $_REQUEST['password'];
		if ($quizParser->checkPassword($password)) {
			$cookiePassHash = $quizParser->createPassHash($quizParser->createPassHash($password));
			setCookie('password', $cookiePassHash, null, '/', $_SERVER['HTTP_HOST'], false, true);
		} else {
			$cookiePassword = $_COOKIE['password'];
			if (!$quizParser->checkCookiePassword($cookiePassword)) {
				require($root.'/password.php');
				exit();
			}
		}
	}

	// Update the quiz text, if valid
	$newQuizData = trim(stripSlashes($_REQUEST['quizData']));
	$newPassword = $_REQUEST['quizPassword'];
	if (strLen($newQuizData) > 0) {
		$quizData = $newQuizData;

		// Update the password hash on the quiz text
		$quizData = preg_replace('/^\\s*PassHash:.*/mi', '', $quizData);

		// TODO: Password confirmation
		$newPassHash = strLen($newPassword) ? $quizParser->createPassHash($newPassword) : $quizParser->passHash;
		$quizData = 'PassHash: '.$newPassHash."\n".$quizData;

		$quizParser->parseText($quizData);
		if (count($quizParser->errors) == 0) {
			// Write the new quiz data
			file_put_contents($quizDataFilePath, $quizData);
		}
	}

	// Remove password hash from the quiz text
	$quizData = preg_replace('/^\\s*PassHash:.*/mi', '', $quizData);
} else {
	$quizData = 'New Quiz!';
}
?>