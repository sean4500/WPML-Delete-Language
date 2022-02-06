<?php
/**
 * Handles admin duties
 * 
 * @package wpml-language-cleanup
 */
namespace WPML_Cleanup\Admin;
use WPML_Cleanup\Utilities, SitePress;

/**
 * Enqueue JavaScript file(s)
 */
add_action( 'admin_enqueue_scripts', __NAMESPACE__ .'\\enqueue_assets' );

/**
 * Register handler for Ajax call
 */
add_action( 'wp_ajax_wpml_cleanup_language', __NAMESPACE__ . '\\cleanup_language' );

/**
 * Add menu to admin tools
 */
add_action( 'admin_menu', function(){
  add_management_page( 'WPML Langauge Cleanup', 'WPML Cleanup Translations', 'install_plugins', 'wpml_language_cleanup', __NAMESPACE__ . '\\render_wpml_language_cleanup_pane', 10 );
} );


/**
 * Enqueues scripts and styles
 */
function enqueue_assets( $hook ){
  if( $hook == 'tools_page_wpml_language_cleanup' ){
    wp_enqueue_style( 'wpml_language_cleanup-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), WPML_LANGUAGE_CLEANUP_VERSION, 'all' );
    wp_enqueue_script( 'wpml_language_cleanup-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ), WPML_LANGUAGE_CLEANUP_VERSION, true );
  }
}


/**
 * Render admin pane
 */
function render_wpml_language_cleanup_pane(){ ?>
  <div class="wrap">
    
    <h2><?php _e( 'WPML Translation Cleanup' ); ?></h2>

    <form action="/wp-admin/admin-post.php" method="post" id="wpml-cleanup-form">
      <input type="hidden" name="action" value="wpml_cleanup">
      <?php wp_nonce_field( 'wpml_cleanup' ); ?>
      <label for="language"><strong><?php _e( 'Select Language to Cleanup' ); ?></strong></label><br>
      <select name="language">
        <option value="">-- <?php _e( 'Select a language' ); ?> --</option>

      <?php 
      $sitepress = new SitePress();
      $languages = $sitepress->get_languages();
      
      if( $languages ){

        foreach( $languages as $language ){ 

          $translation_count = Utilities\get_language_translation_count( $language['code'] );

          if( $translation_count ){ ?>

          <option value="<?php echo esc_attr( $language['code'] ); ?>"><?php echo $language['display_name']; ?> - <?php echo Utilities\get_language_translation_count( $language['code'] ); ?></option>

          <?php
          }
        }
      }
      ?>
      </select><br><br>
      <button class="button button-primary" type="submit"><?php _e( 'Run Cleanup' ); ?> <span style="line-height: 1.4;" class="dashicons dashicons-database-remove"></span></button>
    </form>
  </div>
<?php
}


/**
 * Ajax request handler that runs DB cleanup
 */
function cleanup_language(){
  // Setup variables from the request
  parse_str( $_POST['form'], $form );
  $step = absint( $_POST['step'] );
  $batch_size = 10;

  // Run cleanup
  Utilities\run_cleanup( $form['language'], $batch_size );
  // Get remaining translations
  $translation_count = Utilities\get_language_translation_count( $form['language'] );
  // Calculate progress
  $progress = Utilities\calculate_percentage( $translation_count, $step, $batch_size );
  // Increment to the next step
  $step++;

  // Send response to browser
  wp_send_json_success( 
    array( 
      'step'      => ($progress < 100) ? $step : 'complete', 
      'progress'  => $progress, 
      'remaining' => $translation_count 
    ) 
  );
  exit;
}