<?php

namespace Drupal\donl_statistics;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 *
 */
class PiwikStatistics implements PiwikStatisticsInterface {

  /**
   * @var string
   */
  protected $apiUrl;

  /**
   * @var string
   */
  protected $token;

  /**
   * @var string
   */
  protected $siteId;

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * PiwikStatistics constructor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $configFactory) {
    $this->client = $client;

    if ($config = $configFactory->get('donl_statistics.piwik.settings')) {
      $this->apiUrl = $config->get('api_url');
      $this->token = $config->get('token');
      $this->siteId = $config->get('site_id');
    }
  }

  /**
   * @return array
   */
  public function getMostUsedSearches(): array {
    $stats = [];

    // Default params.
    $params = [
      'module' => 'API',
      'token_auth' => $this->token,
      'idSite' => $this->siteId,
      'format' => 'JSON',
      'filter_limit' => '-1',
      'filter_sort_column' => 'nb_hits',
    ];

    $params['method'] = 'Actions.getSiteSearchKeywords';
    $params['period'] = 'day';
    $params['date'] = 'yesterday';

    try {
      $response = $this->client->request('get', $this->apiUrl . '?' . http_build_query($params));

      if ($results = json_decode($response->getBody()->getContents(), TRUE)) {
        $results = array_slice($results, 0, 10);
        foreach ($results as $result) {
          $stats[] = [
            'topic' => 'most_used_searches',
            'key' => $result['label'],
            'value' => $result['nb_hits'],
            'source' => 'https://data.overheid.nl',
            'date' => strtotime('yesterday'),
          ];
        }
      }
      return $stats;
    }
    catch (\Exception $exception) {

    }
    catch (GuzzleException $exception) {

    }

    return [];
  }

  /**
   * @return array
   */
  public function getPopulairDatasets(): array {
    $stats = [];

    // Default params.
    $params = [
      'module' => 'API',
      'token_auth' => $this->token,
      'idSite' => $this->siteId,
      'format' => 'JSON',
      'filter_limit' => '-1',
      'filter_sort_column' => 'nb_hits',
      'flat' => '1',
    ];

    // The needed method.
    $params['method'] = 'Actions.getPageUrls';
    $params['period'] = 'day';
    $params['date'] = 'yesterday';

    try {
      $response = $this->client->request('get', $this->apiUrl . '?' . http_build_query($params));

      if ($results = json_decode($response->getBody()->getContents(), TRUE)) {
        $results = array_filter($results, static function ($result) {
          return strpos($result['url'], 'https://data.overheid.nl/dataset/') === 0 && strpos($result['url'], '/', 33) === FALSE;
        });
        $results = array_slice($results, 0, 10);

        foreach ($results as $result) {
          $stats[] = [
            'topic' => 'populair_dataset',
            'key' => $result['url'],
            'value' => $result['nb_hits'],
            'source' => 'https://data.overheid.nl',
            'date' => strtotime('yesterday'),
          ];
        }
      }
      return $stats;
    }
    catch (\Exception $exception) {

    }
    catch (GuzzleException $exception) {

    }

    return [];
  }

  /**
   * @return array
   */
  public function getUniqueVisitors(): array {
    // Default params.
    $params = [
      'module' => 'API',
      'token_auth' => $this->token,
      'idSite' => $this->siteId,
      'format' => 'JSON',
    ];

    $params['method'] = 'VisitsSummary.getUniqueVisitors';
    $params['period'] = 'day';
    $params['date'] = 'yesterday';

    try {
      $response = $this->client->request('get', $this->apiUrl . '?' . http_build_query($params));
      // unique_visitors, count, value hits.
      if ($results = json_decode($response->getBody()->getContents(), TRUE)) {
        return [
          'topic' => 'unique_visitors',
          'key' => 'count',
          'value' => $results['value'],
          'source' => 'https://data.overheid.nl',
          'date' => strtotime('yesterday'),
        ];
      }
    }
    catch (\Exception $exception) {

    }
    catch (GuzzleException $exception) {

    }

    return [];
  }

}
