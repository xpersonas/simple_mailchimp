<?php

namespace Drupal\simple_mailchimp\Utilities;

/**
 * Class ValidateDate
 *
 * @package Drupal\simple_mailchimp\Utilities
 */
class ValidateDate {

  public function validate($field_value) {
    try {
      $dt = new \DateTime( trim($field_value) );
      $date = $dt->format('m/d');
      $field_final = $date;
    } catch( \Exception $e ) {
      $field_final = '';
    }

    return $field_final;
  }
}