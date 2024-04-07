jQuery(document).ready(function ($) {
    const answerTags = [];

    // Function to fetch and display a question or the completion message
    function fetchQuestion(questionId = 1) { // Default to the first question
        $.ajax({
            url: hackathon_matching_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'hackathon_fetch_question',
                answerTags: answerTags,
                questionId: questionId,
            },
            success: function (response) {
                if (response.success) {
                    // Check if the quiz is complete
                    if (response.data.isComplete) {
                        // Quiz is complete, so display the success story
                        const quiz = document.getElementById("quizContainer");
                        quiz.innerHTML = `
                        <p><b>Congratulations on completing the quiz!</b></p>
                        <p>Based on your answers, we've matched you with a story that reflects your current situation, highlighting the experiences of someone who has been in a similar position.</p>
                        `
                        const container = document.getElementById("user-story-container");
                        container.innerHTML = '<h3>' + response.data.storyTitle + '</h3><br />' + response.data.storyContent;

                        const questionContainer = document.getElementById("user-questions-container");
                        questionContainer.className = 'question-container';

                        questionContainer.innerHTML = '<h3>Some common questions from this story</h3>'
                        questionContainer.style["margin-bottom"] = "50px";
                        questionContainer.style["position"] = "relative";
                        questionContainer.style["top"] = "-30px";

                        // Custom questions and answers
                        for (let i = 0; i < response.data.questions.length; i++) {
                            const storyQuestion = document.createElement("p");
                            storyQuestion.className = 'story-question';
                            storyQuestion.innerHTML = "<b>" + response.data.questions[i] + "</b>";
                            questionContainer.appendChild(storyQuestion);

                            const storyAnswer = document.createElement("p");
                            storyAnswer.className = 'story-answer';
                            storyAnswer.innerHTML = response.data.answers[i];
                            questionContainer.appendChild(storyAnswer);
                        }
                        questionContainer.appendChild(questionContainer);
                    } else {
                        // Continue showing quiz questions and choices
                        $('#question').text(response.data.question);
                        var answersHtml = '';
                        $.each(response.data.answers, function (key, value) {
                            answersHtml += '<button class="answer" data-answer-tag="' + key + '" data-next-question-id="' + response.data.nextQuestionId + '">' + value + '</button>';
                        });
                        $('#answers').html(answersHtml);
                        $('#counter').html(response.data.id + " of " + response.data.total + " questions");
                    }
                } else {
                    // Handle error or explicitly state when there are no more questions for clarity
                    console.error('An error occurred or there are no more questions.');
                    $('#quizContainer').html('<p>An error occurred or the quiz has ended unexpectedly.</p>');
                }
            },
            error: function (xhr, status, error) {
                // Basic error handling
                console.error('Error fetching question:', error);
                $('#quizContainer').html('<p>There was an error processing your request.</p>');
            }
        });
    }
    // Load the first question on page load
    fetchQuestion();

    // Handle answer button clicks to load the next question or display the quiz completion message
    $('#quizContainer').on('click', '.answer', function () {
        var answerTag = $(this).data('answer-tag');
        answerTags.push(answerTag)
        var nextQuestionId = $(this).data('next-question-id');
        if (nextQuestionId) {
            fetchQuestion(nextQuestionId);
        } else {
            // If there's no nextQuestionId, it could indicate the last question was answered,
            console.log('Attempting to fetch...');
        }
    });
});
