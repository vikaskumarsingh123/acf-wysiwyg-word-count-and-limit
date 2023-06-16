<?php
/**
* Plugin Name: ACF WYSIWYG Word Count and Limit
* Description: Adds a word counter and maximum word limit setting to ACF's What You See Is What You Get field.
* Version: 1.0
* Author: Vikas Kumar Singh
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('acf/init', 'load_acf_wysiwyg_word_count_and_limit');

/**
 * Function's main purpose is to load the plugin after ACF has initialized
 *
 * @return void
 */
function load_acf_wysiwyg_word_count_and_limit(){

    if ( !function_exists('get_field') ) { 
        function ACF_WYSIWYG_admin_notice__error() {
            $class = 'notice notice-error';
            $message = __( 'ACF > v6 is not installed or activated! Please install and activate ACF > v6 to use the ACF WYSIWYG Word Count and Limit Plugin.', 'sample-text-domain' );
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }
        add_action( 'admin_notices', 'ACF_WYSIWYG_admin_notice__error' );
    } else {
        if ( class_exists( 'ACF_WYSIWYG_Word_Count' ) ){
            global $instance_ACF_WYSIWYG_Word_Count;
            if( !isset($instance_ACF_WYSIWYG_Word_Count) ) $instance_ACF_WYSIWYG_Word_Count = new ACF_WYSIWYG_Word_Count();
        }
    }

}

if(!class_exists( 'ACF_WYSIWYG_Word_Count' )):

/**
 * The main plugin class
 */
class ACF_WYSIWYG_Word_Count{

    public function __construct(){
        //Instruct ACF to add this setting to the validation tab of all WYSIWYG fields
            add_action( 'acf/render_field_validation_settings/type=wysiwyg', [ &$this, 'textarea_max_word_count_render_field_settings'] );

        // Alter rendering of the ACF field to show this data on the admin frontend:
            add_filter('acf/render_field/type=wysiwyg', [ &$this, 'add_max_char_count_data_to_field_for_js_to_interpret']);

        // Server side validation of WYSIWYG fields for max words:
            add_filter('acf/validate_value/type=wysiwyg', [ &$this, 'validate_max_words_for_wysiwyg'], 10, 4);

        // Include the word counter JS file on post.php:
            add_action( 'admin_enqueue_scripts', [ &$this, 'add_acf_wysiwyg_word_count_script'] );
    }
    
    /**
     * Instruct ACF to add the max_word_count setting to the validation tab of all WYSIWYG fields
     *
     * @param array $field The WYSIWYG ACF Field
     *
     * @return void
     */
    public function textarea_max_word_count_render_field_settings( $field ) {
        acf_render_field_setting( $field, array(
            'label'        => __( 'Maximum Word Count', 'my-textdomain' ),
            'instructions' => 'Please enter the maximum number of words allowed for this field. Set to 0 (numeric zero) to allow unlimited words.',
            'name'         => 'max_word_count',
            'type'         => 'number',
            'ui'           => 1,
            'default_value' => 0,
        ) ); 
    }
        
    /**
     * Alter rendering of the ACF field to show this data on the admin frontend
     *
     * @param array $field The WYSIWYG ACF Field
     *
     * @return void
     */
    public function add_max_char_count_data_to_field_for_js_to_interpret($field){
        echo "<span data-maxwords='".$field['max_word_count']."'></span>";
    }
        
    /**
     * Server side validation of WYSIWYG fields for max words
     *
     * @param bool $valid If the field is valid
     * @param string $value current value of the field
     * @param array $field The WYSIWYG ACF Field
     * @param string $input_name The name of the actual DOM input
     *
     * @return mixed
     */
    public function validate_max_words_for_wysiwyg( $valid, $value, $field, $input_name ) {
        // Bail early if value is already invalid.
        if( $valid !== true ) {
            return $valid;
        }
        // Prevent value from saving if it contains the companies old name.
        if( is_string($value) && $field['max_word_count'] > 0 && preg_match_all("/[a-z']+/i", html_entity_decode(strip_tags($value), ENT_QUOTES)) > $field['max_word_count'] ) {
            return __( 'Too many words. Please keep number of words less than '.$field['max_word_count']  );
        }
        return $valid;
    }
        
    /**
     * Include the word counter JS file on post.php and post-new.php
     *
     * @param string $hook The current page / script name
     *
     * @return void
     */
    public function add_acf_wysiwyg_word_count_script( $hook ) {
        if ( 'post.php' == $hook || 'post-new.php' == $hook  ) {
            wp_enqueue_script('acf-wysiwyg-word-count-and-limit', plugin_dir_url(__FILE__) . 'acf-wysiwyg-word-count-and-limit.js', array(), '1.0', true );
        } else return;
    }
    
}

endif;