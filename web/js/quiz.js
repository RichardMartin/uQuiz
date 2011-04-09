/**
 * Random number generator that allows the seed to be specified.
 * @param seed The random seed, or null to use an undetermined seed.
 */
function Random(seed) {
	var d = new Date();
	this.seed = (seed != null) ? seed : 2345678901 + (d.getSeconds() * 0xFFFFFF) + (d.getMinutes() * 0xFFFF);
	this.A = 48271;
	this.M = 2147483647;
	this.Q = this.M / this.A;
	this.R = this.M % this.A;
	this.oneOverM = 1.0 / this.M;
}

Random.prototype.next = function() {
	var hi = this.seed / this.Q;
	var lo = this.seed % this.Q;
	var test = this.A * lo - this.R * hi;
	if (test > 0) {
		this.seed = test;
	} else {
		this.seed = test + this.M;
	}
	return (this.seed * this.oneOverM);
};

Random.prototype.nextInt = function(min, max) {
	return Math.round(((max - min) * this.next()) + min);
};

/**
 * Represents a quiz.
 * @param data The quiz data.
 */
function Quiz(data) {
	this.data = data;
	this.name = null;
	this.title = null;
	this.timestamp = new Date();
	this.questions = [];
	this.randomSeed = null;

	this.currentQuestionIndex = null;
	this.currentScore = 0;
	this.maxScore = 0;

	this.$questionPanel = null;
	this.$answersPanel = null;
	this.$button = null;
	this.$button2 = null;
	this.$quizTitle = null;
	this.$quizDetailPanel = null;
	this.$currentScore = null;
	this.$currentQuestionNum = null;
	this.$totalQuestions = null;

	this.buttonAction = null;
	this.button2Action = null;
}
Quiz.RANDOM = new Random(null);

Quiz.prototype.parseData = function(data) {
	this.name = data.name;
	this.title = data.title;
	this.timestamp = new Date(parseInt(data.timestamp));
	this.questions = [];

	this.randomiseQuestions = (data.randomiseQuestions == null || data.randomiseQuestions);
	for (var index in data.questions) {
		var answer = new Question(data.questions[index]);
		this.questions.push(answer);
	}
};

Quiz.prototype.clearSavedPosition = function() {
	if ('localStorage' in window && window.localStorage !== null) {
		window.localStorage.clear()
	}
};

Quiz.prototype.savePosition = function() {
	if (this.currentQuestionIndex > 0) {
		if ('localStorage' in window && window.localStorage !== null) {
			var dataStem = 'quiz.' + this.name + '.';
			window.localStorage[dataStem + 'saved'] = 'true';
			window.localStorage[dataStem + 'currentQuestionIndex'] = this.currentQuestionIndex;
			window.localStorage[dataStem + 'randomSeed'] = this.randomSeed;
			window.localStorage[dataStem + 'currentScore'] = this.currentScore;
		}
	}
};

Quiz.prototype.loadPosition = function() {
	if ('localStorage' in window && window.localStorage !== null) {
		var dataStem = 'quiz.' + this.name + '.';
		if (window.localStorage[dataStem + 'saved'] == 'true') {
			this.currentQuestionIndex = parseInt(window.localStorage[dataStem + 'currentQuestionIndex']);
			this.randomSeed = parseFloat(window.localStorage[dataStem + 'randomSeed']);
			Quiz.RANDOM = new Random(this.randomSeed);
			this.currentScore = parseFloat(window.localStorage[dataStem + 'currentScore']);
		}
	}
};

Quiz.prototype.hasSavedPosition = function() {
	if ('localStorage' in window && window.localStorage !== null) {
		var dataStem = 'quiz.' + this.name + '.';
		return (window.localStorage[dataStem + 'saved'] == 'true');
	} else {
		return false;
	}
};

Quiz.prototype.getMaxScore = function() {
	var maxScore = 0;
	for (var index in this.questions) {
		var question = this.questions[index];
		maxScore += question.getMaxScore();
	}
	return maxScore;
};

