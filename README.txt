# Simple MailChimp #

This module allows users to select existing forms for which they want to add a subscription signup option. It is not meant in any way to be as robust as the standard [MailChimp module](https://www.drupal.org/project/mailchimp).

This module works with:
1. Contact forms
2. Webforms
3. Custom module forms

How to install and configure:

1. Install "Simple MailChimp" from the module list page.
2. Configure Simple MailChimp on your site at admin/config/simple-mailchimp/config, under the Configuration menu.
3. Add MailChimp API key and list ID.
4. Enable forms and map fields.

## Settings ##

* Each form should be on a separate line.
* Separate sections by a pipe.
* Separate properties with a colon.

### Basic Format ###

Structure:
DRUPAL_FORM_ID|EMAIL:drupal_form_email_field|MAILCHIMP_MERGE_TAG:mailchimp_field_type:drupal_form_field

Example:
warranty_form|EMAIL:field_email|FNAME:text:field_fname,LNAME:text:field_lname,MMERGE5:phone:field_phone,MMERGE6:birthday:field_birthday.

MailChimp Field Types:
* text
* zip_code
* number
* address
* date
* phone
* birthday
* website

### MailChimp Address Field ###

The MailChimp address field is not very flexible, and can only be used with US postal addresses. Therefore it is not recommended. However, this module can map form fields to each part of the MailChimp address field.

MailChimp address parts: addr1, addr2, city, state, zip, country

Formatting is similar to before except now we must account for multiple address parts. Separate MailChimp field and form field with a dash. Separate each part of the address with two dashes.

Structure:
MAILCHIMP_MERGE_TAG:address:city-field_city--state-field_state--zip-field_zip

Example:
MMERGE3:address:addr1-address_thoroughfare--addr2-address_premise--city-address_locality--state-address_administrative_area--zip-address_postal_code--country-address_country
