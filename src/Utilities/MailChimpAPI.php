<?php

namespace Drupal\simple_mailchimp\Utilities;

class MailChimpAPI {
  /**
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * @var string
   */
  private $api_key;

  /**
   * @var string
   */
  private $list_id;

  /**
   * @var string
   */
  private $group_id;

  /**
   * @var string
   */
  private $status;

  /**
   * @var string
   */
  private $endpoint;

  /**
   * MailChimpAPI constructor.
   */
  public function __construct() {
    $config = \Drupal::config('simple_mailchimp.settings');
    $this->client = \Drupal::httpClient();
    $this->api_key = $config->get('api_key');
    $this->list_id = $config->get('list_id');
    $this->group_id = $config->get('interest_group');
    $this->status = $config->get('status');
    $this->endpoint = 'https://' . substr($this->api_key,strpos($this->api_key,'-')+1) . '.api.mailchimp.com/3.0';
  }

  /**
   * Request information from MailChimp API.
   *
   * This function is used to get the interest group title and options, if needed.
   *
   * @param string $resource (@see getResourceUrl() - group_title, group_data, subscribe)
   * @param string $method
   * @return mixed|string
   */
  public function request($resource = '', $method = 'GET') {

    $full_url = $this->getResourceUrl($resource);

    try {
      $response = $this->client->$method($full_url, [
        'auth' => ['apikey', $this->api_key],
        'headers' => [
          'content-type' => 'application/json',
          'authorization' => 'Basic '.base64_encode( 'user:'.$this->api_key ),
        ],
      ]);
      $data = json_decode($response->getBody(), true);

      return $data;
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      \Drupal::logger('simple_mailchimp')->notice($e);
    }
  }

  /**
   * Put (subscribe) information to MailChimp API.
   *
   * This function is used to subscribe users.
   *
   * @param string $email
   * @param string $merge_fields
   * @param string $interests
   * @return bool
   */
  public function subscribe($email = '', $merge_fields = '', $interests = '') {

    $full_url = $this->getResourceUrl('subscribe') . md5(strtolower($email));

    $data = array(
      'apikey'        => $this->api_key,
      'email_address' => $email,
      'status'        => $this->status,
      'merge_fields'  => $merge_fields,
      'interests'     => $interests
    );

    try {
      $response = $this->client->put($full_url, [
        'auth' => ['apikey', $this->api_key],
        'headers' => [
          'content-type' => 'application/json',
          'authorization' => 'Basic '.base64_encode( 'user:'.$this->api_key ),
        ],
        'body' => json_encode($data)
      ]);
      $data = $response->getBody();
      drupal_set_message('You have successfully subscribed. Check your inbox to confirm your subscription.');

      return TRUE;
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      $error_message = json_decode($e->getResponse()->getBody()->getContents());
      drupal_set_message($error_message->detail);
      \Drupal::logger('simple_mailchimp')->notice($error_message->detail);

      return FALSE;
    }
  }

  /**
   * Get the appropriate address for MailChimp API endpoint.
   *
   * @param $resource
   * @return string
   */
  public function getResourceUrl($resource) {
    switch ($resource) {
      case 'group_title':
        $this->base_url = '/lists/' . $this->list_id . '/interest-categories/' . $this->group_id;
        break;
      case 'group_data':
        $this->base_url = '/lists/' . $this->list_id . '/interest-categories/' . $this->group_id . '/interests';
        break;
      case 'subscribe':
        $this->base_url = '/lists/' . $this->list_id . '/members/';
        break;
    }
    $base_url = $this->endpoint . $this->base_url;

    return $base_url;
  }

}