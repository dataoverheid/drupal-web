<?php

namespace Drupal\donl_statistics;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Exception;

/**
 *
 */
class StatisticsStorage implements StatisticsStorageInterface {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * @var string
   */
  protected static $table = 'donl_statistics';

  /**
   * @var array
   */
  protected static $fields = ['topic', 'key', 'value', 'source', 'date'];

  /**
   *
   */
  public function __construct(Connection $connection, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->connection = $connection;
    $this->logger = $loggerChannelFactory->get('statistics_storage');
  }

  /**
   * {@inheritdoc}
   */
  public function write(array $row): bool {
    // Check if the statistic on the selected date is already available.
    // If so, update it.
    $select = $this->connection->select(self::$table);
    $select->condition('topic', $row['topic']);
    $select->condition('key', $row['key']);
    $select->condition('source', $row['source']);
    $select->condition('date', $row['date']);
    if ($select->countQuery()->execute()->fetchField()) {
      $query = $this->connection
        ->update(self::$table)
        ->fields(['value' => $row['value']]);
      $query->condition('topic', $row['topic']);
      $query->condition('key', $row['key']);
      $query->condition('source', $row['source']);
      $query->condition('date', $row['date']);
    }
    else {
      $query = $this->connection
        ->insert(self::$table)
        ->fields(self::$fields);
      $query->values($row);
    }

    try {
      $query->execute();
      return TRUE;
    }
    catch (Exception $exception) {
      $this->logger->error($exception->getMessage());
    }

    return FALSE;
  }

}
