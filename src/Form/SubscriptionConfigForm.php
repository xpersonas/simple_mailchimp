<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Form\SubscriptionConfigForm.
 */
namespace Drupal\simple_mailchimp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SubscriptionConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_mailchimp_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('simple_mailchimp.settings');

    $form['subscription_api_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('MailChimp API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
      '#description' => $this->t('MailChimp API key information can be found at <a href="http://admin.mailchimp.com/account/api" target="_blank">http://admin.mailchimp.com/account/api</a>.')
    );

    $form['subscription_list_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('MailChimp List ID'),
      '#default_value' => $config->get('list_id'),
      '#required' => TRUE,
      '#description' => $this->t('For MailChimp list IDs, login to MC account, go to List, then List Tools, and look for the List ID entry.')
    );

    $form['subscription_status'] = array(
      '#type' => 'select',
      '#title' => $this->t('Subscriber Status'),
      '#options' => [
        'subscribed' => 'subscribed',
        'unsubscribed' => 'unsubscribed',
        'cleaned' => 'cleaned',
        'pending' => 'pending'
      ],
      '#default_value' => $config->get('status'),
      '#required' => TRUE,
      '#description' => $this->t('Set the default status for users as they subscribe. Generally this will be left as <em>pending</em>.')
    );

    $form['subscription_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Subscription Text'),
      '#default_value' => $config->get('text'),
      '#required' => TRUE,
      '#description' => $this->t('Subscribe text will be the text placed with the subscribe checkbox.')
    );

    $form['subscription_enabled_forms'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Enabled Forms'),
      '#default_value' => ($config->get('form_ids') ? implode("\r\n", $config->get('form_ids')) : ''),
      '#required' => TRUE,
    );

    $form['instructions'] = array(
      '#type' => 'details',
      '#title' => $this->t('Enabled Forms Field Guideline'),
      '#description' => $this->t('
        <p>
          Each form should be on a separate line.<br/>
          Separate sections by a pipe.<br/>
          Separate properties with a colon.
        </p>
        <hr/>
        <p>
          <strong>Basic Format:</strong><br/>
          <p>Structure: <em>DRUPAL_FORM_ID|EMAIL:drupal_form_email_field|MAILCHIMP_MERGE_TAG:mailchimp_field_type:drupal_form_field</em></p>
          <p>Example: <em>warranty_form|EMAIL:field_email|FNAME:text:field_fname,LNAME:text:field_lname,MMERGE5:phone:field_phone,MMERGE6:birthday:field_birthday.</em></p>
        </p>
        <hr/>
        <p>
          <strong>MailChimp Field Types:</strong><br/>
          text<br/>
          zip_code<br/>
          number<br/>
          address<br/>
          date<br/>
          phone<br/>
          birthday<br/>
          website<br/>
        </p>
        <hr/>
        <p>
          <strong>MailChimp Address Field:</strong><br/>
          The MailChimp address field is not very flexible, and can only be used with US postal addresses. Therefore it is not recommended. However, this module can accommodate it.<br/>
        </p>
        <p><em>MailChimp address parts: addr1, addr2, city, state, zip, country</em></p>
        <p><em>MMERGE3:address:addr1-address_thoroughfare--addr2-address_premise--city-address_locality--state-address_administrative_area--zip-address_postal_code--country-address_country</em></p>
      '),
      '#open' => FALSE,
    );

    $form['subscription_interests'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Subscription Interests'),
      '#default_value' => $config->get('interest_group'),
      '#required' => FALSE,
      '#description' => $this->t('Subscription interests allow users to opt-in to pre-determined MailChimp groups. Enter the id for the interest group.')
    );

    return $form;

  }

  /**
   * Save configuration settings.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('simple_mailchimp.settings');
    $config->set('api_key', $form_state->getValue('subscription_api_key'));
    $config->set('list_id', $form_state->getValue('subscription_list_id'));
    $config->set('status', $form_state->getValue('subscription_status'));
    $config->set('text', $form_state->getValue('subscription_text'));
    $config->set('form_ids', explode("\r\n", $form_state->getValue('subscription_enabled_forms')));
    $config->set('interest_group', $form_state->getValue('subscription_interests'));
    $config->set('configuration', $form_state->getValue('config'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {

    return [
      'simple_mailchimp.settings',
    ];

  }

}