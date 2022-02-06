<?php
namespace WPML_Cleanup\Utilities;

/**
 * Gets translation count for language
 */
function get_language_translation_count( $language_code ){
  global $wpdb;

  $sql = $wpdb->prepare( 
    "SELECT COUNT(*) 
     FROM `{$wpdb->prefix}icl_translations` 
     WHERE `language_code` = %s",
     $language_code
  );

  return $wpdb->get_var( $sql );
}


/**
 * Get translations based on language code
 */
function get_language_translations( $language_code, $batch_size = false ){
  global $wpdb;

  if( $batch_size ){
    $sql = $wpdb->prepare( 
      "SELECT `translation_id`,`element_id`,`element_type`
      FROM `{$wpdb->prefix}icl_translations` 
      WHERE `language_code` = %s
      LIMIT %d",
      $language_code,
      $batch_size
    );
  } else {
    $sql = $wpdb->prepare( 
      "SELECT * 
      FROM `{$wpdb->prefix}icl_translations` 
      WHERE `language_code` = %s",
      $language_code
    );
  }

  return $wpdb->get_results( $sql );
}


/**
 * Deletes translation row
 */
function delete_langauge_translation( $translation_id ){
  global $wpdb;

  return $wpdb->delete(
    $wpdb->prefix . 'icl_translations', 
    array( 'translation_id' => $translation_id ), 
    '%d' 
  );
}


/**
 * Deletes attachments with removing image file
 */
function delete_attachment_post_object( $post_id ){
  global $wpdb;
  // Delete post object
  $deleted = $wpdb->delete(
    $wpdb->prefix . 'posts',
    array( 'ID' => $post_id ),
    '%d'
  );

  // Delete any related postmeta
  if( $deleted ){
    $wpdb->delete(
      $wpdb->prefix . 'postmeta',
      array( 'post_id' => $post_id ),
      '%d' 
    );
  }
}


/**
 * Calculate status of current job
 */
function calculate_percentage( $translation_count, $step, $batch_size ){
  // Calc total batches based on current variables
  $total_batches = ceil( ( $translation_count + ( $batch_size * $step ) ) / $batch_size );
  // Calc progress based on current step
  return round( ( $step / $total_batches ) * 100, 2 );
}


/**
 * Get post_types for current WP environment
 */
function run_cleanup( $language_code, $batch_size ){

  $translations = get_language_translations( $language_code, $batch_size );

  if( $translations ){
    foreach( $translations as $index => $translation ){

      // Handle posts
      if( stripos( $translation->element_type, 'post_' ) !== false  ){
        if( $translation->element_type == 'post_attachment' ){
          delete_attachment_post_object( $translation->element_id );
        } else {
          wp_delete_post( $translation->element_id, true );
        }
      }

      // Handle Terms
      if( stripos( $translation->element_type, 'tax_' ) !== false ){
        wp_delete_term( $translation->element_id, str_replace( 'tax_', '', $translation->element_type ), array( 'force_delete' => true ) );
      }

      // Delete WPML translation itself
      delete_langauge_translation( $translation->translation_id );
    } // endforeach
  } // endif
}