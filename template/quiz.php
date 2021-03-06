<?php

/**
 * Copyright (c) 2013 Claudiu Persoiu (http://www.claudiupersoiu.ro/)
 *
 * This file is part of "Just quizzing".
 *
 * Official project page: http://blog.claudiupersoiu.ro/just-quizzing/
 *
 * You can download the latest version from https://github.com/claudiu-persoiu/Just-Quizzing
 *
 * "Just quizzing" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * Just quizzing is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

?>

<div id="pager-left" class="pager">
    <img src="images/leftArrow.png" onclick="displayPreviousQuestion(); this.blur();" />
</div>

<div id="pager-right" class="pager">
    <img src="images/rightArrow.png" onclick="displayNextQuestion(); this.blur();" />
</div>

<div id="header-addition">
    <div id="questions"></div>
    <div id="timer">0:00:00</div>
    <div id="category-name"></div>

</div>

<div id="question"></div>
<div id="answers"></div>

<div id="controls">
    <button type="submit" id="check" onclick="checkAnswers();this.blur();" onmouseup="this.blur();">check</button>
    <button type="button" id="next" onclick="displayNextQuestion(); this.blur();">continue</button>
    <button type="button" id="skip" onclick="skippQuestion();">skip</button>
</div>

<div id="results-stats">
    <span id="good-result-no">ok</span>
    <span id="good-result">&nbsp;</span>
    <span id="bad-result">&nbsp;</span>
    <span id="bad-result-no">bad</span>
</div>
<div id="final-results">
    <div class="overlay"></div>
    <div class="font-larger overlay-container">
        <div id="container">
            <div class="font-larger" id='results'>Results</div>
            <div>Correct: <span id="good-final-result"></span></div>
            <div>Wrong: <span id="bad-final-result"></span></div>
            <div>Skipped: <span id="skipped-final-result"></span></div>
            <div id="results-timer">Time: <span id="timer-result"></span></div>
            <div>
                <button onclick="startQuiz(categoryId, categoryName);" id="restart">Restart</button>
            </div>
        </div>
    </div>
</div>

<div id="qr-container" style="display: none;" onclick="this.style.display='none';">
    <div class="overlay"></div>
    <div class="overlay-container">
        <img id="qr-img" src="">
        <div>
            <a href="https://play.google.com/store/apps/details?id=ro.claudiupersoiu.just.quizzing" target="_blank">
                <img src="images/google_play.png" />
            </a>
        </div>
    </div>
</div>

<script type="text/javascript">

// Array shuffle implementation
Array.prototype.shuffle = function () {
    var i = this.length, j, tempi, tempj;
    if (i == 0) return false;
    while (--i) {
        j = Math.floor(Math.random() * ( i + 1 ));
        tempi = this[i];
        tempj = this[j];
        this[i] = tempj;
        this[j] = tempi;
    }
    return this;
};

var json_base_arr = <?php echo json_encode($json); ?>;

// current question
var current,
// index of the current question
    current_index,
// number of seconds since the begging
    time,
// type of current question
    answer_type,
// initial length of the questions json object
    initial_length,
// timer html container
    timer_container = document.getElementById('timer'),
// results overlay container
    results_container = document.getElementById('final-results'),
// questions container
    questions_container = document.getElementById('questions'),
// category name container
    category_name_container = document.getElementById('category-name'),
// question container
    question_container = document.getElementById('question'),
// answers container
    answers_container = document.getElementById('answers'),
// check button container
    check_container = document.getElementById('check'),
// next button container
    next_container = document.getElementById('next'),
// result stats container
    stats_container = document.getElementById('results-stats'),
// stats with correct results
    stats_correct_container = document.getElementById('good-result-no'),
// stats with wrong results
    stats_wrong_container = document.getElementById('bad-result-no'),
// stats with correct results bar
    stats_correct_bar_container = document.getElementById('good-result'),
// stats with wrong results bar
    stats_wrong_bar_container = document.getElementById('bad-result'),
// controls container
    controls_container = document.getElementById('controls'),
// interval timer
    timer,
// questions json object
    json_arr,
// current categoryId filter
    categoryId = false,
// category name
    categoryName = '';

/**
 * Start quiz
 *
 * @returns {boolean}
 */