Quiz.prototype.getCurrentMaxScore = function() {
	var maxScore = 0;
	for (var index = 0; index <= this.currentQuestionIndex; index++) {
		var question = this.questions[index];
		maxScore += question.getMaxScore();
	}
	return maxScore;
};

Quiz.prototype.updateScore = function(scoreChange) {
	this.currentScore += (scoreChange) ? scoreChange : 0;

	var currentMaxScore = Math.max(this.getCurrentMaxScore(), 1);
	var currentScorePercent = parseInt(100 * this.currentScore / currentMaxScore);
	this.$currentScore.text(currentScorePercent);
};

Quiz.prototype.init = function($questionPanel, $answersPanel, $button, $button2, $quizTitle,
                               $quizDetailPanel, $currentScore, $currentQuestionNum, $totalQuestions) {
	// Set up the interface
	this.$questionPanel = $questionPanel;
	this.$answersPanel = $answersPanel;
	this.$quizDetailPanel = $quizDetailPanel;
	this.$currentScore = $currentScore;
	this.$currentQuestionNum = $currentQuestionNum;
	this.$totalQuestions = $totalQuestions;

	this.$button = $('<div/>');
	this.$button.addClass('button');
	$button.append(this.$button);
	$button.addClass('buttonContainer');

	this.$button2 = $('<div/>');
	this.$button2.addClass('button');
	$button2.addClass('buttonContainer').hide();
	$button2.append(this.$button2);

	this.$quizTitle = $quizTitle;

	var self = this;
	this.$button.click(function() {
		$(this).removeClass('pressed');
		if (self.buttonAction) {
			self.buttonAction();
		}
	});
	this.$button2.click(function() {
		$(this).removeClass('pressed');
		if (self.button2Action) {
			self.button2Action();
		}
	});

	$('.button').bind('touchstart', function() {
		$(this).addClass('pressed');
	});
	$('.button').bind('touchend', function() {
		$(this).removeClass('pressed');
	});
};

Quiz.prototype.start = function() {
	this.$questionPanel.empty().hide();
	this.$answersPanel.empty().hide();

	this.setUpQuestions();
	if (this.title != null) {
		this.$quizTitle.text(this.title);
	}

	this.currentQuestionIndex = null;
	this.currentScore = 0;
	this.maxScore = this.getMaxScore();
	this.$quizDetailPanel.hide();

	this.updateScore();
	this.$totalQuestions.text(this.questions.length);

	// Start the quiz
	$('body').addClass('start');
	this.$button.text('Start quiz');

	if (this.hasSavedPosition()) {
		var dataStem = 'quiz.' + this.name + '.';
		var savedQuestionNumber = parseInt(window.localStorage[dataStem + 'currentQuestionIndex']) + 1;
		this.$button2.text('Resume quiz (Q' + savedQuestionNumber + ')').parent().show();
	}

	var self = this;
	var startQuiz = function() {
		self.$questionPanel.addClass('panel').show();
		self.$answersPanel.addClass('panel options').show();
		self.$quizDetailPanel.show();
		self.$button2.parent().hide();

		$('body').removeClass('start');

		self.nextQuestion();
	};
	this.buttonAction = function() {
		self.clearSavedPosition();

		startQuiz();
	};

	this.button2Action = function() {
		// Load the saved quiz data and start the quiz
		self.loadPosition();
		self.setUpQuestions();

		self.currentQuestionIndex--;
		self.updateScore(0);

		startQuiz();
	};
};

Quiz.prototype.setUpQuestions = function() {
	this.parseData(this.data);

	this.randomSeed = Quiz.RANDOM.seed;
	if (this.randomiseQuestions) {
		var randomisedQuestions = [];
		while (this.questions.length > 0) {
			var questionIndex = Quiz.RANDOM.nextInt(0, this.questions.length - 1);
			randomisedQuestions.push(this.questions[questionIndex]);
			this.questions.splice(questionIndex, 1);
		}
		this.questions = randomisedQuestions;
	}
};

