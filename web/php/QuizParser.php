<?php
define('STATE_QUIZ', 1);
define('STATE_QUESTION', 2);
define('STATE_ANSWER', 3);

/**
 * Parses a plain-text quiz file ready for output as a JSON object.
 */
class QuizParser {
	var $debug = false;

	var $quizData = array();
	var $answerKey = array();

	var $logs = array();
	var $warnings = array();
	var $errors = array();

	var $passHash = null;

	var $currentLine = 0;
	var $currentState = STATE_QUIZ;
	var $currentQuestion = null;
	var $currentQuestionNumber = 0;
	var $currentAnswer = null;
	var $currentAnswerNumber = 0;

	function parse($quizFileName) {
		$quizFile = fOpen($quizFileName, 'r');
		if ($quizFile) {
			$this->parseStart();
			while (($quizLine = fGets($quizFile, 4096)) !== false) {
				$this->parseLine($quizLine);
			}
			$this->parseEnd();
			fClose($quizFile);
		}
	}

	function parseText($quizText) {
		$quizLines = explode("\n", $quizText);
		$this->parseStart();
		foreach ($quizLines as $quizLine) {
			$this->parseLine($quizLine);
		}
		$this->parseEnd();
	}

	function parseStart() {
		$this->quizData['questions'] = array();
		$this->currentQuestionNumber = 0;
	}

	function parseEnd() {
		// Finish the final question
		$this->addCurrentAnswer();
		$this->addCurrentQuestion();

		if (count($this->quizData['questions']) == 0) {
			$this->error('There are no questions in this quiz.');
		}
	}

	function parseLine($quizLine) {
		$this->currentLine++;
		$quizLine = trim($quizLine);
		$matches = array();

		if (preg_match('/^NoRandomOrder$/i', $quizLine)) {
			$this->setRandomOrder(false);
		} else if (preg_match('/^PassHash:\\s*(.*)/i', $quizLine, $matches)) {
			$this->setPassHash($matches[1]);
		} else if (preg_match('/^AnswerKey:\\s*(.*)/i', $quizLine, $matches)) {
			$this->setAnswerKey($matches[1]);
		} else if (preg_match('/^Q\\s*:(.*)/i', $quizLine, $matches)) {
			$this->addQuestion();
			$this->addContent($matches[1]);
		} else if (preg_match('/^A([^:a-z]*):(.*)/i', $quizLine, $matches)) {
			$this->addAnswer($matches[1]);
			$this->addContent($matches[2]);
		} else {
			$this->addContent($quizLine);
		}
	}

	function setRandomOrder($value) {
		if ($this->currentState == STATE_QUIZ) {
			$this->quizData['randomiseQuestions'] = $value;
		} else if ($this->currentState == STATE_QUESTION) {
			$this->currentQuestion['randomiseAnswers'] = $value;
		} else {
			$message = 'Random ordering is a property of quizzes and questions, not individual answers.';
			if (is_array($this->currentQuestion)) {
				$message .= '  Property will be attached to the current question instead.';
				$this->currentQuestion['randomiseAnswers'] = $value;
			} else {
				$message .= '  Property will be attached to the quiz instead.';
				$this->quizData['randomiseQuestions'] = $value;
			}
			$this->warn($message);
		}
	}

	function setAnswerKey($answerKey) {
		if ($this->currentState == STATE_QUIZ) {
			$matches = array();
			if (preg_match_all('/[^a-z]*([a-z]+)/i', $answerKey, $matches)) {
				foreach ($matches[1] as $answerNumber) {
					if (is_numeric($answerNumber)) {
						$this->answerKey[] = (int) $answerNumber;
					} else {
						$this->answerKey[] = ord(strToLower($answerNumber)) - ord('a');
					}
				}
			}
		} else if ($this->currentState == STATE_QUESTION) {
			$this->error('An answer-key is a property of quizzes, not individual questions (at the moment).');
		} else if ($this->currentState == STATE_QUESTION) {
			$this->error('An answer-key is a property of quizzes, not individual answers.');
		}
	}

	function setPassHash($passHash) {
		$passHash = trim($passHash);
		$this->passHash = (strLen($passHash) == 0) ? null : $passHash;
	}

	function createPassHash($password) {
		return ($password == null || strLen($password) == 0) ? null : strToLower(md5($password));
	}

	function checkPassword($password) {
		return ($this->passHash == $this->createPassHash($password));
	}

	function checkCookiePassword($password) {
		return ($this->createPassHash($this->passHash) == $password);
	}

	function isPasswordProtected() {
		return ($this->passHash != null);
	}

	function addQuestion() {
		// Add the previous question to the quiz
		if ($this->currentState != STATE_QUIZ) {
			$this->addCurrentAnswer();
			$this->addCurrentQuestion();
		}

		// Start a new question
		$this->currentState = STATE_QUESTION;
		$this->currentQuestion = array('text' => '', 'answers' => array());
		$this->currentQuestionNumber++;
		$this->currentAnswerNumber = 0;
	}

