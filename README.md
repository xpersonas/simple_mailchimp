# Simple MailChimp #

This module allows users to select existing forms for which they want to add a 
subscription signup option. It is not meant in any way to be as robust as the 
standard [MailChimp module](https://www.drupal.org/project/mailchimp).

This module works with:
1. Contact forms (core)
2. Webforms (contrib)
3. Custom module forms (custom)

## Installation ##
Module page: https://www.drupal.org/project/simple_mailchimp
```
composer require drupal/simple_mailchimp
```

1. Install "Simple MailChimp" from the module list page.
2. Configure Simple MailChimp on your site at 
admin/config/simple-mailchimp/config, under the Configuration menu.
3. Add MailChimp API key and list ID.
4. Enable forms and map fields.

## Settings ##

* Each form should be on a separate line.
* Separate sections by a pipe.
* Separate properties with a colon.

### Basic Format ###

**Structure:**
```
d8_form_id|email:d8_field|MC_MERGE_TAG:mc_field_type:d8_field
```

**Example:**
```
warranty_form|email:d8_field|FNAME:text:field_fname,MMERGE5:phone:field_phone
```

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

The MailChimp address field is not very flexible, and can only be used with US 
postal addresses. Therefore it is not recommended. However, this module can map 
form fields to each part of the MailChimp address field.

MailChimp address field parts:
* addr1
* addr2
* city
* state
* zip
* country

Formatting is similar to before except now we must account for multiple address 
parts. Separate MailChimp field and form field with a dash. Separate each part
of the address with two dashes.

**Example:**
```
MMERGE3:address:addr1-thoroughfare--addr2-premise--city-locality...
```
