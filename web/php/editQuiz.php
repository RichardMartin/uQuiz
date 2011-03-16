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
			setCookie('passwordCookie', $cookiePassHash, null, '/', $_SERVER['HTTP_HOST'], false, true);
		} else {
			$cookiePassword = $_COOKIE['passwordCookie'];
			if (!$quizParser->checkCookiePassword($cookiePassword)) {
				require($root.'/password.php');
				exit();
			}
		}
	}
} else if (is_readable($faqDataFilePath)) {
	$quizData = file_get_contents($faqDataFilePath);
} else {
	$quizData = 'Q: Shouldn\'t there be a helpful FAQ here?'."\n".
	            'NoRandomOrder'."\n".
	            'A:  What, no!  Everything is working fine...'."\n".
	            'A*: Erm, okay, maybe something has gone wrong.  // You might want to try again later...'."\n";
}

// Update the quiz text, if valid
$newQuizData = trim(stripSlashes($_REQUEST['quizData']));
$newPassword = $_REQUEST['quizPassword'];
if (strLen($newQuizData) > 0) {
	$quizData = $newQuizData;

	// Update the password hash on the quiz text
	$quizData = preg_replace('/^\\s*PassHash:.*/mi', '', $quizData);

	// TODO: Password confirmation
	$newPassHash = $quizParser->passHash;
	if (strLen($newPassword)) {
		$newPassHash = $quizParser->createPassHash($newPassword);
		$newCookiePassHash = $quizParser->createPassHash($newPassHash);
		setCookie('passwordCookie', $newCookiePassHash, null, '/', $_SERVER['HTTP_HOST'], false, true);
	}
	$quizData = 'PassHash: '.$newPassHash."\n".$quizData;

	$quizParser->parseText($quizData);
	if (count($quizParser->errors) == 0) {
		// Write the new quiz data
		file_put_contents($quizDataFilePath, $quizData);
	}
}

// Remove password hash from the quiz text
$quizData = preg_replace('/^\\s*PassHash:.*/mi', '', $quizData);
?>