	function addCurrentQuestion() {
		// Check that the question is valid
		if (is_array($this->currentQuestion)) {
			if (strLen(trim($this->currentQuestion['text'])) == 0) {
				$this->error('Question had no text, and will not be included in the quiz.');
			} else if (count($this->currentQuestion['answers']) == 0) {
				$this->error('Question did not have any answers, and will not be included in the quiz.');
			} else {
				$answerCount = count($this->currentQuestion['answers']);

				// See if there is a correct answer marked in the answer key
				$correctAnswerFromKey = $this->answerKey[$this->currentQuestionNumber - 1];
				if (is_numeric($correctAnswerFromKey)) {
					$correctAnswer = &$this->currentQuestion['answers'][$correctAnswerFromKey];
					if (is_array($correctAnswer)){// && !$correctAnswer['score']) {
						$correctAnswer['score'] = 1;
					}
				}

				// Check that there is at least one correct answer
				$hasCorrectAnswer = false;
				foreach ($this->currentQuestion['answers'] as $answer) {
					if ($answer['score'] > 0) {
						$hasCorrectAnswer = true;
						break;
					}
				}
				if (!$hasCorrectAnswer) {
					if ($answerCount > 1) {
						$this->warn('Question has no answer marked as correct.');
					} else {
						$this->currentQuestion['answers'][0]['score'] = 1;
					}
				}

				// Add the question to the quiz
				$this->quizData['questions'][] = $this->currentQuestion;
				$questionType = ($answerCount == 1) ? 'free-form' : 'multiple choice';
				$this->log('Question ('.$questionType.') added okay.');
			}
		} else {
			$this->warn('No question found.');
		}

		// Reset the question data
		$this->currentState = STATE_QUIZ;
		$this->currentQuestion = null;
	}

	function addAnswer($scoreText) {
		// Add the previous answer to the quiz
		$this->addCurrentAnswer();

		// Parse the score text
		$answerScore = 0;
		$scoreText = trim($scoreText);
		$matches = array();
		if (preg_match('/(-?[0-9]+)/', $scoreText, $matches)) {
			$answerScore = (int) $matches[1];
		} else if (strLen($scoreText) > 0) {
			// Accept any kind of mark as a correct-answer indicator
			$answerScore = 1;
		}

		// Start a new answer
		$this->currentState = STATE_ANSWER;
		$this->currentAnswer = array('text' => '', 'score' => $answerScore);
		$this->currentAnswerNumber++;
	}

	function addCurrentAnswer() {
		// Check that the question is valid
		if (is_array($this->currentAnswer) && ($this->currentState == STATE_QUIZ || !is_array($this->currentQuestion))) {
			$this->error('Answer was not attached to a question, and will not be included in the quiz.');
		} else if (is_array($this->currentAnswer)) {
			if (strLen(trim($this->currentAnswer['text'])) == 0) {
				$this->error('Answer had no text, and will not be included in the quiz.');
			} else {
				// Make sure the answer has a score
				$answerScore = $this->currentAnswer['score'];
				if (!is_numeric($answerScore)) {
					$this->warn('Answer had an invalid score ('.$answerScore.'), and will be set to zero.');
					$this->currentAnswer['score'] = 0;
				}

				// Add the answer to the question
				$this->currentQuestion['answers'][] = $this->currentAnswer;
				$this->log('Answer (score '.$this->currentAnswer['score'].') added okay.');
			}
		}

		// Reset the answer data
		$this->currentState = ($this->currentState == STATE_ANSWER) ? STATE_QUESTION : $this->currentState;
		$this->currentAnswer = null;
	}

	function addContent($text) {
		$text = trim($text);

		if ($this->currentState == STATE_QUIZ) {
			if (strLen($text) > 0) {
				$this->warn('Text is not part of a question or an answer, and will be ignored: '.$text);
			}
		} else if (strLen($text) > 0) {
			if ($this->currentState == STATE_QUESTION) {
				// Add the question text
				if ($this->currentQuestion['text'] == null) {
					$this->currentQuestion['text'] = $text;
				} else {
					$this->currentQuestion['text'] .= "\n".$text;
				}
			} else if ($this->currentState == STATE_ANSWER) {
				if ($this->currentAnswer['comment'] == null) {
					// Separate any comment from the text
					$matches = array();
					$comment = null;
					if (preg_match('/(.*)\/\/(.*)/', $text, $matches)) {
						$text = trim($matches[1]);
						$comment = trim($matches[2]);
					}

					// Add the answer text
					if (strLen($text) > 0) {
						if ($this->currentAnswer['text'] == null) {
							$this->currentAnswer['text'] = $text;
						} else {
							$this->currentAnswer['text'] .= "\n".$text;
						}
					}
				} else {
					$comment = preg_replace('/^\/\/\\s*/', '', $text);
				}

				if ($comment != null) {
					// Add the comment text
					if ($this->currentAnswer['comment'] == null) {
						$this->currentAnswer['comment'] = $comment;
					} else {
						$this->currentAnswer['comment'] .= "\n".$comment;
					}
				}
			}
		}
	}

	function buildJSON($quizName, $quizTimestamp = null, $showLogs = false, $showErrors = true) {
		$quizTimestamp = is_numeric($quizTimestamp) ? $quizTimestamp : time();
		$jsonData = array('name' => $quizName, 'timestamp' => $quizTimestamp);
		$jsonData['randomiseQuestions'] = $this->quizData['randomiseQuestions'];
		$jsonData['questions'] = $this->quizData['questions'];

		if ($showLogs || $showErrors) {
			$logs = array();
			if ($showLogs) {
				$logs['info'] = $this->logs;
			}
			if ($showErrors) {
				$logs['warnings'] = $this->warnings;
				$logs['errors'] = $this->errors;
			}
			$jsonData['logs'] = $logs;
		}
		return json_encode($jsonData);
	}

	function log($message) {
		$this->logs[] = array('lineNumber' => $this->currentLine, 'text' => $message);
		if ($this->debug) {
			print('INFO @'.$this->currentLine.' - '.$message."\n");
		}
	}

	function warn($message) {
		$this->warnings[] = array('lineNumber' => $this->currentLine, 'text' => $message);
		if ($this->debug) {
			print('WARN @'.$this->currentLine.' - '.$message."\n");
		}
	}

	function error($message) {
		$this->errors[] = array('lineNumber' => $this->currentLine, 'text' => $message);
		if ($this->debug) {
			print('ERROR @'.$this->currentLine.' - '.$message."\n");
		}
	}
}
?>