var startQuiz = function (categoryIdParam, categoryNameParam) {

    // clone the original json array
    json_arr = json_base_arr.slice(0);
    categoryId = false;
    categoryName = '';

    if (categoryIdParam) {
        categoryId = categoryIdParam;
        categoryName = categoryNameParam;
        // get the questions from a particular categoryId
        json_arr = filterCategory(json_arr, categoryId);
    }

    category_name_container.innerHTML = categoryName;

    // get the length of the json object
    initial_length = json_arr.length;

    // give it a good shuffle
    json_arr.shuffle();
    json_arr.shuffle();
    json_arr.shuffle();

    // reset the answers counter
    questionsAnswers.reset();

    // reset the time
    time = 0;

    clearInterval(timer);
    displayTime(time);

    // in case of a restart hide the result stats
    results_container.style.display = 'none';

    // hide results stats because there isn't any answer at this point
    stats_container.style.display = 'none';

    // hide empty quiz
    if (initial_length == 0) {
        updateQuestionCounter();
        hideQuiz();
        return false;
    }

    // set the interval to update the time
    timer = setInterval(function () {
        time++;

        displayTime(time);
    }, 1000);

    // show quiz form in case the previously there was an empty quiz
    showQuiz();

    current_index = -1;

    // get the first question
    displayNextQuestion();

    return true;
};

/**
 * Hide quiz if quiz is empty
 *
 */
var hideQuiz = function () {
    question_container.style.display = 'none';
    answers_container.style.display = 'none';
    controls_container.style.display = 'none';
};

/**
 * Show quiz
 *
 */
var showQuiz = function () {
    question_container.style.display = 'block';
    answers_container.style.display = 'block';
    controls_container.style.display = 'block';
};

/**
 * Filter questions by category
 *
 */
var filterCategory = function (questions, category) {

    return questions.filter(function (question) {
        return question.relations.indexOf(category) !== -1;
    });
};

/**
 * Update timer label
 *
 */
var displayTime = function (time) {

    var hours = Math.floor(time / 3600);
    var minutes = Math.floor(time / 60) - hours * 60;
    var seconds = time - minutes * 60 - hours * 3600;

    if (minutes < 10) {
        minutes = '0' + minutes;
    }

    if (seconds < 10) {
        seconds = '0' + seconds;
    }

    timer_container.innerHTML = hours + ':' + minutes + ':' + seconds;
};

/**
 * Display next question
 */
var displayNextQuestion = function () {

    current = json_arr[current_index + 1];

    if (!current) {
        return stopGame();
    }

    current_index++;

    updateQuestionCounter();

    displayQuestion(current, current_index);
};

/**
 * Display previous question
 */
var displayPreviousQuestion = function () {

    current = json_arr[current_index - 1];
console.log(current.data);
    console.log(current_index);
    if (!current) {
        return;
    }

    current_index--;

    updateQuestionCounter();

    displayQuestion(current, current_index);
};

/**
 * Display question
 */
var displayQuestion = function (question, index) {

    var img = '';

    if (question.data.img) {
        img = '<br /><img src="data/<?php echo QUESTION_IMAGE; ?>/' + question.data.img + '" width=100% />';
    }

    question_container.innerHTML = question.data.question + img;

    var was_answered = questionsAnswers.wasAnswered(index);

    var answers = question.data.ans;

    if (!was_answered) {
        answers.shuffle();
    }

    answers_container.innerHTML = '';

    var correct = 0;

    answer_type = 'simple';
    for (var i = 0; i < answers.length; i++) {

        if (answers[i].correct === 'true') {
            correct++;
        }

        if (correct > 1) {
            answer_type = 'multiple';
        }

        var p = createAnswerObj({
            'id': i,
            'txt': answers[i]['text'],
            'was_answered': was_answered,
            'was_selected': answers[i].was_selected
        });

        answers_container.appendChild(p);
    }

    if (was_answered) {
        check_container.style.display = 'none';
        next_container.style.display = 'block';
        checkAnswers(true);
    } else {
        check_container.style.display = '';
        next_container.style.display = 'none';
    }

    updatePager(index);
};

var updatePager = function (index) {
    document.getElementById('pager-left').style.display = (index == 0) ? 'none' : '';
    document.getElementById('pager-right').style.display = (index == (initial_length - 1)) ? 'none' : '';
};

var updateQuestionCounter = function () {
    questions_container.innerHTML = (current_index + 1) + '/' + initial_length;
};

/**
 * Stop game if there aren't any more questions, see displayNextQuestion
 *
 * @returns {boolean}
 */
var stopGame = function () {

    var correct_answers = questionsAnswers.getCorrectNumber();
    var incorrect_answers = questionsAnswers.getWrongNumber();
    var skipped_questions = initial_length - correct_answers - incorrect_answers;

    results_container.style.display = 'block';
    document.getElementById('good-final-result').innerHTML = correct_answers.toString();
    document.getElementById('bad-final-result').innerHTML = incorrect_answers.toString();
    document.getElementById('skipped-final-result').innerHTML = skipped_questions.toString();

    document.getElementById('timer-result').innerHTML = timer_container.innerHTML;
    clearInterval(timer);

    return false;
};

/**
 * Create answer HTML object to be inserted in the page
 *
 * @param obj Current question object
 * @returns {HTMLElement}
 */
