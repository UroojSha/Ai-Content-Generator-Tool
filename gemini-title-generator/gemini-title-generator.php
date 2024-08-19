<?php
/*
Plugin Name: Blog Title Generator Tool
Description: Generates blog titles using the Gemini API.
Version: 1.0
Author: Urooj Shafait
Company: LoomVision
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue scripts and styles
function gtg_enqueue_scripts() {
    wp_enqueue_script('gtg-ajax-script', plugin_dir_url(__FILE__) . 'js/gtg-script.js', array('jquery'), null, true);
    wp_localize_script('gtg-ajax-script', 'gtg_ajax_obj', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('gtg_nonce')));
    wp_enqueue_style('gtg-styles', plugin_dir_url(__FILE__) . 'css/gtg-style.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'gtg_enqueue_scripts');

// Register the shortcode
function gtg_shortcode() {
    ob_start();
    ?>
    <div class="gtg-container">
        <input type="text" id="gtg-input" placeholder="Enter a topic (e.g., AI in Healthcare)">
        <button id="gtg-generate-button">Generate Titles</button>
        <div id="gtg-image-container" class="gtg-hidden">
            <img src="<?php echo plugin_dir_url(__FILE__) . 'images/loading.gif'; ?>" alt="Loading" class="gtg-loading-image gtg-hidden">
        </div>
        <ul id="gtg-title-list"></ul>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('title-generator', 'gtg_shortcode');

// AJAX handler function
function gtg_generate_titles() {
    check_ajax_referer('gtg_nonce', 'security');

    $input = sanitize_text_field($_POST['input']);
    $api_key = 'AIzaSyBPAFG69SyFnkMZz_dImgxn25nU2W-lOss'; // Your API Key
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $api_key;

    $headers = [
        'Content-Type' => 'application/json'
    ];

    $body = [
        'contents' => [
            [
                'parts' => [
                    ['text' => "Generate 5 engaging and catchy blog titles based on the following input topic: \"" . $input . "\". Ensure the titles are unique, attention-grabbing, and relevant to the topic provided. The goal is to impress users with creative and high-quality title suggestions that stand out."]
                ]
            ]
        ]
    ];

    $args = [
        'method'    => 'POST',
        'headers'   => $headers,
        'body'      => json_encode($body),
        'timeout'   => 120
    ];

    $response = wp_remote_request($api_url, $args);
    $responseBody = wp_remote_retrieve_body($response);
    $decoded = json_decode($responseBody, true);

    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        $titles = explode("\n", $decoded['candidates'][0]['content']['parts'][0]['text']);
        wp_send_json_success(['titles' => $titles]);
    } else {
        wp_send_json_error('No titles generated');
    }

    wp_die();
}
add_action('wp_ajax_gtg_generate_titles', 'gtg_generate_titles');
add_action('wp_ajax_nopriv_gtg_generate_titles', 'gtg_generate_titles');
