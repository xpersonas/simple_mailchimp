<?php

namespace Drupal\simple_mailchimp\Utilities;

use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class MailChimpAPI.
 *
 * @package Drupal\simple_mailchimp\Utilities
 */
class MailChimpAPI {

  /**
   * Guzzle client for API call.
   *
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * Mailchimp API key from settings.
   *
   * @var array|mixed|null
   */
  private $apiKey;

  /**
   * Mailchimp list ID from settings.
   *
   * @var array|mixed|null
   */
  private $listId;

  /**
   * Mailchimp group ID from settings.
   *
   * @var array|mixed|null
   */
  private $groupId;

  /**
   * Mailchimp default subscriber status.
   *
   * @var array|mixed|null
   */
  private $status;

  /**
   * Base url for API call.
   *
   * @var string
   */
  private $baseUrl;

  /**
   * Endpoint url for API call.
   *
   * @var string
   */
  private $endpoint;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * MailChimpAPI constructor.
   *
   * @param  \Drupal\Core\Logger\LoggerChannelFactory  $loggerFactory
   * @param  \Drupal\Core\Messenger\MessengerInterface  $messenger
   * @param  \GuzzleHttp\ClientInterface  $client
   * @param  \Drupal\Core\Config\ConfigFactoryInterface  $config
   */
  public function __construct(LoggerChannelFactory $loggerFactory, MessengerInterface $messenger, ClientInterface $client, ConfigFactoryInterface $config) {
    $this->config = $config->get('simple_mailchimp.settings');
    $this->client = $client;
    $this->apiKey = $config->get('apiKey');
    $this->listId = $config->get('listId');
    $this->groupId = $config->get('interestGroup');
    $this->status = $config->get('status');
    $this->endpoint = 'https://' . substr($this->apiKey, strpos($this->apiKey, '-') + 1) . '.api.mailchimp.com/3.0';
    $this->loggerFactory = $loggerFactory->get('simple_mailchimp');
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
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
      $this->messenger->addError($error_message->detail);
      $this->loggerFactory->notice($e);
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
   * @param string $language
   *   Default language.
   *
   * @return bool
   *   Returns true if successful.
   */
  public function subscribe($email = '', $merge_fields = '', $interests = '', $language = 'en') {

    $full_url = $this->getResourceUrl('subscribe') . md5(strtolower($email));

    $data = [
      'apikey'        => $this->apiKey,
      'email_address' => $email,
      'status'        => $this->status,
      'language'      => $language,
    ];

    if ($merge_fields) {
      $data['merge_fields'] = $merge_fields;
    }

    if ($interests) {
      $data['interests'] = $interests;
    }

    try {
      $this->client->put($full_url, [
        'auth' => ['apikey', $this->apiKey],
        'headers' => [
          'content-type' => 'application/json',
          'authorization' => 'Basic ' . base64_encode('user:' . $this->apiKey),
        ],
        'body' => json_encode($data),
      ]);

      $this->messenger->addStatus(t('You have successfully subscribed. Check your inbox to confirm your subscription.'));
      return TRUE;
    }
    catch (RequestException $e) {
      $error_message = json_decode($e->getResponse()->getBody()->getContents());
      $this->messenger->addError($error_message->detail);
      $this->loggerFactory->notice($error_message->detail);

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
