<?php require_once('./php/setup.php'); ?>
<!DOCTYPE html>
<html lang="en" manifest="/quiz.<?=$quizName?>.manifest">
<head>
	<meta name="description" content="Simple quiz webapp for iPhone"/>
	<meta name="keywords" content="simple quiz webapp iPhone"/>

	<meta name="apple-mobile-web-app-capable" content="yes"/>
	<meta name="viewport" content="width=device-width,user-scalable=false"/>

	<title>Quiz v<?=QUIZ_VERSION?> :: <?=$quizTitle?></title>

	<link rel="apple-touch-icon" href="/img/iphone.icon.png"/>
	<link rel="stylesheet" type="text/css" href="/css/main.css"/>

	<script type="text/javascript" src="/js/jquery-1.5.1.min.js"></script>
	<script type="text/javascript" src="/js/quiz.js"></script>
	<script type="text/javascript" src="/js/quizDB.<?=$quizName?>.js"></script>
</head>
<body>
	<div class="container">
		<h1>
			<span id="questionNum">Q: <span id="currentQuestion">0</span> / <span id="totalQuestions">0</span></span>
			<span id="score">Score: <span id="currentScore">0</span>%</span>
		</h1>

		<div id="question"></div>
		<div id="answers"></div>

		<div id="button"></div>
		<div id="button2"></div>
	</div>
	<div class="footer">Quiz updated on <?=date('Y-m-d H:i:s', $quizTimestamp)?></div>
</body>
</html>
