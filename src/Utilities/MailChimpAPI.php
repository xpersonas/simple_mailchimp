<?php

namespace Drupal\simple_mailchimp\Utilities;

use \GuzzleHttp\Exception\RequestException;

/**
 * Class MailChimpAPI.
 *
 * @package Drupal\simple_mailchimp\Utilities
 */
class MailChimpAPI {

  private $client;
  private $apiKey;
  private $listId;
  private $groupId;
  private $status;
  private $baseUrl;
  private $endpoint;

  /**
   * MailChimpAPI constructor.
   */
  public function __construct() {
    $config = \Drupal::config('simple_mailchimp.settings');
    $this->client = \Drupal::httpClient();
    $this->apiKey = $config->get('apiKey');
    $this->listId = $config->get('listId');
    $this->groupId = $config->get('interestGroup');
    $this->status = $config->get('status');
    $this->endpoint = 'https://' . substr($this->apiKey, strpos($this->apiKey, '-') + 1) . '.api.mailchimp.com/3.0';
  }

  /**
   * Request information from MailChimp API.
   *
   * This function is used to get the interest group title and options, if
   * needed.
   *
   * @param string $resource
   *   URL endpoint for MailChimp API resource.
   * @param string $method
   *   Request method defaults to GET.
   *
   * @return mixed|string
   *   Return response from API if successful.
   */
  public function request($resource = '', $method = 'GET') {

    $full_url = $this->getResourceUrl($resource);

    try {
      $response = $this->client->$method($full_url, [
        'auth' => ['apikey', $this->apiKey],
        'headers' => [
          'content-type' => 'application/json',
          'authorization' => 'Basic ' . base64_encode('user:' . $this->apiKey),
        ],
      ]);
      $data = json_decode($response->getBody(), TRUE);

      return $data;
    }
    catch (RequestException $e) {
      $error_message = json_decode($e->getResponse()->getBody()->getContents());
      drupal_set_message($error_message->detail, 'error');
      \Drupal::logger('simple_mailchimp')->notice($e);
    }
  }

  /**
   * Put (subscribe) information to MailChimp API.
   *
   * This function is used to subscribe users.
   *
   * @param string $email
   *   Subscriber email address.
   * @param string $merge_fields
   *   Subscriber merge fields.
   * @param string $interests
   *   Interest-category values.
   *
   * @return bool
   *   Returns true if successful.
   */
  public function subscribe($email = '', $merge_fields = '', $interests = '') {

    $full_url = $this->getResourceUrl('subscribe') . md5(strtolower($email));

    $data = array(
      'apikey'        => $this->apiKey,
      'email_address' => $email,
      'status'        => $this->status,
    );

    if ($merge_fields) {
      $data['merge_fields'] = $merge_fields;
    }

    if ($interests) {
      array_push($data, $interests);
    }

    try {
      $response = $this->client->put($full_url, [
        'auth' => ['apikey', $this->apiKey],
        'headers' => [
          'content-type' => 'application/json',
          'authorization' => 'Basic ' . base64_encode('user:' . $this->apiKey),
        ],
        'body' => json_encode($data),
      ]);
      $data = $response->getBody();
      $x = json_decode($data->getContents());
      drupal_set_message(t('You have successfully subscribed. Check your inbox to confirm your subscription.'));

      return TRUE;
    }
    catch (RequestException $e) {
      $error_message = json_decode($e->getResponse()->getBody()->getContents());
      drupal_set_message($error_message->detail, 'error');
      \Drupal::logger('simple_mailchimp')->notice($error_message->detail);

      return FALSE;
    }
  }

  /**
   * Get the appropriate address for MailChimp API endpoint.
   *
   * @return string
   *   Returns the proper URL endpoint for MailChimp API resource.
   */
  public function getResourceUrl($resource) {
    switch ($resource) {
      case 'group_title':
        $this->baseUrl = '/lists/' . $this->listId . '/interest-categories/' . $this->groupId;
        break;

      case 'group_data':
        $this->baseUrl = '/lists/' . $this->listId . '/interest-categories/' . $this->groupId . '/interests';
        break;

      case 'subscribe':
        $this->baseUrl = '/lists/' . $this->listId . '/members/';
        break;

    }
    $baseUrl = $this->endpoint . $this->baseUrl;

    return $baseUrl;
  }

}
