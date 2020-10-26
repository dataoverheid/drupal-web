<?php

/**
 * @file
 * Provide views data for donl_statistics.module.
 *
 * @ingroup views_module_handlers
 */

/**
 * Implements hook_views_data().
 */
function donl_statistics_views_data() {
  $data = [];

  $data['donl_statistics'] = [
    'table' => [
      'group' => t('DONL statistics'),
      'base' => [
        'field' => 'serial',
        'title' => t('DONL statistics'),
      ],
    ],
    'topic' => [
      'title' => 'Topic',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'key' => [
      'title' => 'Key',
      'field' => [
        'id' => 'donl_statistics_key_field',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'value' => [
      'title' => 'Value',
      'field' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'source' => [
      'title' => 'Source',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'date' => [
      'title' => 'Date',
      'field' => [
        'id' => 'date',
      ],
      'filter' => [
        'id' => 'date',
      ],
      'sort' => [
        'id' => 'date',
      ],
    ],
  ];

  return $data;
}
