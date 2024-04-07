<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://ascentria.streamlit.app/
 * @since             1.0.0
 * @package           Hackathon_Matching
 *
 * @wordpress-plugin
 * Plugin Name:       Story Matching
 * Plugin URI:        https://https://ascentria.streamlit.app/
 * Description:       Match users with relevant foster success stories.
 * Version:           1.0.0
 * Author:            CS Social Good
 * Author URI:        https://https://ascentria.streamlit.app//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hackathon-matching
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'HACKATHON_MATCHING_VERSION', '1.0.0' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hackathon-matching-activator.php
 */
function activate_hackathon_matching() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hackathon-matching-activator.php';
	Hackathon_Matching_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-hackathon-matching-deactivator.php
 */
function deactivate_hackathon_matching() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hackathon-matching-deactivator.php';
	Hackathon_Matching_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_hackathon_matching' );
register_deactivation_hook( __FILE__, 'deactivate_hackathon_matching' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-hackathon-matching.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_hackathon_matching() {

	$plugin = new Hackathon_Matching();
	$plugin->run();

}
run_hackathon_matching();

/**
 * The code enqueues and adds local scripts and stylesheets.
 */
function hackathon_matching_enqueue_scripts() {
    // Javascript and CSS
    wp_enqueue_script('hackathon-matching-js', plugins_url('js/hackathon-matching-quiz.js', __FILE__), array('jquery'), null, true);
    wp_register_style( 'hackathon-matching-styles', plugins_url('css/hackathon-matching-styles.css', __FILE__), false, '1.0.0', 'all');
    wp_enqueue_style( 'hackathon-matching-styles' );

    // JavaScript Ajax Functionality
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
    );
    wp_localize_script('hackathon-matching-js', 'hackathon_matching_ajax_object', $translation_array);
}
add_action('wp_enqueue_scripts', 'hackathon_matching_enqueue_scripts');

/**
 * Finds the user story with the most matching tags to the user answers.
 * Returns the id of the user story with the most matches found, or
 * the default story if no match was found.
 */
function hackathon_get_best_match_story_by_tags($user_tags) {
    // Ensure there's no duplicates
    $user_tags = array_unique($user_tags);

    // Query all stories to calculate the match score
    $args = array(
        'post_type' => 'user-story',
        'posts_per_page' => -1, // Get all stories
    );
    $query = new WP_Query($args);
    $best_match_id = null;
    $highest_score = 0;

    // Go through each post
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $story_id = get_the_ID();
            $is_default = false;
            
            // Extract the tags associated with the user story
            $story_taxonomy = get_the_terms($story_id, 'story-tag');
            $story_tags = array();
            if ($story_taxonomy) {
                foreach ($story_taxonomy as $tag) {
                    array_push($story_tags, $tag->name);
                    if ($tag->name == 'default') {
                        $is_default = true;
                    }
                }
            } 

            // Calculate the match score
            $score = count(array_intersect($story_tags, $user_tags));

            // Update best match if this story has a higher score (or is the default)
            if ($score > $highest_score || ($is_default && $best_match_id == null)) {
                $highest_score = $score;
                $best_match_id = $story_id;
            }
        }
    }
    wp_reset_postdata(); // Reset global post data stomped by the_post()

    return $best_match_id; // Return the ID of the best matching story
}

/**
 * Reads HTML template.
 */
function hackathon_matching_quiz_function($atts) {
    $output = file_get_contents(plugin_dir_path(__FILE__) . 'includes/hackathon-matching-quiz.html');
    return $output;
}
add_shortcode('hackathon_matching_quiz', 'hackathon_matching_quiz_function');

// Handle AJAX request for logged-in users
add_action('wp_ajax_hackathon_fetch_question', 'hackathon_fetch_question_callback');

// Handle AJAX request for non-logged-in users
add_action('wp_ajax_nopriv_hackathon_fetch_question', 'hackathon_fetch_question_callback');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hackathon-matching-activator.php
 */
function hackathon_get_questions() {
    $terms = get_terms([
        'taxonomy' => 'story-quiz',
        'hide_empty' => false,
    ]);
    $questions = array();
    $num_terms = count($terms);
    foreach ($terms as $term){
        $q_id = intval(get_field('question_id', $term));
        $q = array(
            'id' => $q_id,
            'question' => $term->name,
            'answers' => array(),
            'total' => $num_terms,
        );
        $q_choices = intval(get_field("number_of_choices", $term));
        for ($i = 1; $i <= $q_choices; $i++) {
            $a_tag = get_field("choice_{$i}_tag", $term);
            $a_text = get_field("choice_{$i}_text", $term);

            if ($a_text != "") {
                $q['answers'][$a_tag] = $a_text;
            }
        }
        if ($q_id < $num_terms) {
            $q["nextQuestionId"] = $q_id + 1;
        }
        $questions[$q_id] = $q;
    }
    return $questions;
}


/**
 * This function handles the communication between backend (questions and answers) and
 * sending that information to the frontend.
 */
function hackathon_fetch_question_callback() {    
    // Dynamically get questions
    $questions = hackathon_get_questions();

    // Get the current question ID from the request
    $current_question_id = isset($_POST['questionId']) ? intval($_POST['questionId']) : 0;
    
    // Get all question tags for the quiz
    $current_answer_tags = isset($_POST['answerTags']) ? $_POST['answerTags'] : [];

    // Check if there's a next question and provide it if available
    if (array_key_exists($current_question_id, $questions)) {
        wp_send_json_success($questions[$current_question_id]);
    } else {
        // Assume the quiz is completed, so find a matching success story
        $matched_story_id = hackathon_get_best_match_story_by_tags($current_answer_tags);

        if ($matched_story_id) {
            // Fetch the matched story
            $args = array(
                'p'         => $matched_story_id,
                'post_type' => 'user-story'
            );
            $query = new WP_Query($args);
            while ($query->have_posts()) {
                $query->the_post();
                $story_post = get_post($matched_story_id);
                
                // Populate story content
                $question_taxonomy = get_the_terms($matched_story_id, 'question-and-answer');
                $question_array = array();
                $answer_array = array();
                if ($question_taxonomy) {
                    foreach ($question_taxonomy as $qa) {
                        $question = get_field('question', $qa);
                        $answer = get_field('answer', $qa);
                        array_push($question_array, $question);
                        array_push($answer_array, $answer);
                    }
                } 
                // Send the story content in the response
                wp_send_json_success([
                    'message' => 'Thank you for completing the quiz!',
                    'storyTitle' => get_the_title(),
                    'storyContent' => get_the_content(),
                    'questions' => $question_array,
                    'answers' => $answer_array,
                    'isComplete' => true, // Indicate that the quiz is complete
                ]);
            }

        } else {
            // No default story found - make sure to add "default" as story tag to at least one story
            wp_send_json_error('Unfortunately, we could not find a matching story at this time. Please check back again soon.');
        }
    }
}
add_action('wp_ajax_hackathon_fetch_question', 'hackathon_fetch_question_callback');
add_action('wp_ajax_nopriv_hackathon_fetch_question', 'hackathon_fetch_question_callback');
