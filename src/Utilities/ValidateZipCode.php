<?php

namespace Drupal\simple_mailchimp\Utilities;

/**
 * Class ValidateZipCode.
 *
 * @package Drupal\simple_mailchimp\Utilities
 */
class ValidateZipCode {

  /**
   * Validate value is a five digit numeric zip value.
   *
   * @return string
   *   Returns passed value if validated, otherwise returns an empty string.
   */
  public function validate($zipcode) {
    if (preg_match('/^[0-9]{5}?$/', $zipcode)) {
      $field_final = $zipcode;
    }
    else {
      $field_final = '';
    }

    return $field_final;
  }

}