Quiz.prototype.nextQuestion = function() {
	this.currentQuestionIndex = (this.currentQuestionIndex == null) ? 0 : this.currentQuestionIndex + 1;
	var question = this.questions[this.currentQuestionIndex];
	this.$currentQuestionNum.text(this.currentQuestionIndex + 1);
	this.$questionPanel.text(question.text);
	this.$questionPanel.html(this.$questionPanel.html().replace(/\n/g, '<div class="break"/>'));

	var answers = question.getAnswers();
	var self = this;
	if (answers.length > 1) {
		// Question is multiple choice
		this.$button.text('Accept answer');
		this.buttonAction = function() {
			self.acceptAnswer();
		};
	} else {
		// Question is free-form rather than multiple choice
		this.$answersPanel.hide();
		this.$button.text('Check answer');
		this.buttonAction = function() {
			self.checkAnswer();
		};
	}

	this.$answersPanel.empty();
	for (var index in answers) {
		var answer = answers[index];
		var $answerPanel = $('<div/>');
		$answerPanel.addClass('option');
		this.$answersPanel.append($answerPanel);

		answer.init(question, $answerPanel);
	}

	this.savePosition();
};

Quiz.prototype.acceptAnswer = function() {
	// Automatically check the result of a multiple choice question
	var question = this.questions[this.currentQuestionIndex];
	if (question.selectedAnswer == null) {
		alert('You need to select an answer first!');
	} else {
		// Update the score and reveal the answer
		this.updateScore(question.selectedAnswer.score);

		question.revealAnswer();
		this.finishAnswer();
	}
};

Quiz.prototype.checkAnswer = function() {
	// Reveal the answer to a free-form question, and let the user self-mark
	this.$answersPanel.show();

	var question = this.questions[this.currentQuestionIndex];
	question.selectedAnswer = question.answers[0];

	// Update the score
	var self = this;
	this.$button.text('I was correct');
	this.buttonAction = function() {
		question.selectAnswer(question.selectedAnswer);
		question.selectedAnswer.reveal(true, false, true);
		self.updateScore(question.selectedAnswer.score);
		self.finishAnswer();
	};
	this.$button2.text('I was wrong').parent().show();
	this.button2Action = function() {
		question.selectAnswer(question.selectedAnswer);
		question.selectedAnswer.reveal(true, false, false);
		self.finishAnswer();
	};
};

Quiz.prototype.finishAnswer = function() {
	this.$button2.parent().hide();

	// User can now move on to the next question, if there is one
	var self = this;
	if (this.currentQuestionIndex + 1 < this.questions.length) {
		this.$button.text('Next question');
		this.buttonAction = function() {
			self.nextQuestion();
		};
	} else {
		this.$button.text('Finish quiz');
		this.buttonAction = function() {
			self.finishQuiz();
		};
	}
};

Quiz.prototype.finishQuiz = function() {
	var resultText = 'Your final score is ' + this.currentScore;
	if (this.currentScore < this.maxScore) {
		resultText += ', out of a possible ' + this.maxScore;
		resultText += '\nThat\'s ' + parseInt(100 * this.currentScore / this.maxScore) + '%.';
	} else {
		resultText += '.\nCongratulations, you got all of the questions right!';
	}
	this.$questionPanel.text(resultText);
	this.$questionPanel.html(this.$questionPanel.html().replace(/\n/g, '<div class="break"/>'));
	this.$answersPanel.empty().hide();

	this.$button.text('All done!');

	var self = this;
	this.buttonAction = function() {
		self.start();
	};
};

/**
 * Represents a question in the quiz.
 * @param data The question data.
 */
function Question(data) {
	this.text = '';
	this.answers = [];
	this.randomiseAnswers = true;

	this.selectedAnswer = null;

	this.parseData(data);
}

Question.prototype.parseData = function(data) {
	this.text = data.text;
	this.randomiseAnswers = (data.randomiseAnswers == null || data.randomiseAnswers);
	for (var index in data.answers) {
		var answer = new Answer(data.answers[index]);
		this.answers.push(answer);
	}
};

