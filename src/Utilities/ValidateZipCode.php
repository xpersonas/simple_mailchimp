<?php

namespace Drupal\simple_mailchimp\Utilities;

/**
 * Class ValidateZipCode
 *
 * @package Drupal\simple_mailchimp\Utilities
 */
class ValidateZipCode {

  public function validate($zipcode) {
    if(preg_match('/^[0-9]{5}?$/', $zipcode)) {
      $field_final = $zipcode;
    } else {
      $field_final = '';
    }
    return $field_final;
  }
}