var createAnswerObj = function (obj) {
    var p = document.createElement('p');
    // assign select answer functionality to the new element
    if (obj.was_answered) {
        p.onclick = function () {
            return false;
        };
    } else {
        p.onclick = function () {
            selectItem(this.id);
        };
    }

    p.id = 'ap' + obj.id;

    var span = document.createElement('span');
    span.innerHTML = obj.txt;
    p.appendChild(span);

    var input = document.createElement('input');
    input.type = 'hidden';
    input.id = 'a' + obj.id;

    if (obj.was_selected) {
        input.value = 'true';
        console.log(obj);
    }
    p.appendChild(input);

    return p;
};

/**
 * Select answer
 *
 * @param id Id of the selected answer
 */
var selectItem = function (id) {

    var no = id.replace('ap', '');

    console.log(current.data.ans[no]);
    // was_selected

    if (answer_type == 'simple') {
        for (var i = 0; i < current.data.ans.length; i++) {
            document.getElementById('ap' + i).className = '';
            document.getElementById('a' + i).value = '';
            current.data.ans[i].was_selected = false;
        }

        document.getElementById('ap' + no).className = 'selected';
        document.getElementById('a' + no).value = 'true';
        current.data.ans[no].was_selected = true;
    } else {
        if (document.getElementById('ap' + no).className == '') {
            document.getElementById('ap' + no).className = 'selected';
            document.getElementById('a' + no).value = 'true';
            current.data.ans[no].was_selected = true;
        } else {
            document.getElementById('ap' + no).className = '';
            document.getElementById('a' + no).value = '';
            current.data.ans[no].was_selected = false;
        }
    }

};

/**
 * Check if the answers selected are correct
 *
 * @returns {boolean}
 */
var checkAnswers = function (display_only) {

    if (!current) {
        return false;
    }

    var correct = true;
    var answers = current.data.ans;

    for (var i = 0; i < answers.length; i++) {

        var p = document.getElementById('ap' + i);
        var input = document.getElementById('a' + i);

        if (input.value == 'true' && answers[i]['correct'] == 'true') {
            p.className = 'selected_correct';
        } else if (input.value == 'true' && answers[i]['correct'] !== 'true') {
            correct = false;
            p.className = 'error';
        } else if (input.value == '' && answers[i]['correct'] == 'true') {
            correct = false;
            p.className = 'correct';
        }

        p.onclick = function () {
            return false;
        };
    }

    if (display_only) {
        return true;
    }

    // if the answer is correct go to the next question, otherwise display the correct result
    if (correct == false) {
        questionsAnswers.setWrong(current_index);
        check_container.style.display = 'none';
        next_container.style.display = 'block';
    } else {
        questionsAnswers.setCorrect(current_index);
        displayNextQuestion();
    }

    updatePercent();
};

/**
 * Skip current question
 */
var skippQuestion = function () {
    displayNextQuestion();
};

/**
 * Update results stats from the bottom of the screen
 */
var updatePercent = function () {

    var correct_answers = questionsAnswers.getCorrectNumber();

    var incorrect_answers = questionsAnswers.getWrongNumber();

    // display results stats container if it's not already displayed
    stats_container.style.display = 'block';

    // set number of correct/wrong answers
    stats_correct_container.innerHTML = correct_answers.toString();
    stats_wrong_container.innerHTML = incorrect_answers.toString();

    // display the graph with results percent
    var proc = Math.round((86 / (correct_answers + incorrect_answers)) * correct_answers);
    stats_correct_bar_container.style.width = proc + '%';
    stats_wrong_bar_container.style.width = (86 - proc) + '%';
};

/**
 * Answers manager
 */
var questionsAnswers = (function () {
    var questions_result = [];

    return {
        getCorrectNumber: function () {
            return questions_result.filter(function (x) {
                return x == 'c';
            }).length;
        },
        getWrongNumber: function () {
            return questions_result.filter(function (x) {
                return x == 'i';
            }).length;
        },
        setCorrect: function (index) {
            questions_result[index] = 'c';
        },
        setWrong: function (index) {
            questions_result[index] = 'i';
        },
        wasAnswered: function (index) {
            return (index in questions_result);
        },
        reset: function () {
            questions_result = [];
        }
    };
}());

/**
 * Display QR Code for mobile app import
 */
var displayQr = function () {
    document.getElementById('qr-container').style.display = '';

    var url = document.location.href;

    // filter parameters
    url = url.replace("index.php", "");
    if (url.indexOf("?") != -1) {
        url = url.substring(0, url.indexOf("?"));
    }
    url = encodeURIComponent(url);


    document.getElementById('qr-img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + url;
};

// on window load start the quiz
window.onload = function () {
    startQuiz();
};

</script>