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
</head>
<body>
	<div class="container">
		<h1>
			Quiz v<?=QUIZ_VERSION?> :: Edit quiz
		</h1>

		<div id="quiz">
			<form id="quizEditForm" action="" method="POST">
				<label for="quizData"><?=$quizTitle?> quiz content</label>
				<textarea id="quizData" name="quizData"><?=htmlEntities($quizData)?></textarea>
			</form>
		</div>

		<div class="buttonContainer">
			<div class="button">Save quiz</div>
		</div>
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