Question.prototype.getAnswers = function() {
	// Randomise the answers
	if (this.randomiseAnswers) {
		var randomisedAnswers = [];
		while (this.answers.length > 0) {
			var answerIndex = Quiz.RANDOM.nextInt(0, this.answers.length - 1);
			randomisedAnswers.push(this.answers[answerIndex]);
			this.answers.splice(answerIndex, 1);
		}
		this.answers = randomisedAnswers;
	}
	return this.answers;
};

Question.prototype.selectAnswer = function(selectedAnswer) {
	this.selectedAnswer = selectedAnswer;
	for (var index in this.answers) {
		var answer = this.answers[index];
		var isSelected = (answer == this.selectedAnswer);
		answer.$answerPanel.toggleClass('active', isSelected);
	}
};

Question.prototype.revealAnswer = function() {
	for (var index in this.answers) {
		var answer = this.answers[index];
		var isSelected = (answer == this.selectedAnswer);
		var isMultiChoice = (this.answers.length > 1);
		answer.reveal(isSelected, isMultiChoice);
	}
};

Question.prototype.getMaxScore = function() {
	var maxScore = null;
	for (var index in this.answers) {
		var answer = this.answers[index];
		maxScore = (maxScore == null || maxScore < answer.score) ? answer.score : maxScore;
	}
	return maxScore;
};

/**
 * Represent an answer to a question in the quiz.
 * @param data The answer data.
 */
function Answer(data) {
	this.text = '';
	this.score = 0;
	this.comment = '';

	this.revealed = false;

	this.$answerPanel = null;

	this.parseData(data);
}

Answer.prototype.parseData = function(data) {
	this.text = data.text;
	this.score = parseInt(data.score);
	this.comment = data.comment ? data.comment : null;
};

Answer.prototype.init = function(question, $answerPanel) {
	this.$answerPanel = $answerPanel;
	this.$answerPanel.text(this.text);
	this.$answerPanel.html(this.$answerPanel.html().replace(/\n/g, '<div class="break"/>'));

	var self = this;
	this.$answerPanel.click(function() {
		if (!self.revealed) {
			question.selectAnswer(self);
		}
	});
};

Answer.prototype.reveal = function(isSelected, isMultiChoice, isCorrect) {
	this.revealed = true;

	if (isSelected) {
		isCorrect = (isCorrect == null) ? (this.score > 0) : isCorrect;
		this.$answerPanel.toggleClass('correct', isCorrect);
		this.$answerPanel.toggleClass('incorrect', !isCorrect);

		var comment;
		if (isMultiChoice) {
			comment = (isCorrect) ? 'Correct' : 'Wrong';
			comment += (this.comment) ? ' - ' + this.comment : '!';
		} else {
			comment = (this.comment) ? this.comment : '';
		}

		var $commentPanel = $('<div/>');
		$commentPanel.addClass('comment');
		$commentPanel.text(comment);
		$commentPanel.html($commentPanel.html().replace(/\n/g, '<div class="break"/>'));

		this.$answerPanel.empty();
		this.$answerPanel.text(this.text);
		this.$answerPanel.html(this.$answerPanel.html().replace(/\n/g, '<div class="break"/>'));
		this.$answerPanel.append($commentPanel);
	} else if (this.score > 0) {
		this.$answerPanel.addClass('correct');
	}
};

/**
 * Quiz startup.
 */
$(function() {
	window.quiz = new Quiz(window.quizData);
	quiz.init($('#question'), $('#answers'), $('#button'), $('#button2'), $('#quizTitle'), $('#quizDetail'), $('#currentScore'), $('#currentQuestion'), $('#totalQuestions'));
	quiz.start();
});

/**
 * Manifest handling.
 */
var cache = window['applicationCache'];
cache.addEventListener('error', function(error) {
	console.log('Manifest cache error', error);
}, false);
