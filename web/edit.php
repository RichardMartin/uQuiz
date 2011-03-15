<?php require_once('./php/editQuiz.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="description" content="Create or edit a simple quiz webapp for iPhone"/>
	<meta name="keywords" content="create edit simple quiz webapp iPhone"/>

	<meta name="viewport" content="width=device-width,user-scalable=false"/>

	<title>Edit quiz :: <?=$quizTitle?></title>

	<link rel="apple-touch-icon" href="/img/iphone.icon.png"/>
	<link rel="stylesheet" type="text/css" href="/css/main.css"/>
	<link rel="stylesheet" type="text/css" href="/css/edit.css"/>

	<script type="text/javascript" src="/js/jquery-1.5.1.min.js"></script>
	<script type="text/javascript" src="/js/forms.js"></script>
</head>
<body>
	<div class="container">
		<h1>
			Quiz v<?=QUIZ_VERSION?> :: Edit quiz
		</h1>

		<form id="quizEditForm" action="" method="POST">
			<div id="quiz">
				<label for="quizData"><?=$quizTitle?> quiz content</label>
				<textarea id="quizData" name="quizData" class="quizData"><?=htmlEntities($quizData)?></textarea>

				<label for="quizPassword">Update quiz password</label>
				<input type="password" id="quizPassword" name="quizPassword" value=""/>
			</div>

			<div class="buttonContainer">
				<div class="button submit">Save quiz</div>
			</div>
		</form>
	</div>
	<div class="footer">
		<?php
			if ($quizTimestamp) {
		?>
			Quiz last modified on <?=date('Y-m-d H:i:s', $quizDataTimestamp)?>
		<?php
			} else {
		?>
			New quiz!
		<?php
			}
		?>
	</div>
</body>
</html>
