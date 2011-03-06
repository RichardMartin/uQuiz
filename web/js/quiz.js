/**
 * Represents a quiz.
 * @param data The quiz data.
 */
function Quiz(data) {
	this.timestamp = new Date();
	this.questions = [];

	this.currentQuestionIndex = null;

	this.$questionPanel = null;
	this.$answersPanel = null;
	this.$button = null;

	this.buttonAction = null;

	this.parseData(data);
}

Quiz.prototype.parseData = function(data) {
	this.timestamp = new Date(parseInt(data.timestamp));
	this.randomiseQuestions = (data.randomiseQuestions == null || data.randomiseQuestions);
	for (var index in data.questions) {
		var answer = new Question(data.questions[index]);
		this.questions.push(answer);
	}
};

Quiz.prototype.init = function($questionPanel, $answersPanel, $button) {
	// Set up the interface
	this.$questionPanel = $questionPanel;
	this.$answersPanel = $answersPanel;
	this.$button = $button;

	this.$button.addClass('button');

	var self = this;
	this.$button.click(function() {
		if (self.buttonAction) {
			self.buttonAction();
		}
	});
};

Quiz.prototype.start = function() {
	// Randomise the quiz
	if (this.randomiseQuestions) {
		var randomisedQuestions = [];
		while (this.questions.length > 0) {
			var questionIndex = Math.floor(Math.random() * this.questions.length);
			randomisedQuestions.push(this.questions[questionIndex]);
			this.questions.splice(questionIndex, 1);
		}
		this.questions = randomisedQuestions;
	}

	// Start the quiz
	this.$button.text('Start quiz');

	var self = this;
	self.buttonAction = function() {
		self.$questionPanel.addClass('panel');
		self.$answersPanel.addClass('panel options');

		self.nextQuestion();
	};
};

Quiz.prototype.nextQuestion = function() {
	this.currentQuestionIndex = (this.currentQuestionIndex == null) ? 0 : this.currentQuestionIndex + 1;
	var question = this.questions[this.currentQuestionIndex];
	this.$questionPanel.text(question.text);

	this.$answersPanel.empty();
	var answers = question.getAnswers();
	for (var index in answers) {
		var answer = answers[index];
		var $answerPanel = $('<div/>');
		$answerPanel.addClass('option');
		this.$answersPanel.append($answerPanel);

		answer.init(question, $answerPanel);
	}

	this.$button.text('Accept answer');
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
			var answerIndex = Math.floor(Math.random() * this.answers.length);
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
		var isSelected = (answer == selectedAnswer);
		answer.$answerPanel.toggleClass('active', isSelected);
	}
};

/**
 * Represent an answer to a question in the quiz.
 * @param data The answer data.
 */
function Answer(data) {
	this.text = '';
	this.score = 0;
	this.comment = '';

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

	var self = this;
	this.$answerPanel.click(function() {
		question.selectAnswer(self);
	});
};

/**
 * Quiz startup.
 */
$(function() {
	window.quiz = new Quiz(window.quizData);
	quiz.init($('#question'), $('#answers'), $('#button'));
	quiz.start();
});

/**
 * Manifest handling.
 */
var cache = window['applicationCache'];
cache.addEventListener('error', function(error) {
	console.log('Manifest cache error', error);
}, false);
