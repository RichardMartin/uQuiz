<?php
require('setup.php');

header('Content-Type: text/cache-manifest');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $quizTimestamp).' GMT');
?>
CACHE MANIFEST

# Manifest for Quiz : '<?=$quizTitle?>'
# v<?=QUIZ_VERSION?>.<?=$quizTimestamp?>.0

# HTML
index.php

# Javascript
js/jquery-1.5.1.min.js
js/quiz.js
js/quizDB.<?=$quizName?>.js

# CSS
css/main.css

# Images
img/iphone.icon.png
