<?php
require('../php/setup.php');

header('Content-Type: application/javascript');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $quizTimestamp).' GMT');

// TODO: Parse plain text file into JSON
?>
window.quizData = {
	'name': '<?=$quizName?>',
	'timestamp': <?=$quizTimestamp?>,
<?php
@readfile($quizDataFilePath);
?>
};
