<?php

namespace Drupal\donl_markdown\Plugin\Editor;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\editor\Plugin\EditorBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a Editor.md-based text editor for Drupal.
 *
 * @Editor(
 *   id = "editor_md",
 *   label = @Translation("Editor.md"),
 *   supports_content_filtering = TRUE,
 *   supports_inline_editing = TRUE,
 *   is_xss_safe = FALSE,
 *   supported_element_types = {
 *     "textarea"
 *   }
 * )
 */
class EditorMd extends EditorBase implements ContainerFactoryPluginInterface {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheBackendInterface $cacheBackend, FileSystemInterface $fileSystem) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cacheBackend = $cacheBackend;
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.discovery'),
      $container->get('file_system'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultSettings() {
    return [
      'editorTheme' => 'base16-light',
      'height' => '440px',
      'path' => (string) Url::fromUserInput('/libraries/editor.md/lib/')->toString(),
      'pluginPath' => (string) Url::fromUserInput('/libraries/editor.md/plugins/')->toString(),
      'previewTheme' => 'default',
      'theme' => 'default',
      'toolbar' => TRUE,
      'toolbarAutoFixed' => TRUE,
      'toolbarIcons' => [
        'bold', 'italic', 'quote', '|',
        'h2', 'h3', 'h4', '|',
        'list-ul', 'list-ol', 'hr', '|',
        'link', 'reference-link', 'image', '|',
        'code', 'preformatted-text', 'code-block', '|',
        'table', 'datetime', 'emoji', 'html-entities', '|',
        'goto-line', 'watch', 'preview', 'fullscreen', 'clear', 'search'
      ],
      'watch' => TRUE,
      'width' => '100%',
    ];
  }

  /**
   * Retrieves the available CodeMirror editor themes.
   *
   * @return array
   *   An array of editor theme names.
   */
  protected function getEditorThemes(): array {
    $cid = 'donl_markdown:editormd.themes';
    if (($cache = $this->cacheBackend->get($cid)) && isset($cache->data)) {
      return $cache->data;
    }

    $themes = [];
    $dir = 'libraries/editor.md/lib/codemirror/theme';
    if (is_dir($dir)) {
      foreach ($this->fileSystem->scanDirectory($dir, '/\.css$/') as $file) {
        $themes[$file->name] = $file->name;
      }
    }
    ksort($themes);

    $this->cacheBackend->set($cid, $themes);
    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $editor = $form_state->get('editor');
    $settings = $editor->getSettings();

    $form['tabs'] = ['#type' => 'vertical_tabs'];

    // General.
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'editor][settings][tabs',
    ];

    $form['general']['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $settings['height'],
    ];

    $form['general']['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $settings['width'],
    ];

    $form['general']['watch'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Watch/Preview'),
      '#default_value' => $settings['watch'],
    ];

    // Themes.
    $form['themes'] = [
      '#type' => 'details',
      '#title' => $this->t('Themes'),
      '#group' => 'editor][settings][tabs',
    ];

    $form['themes']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Editor.md (container, toolbar, etc.)'),
      '#options' => [
        'default' => $this->t('Light (default)'),
        'dark' => $this->t('Dark'),
      ],
      '#default_value' => $settings['theme'],
    ];

    $form['themes']['editorTheme'] = [
      '#type' => 'select',
      '#title' => $this->t('Editor'),
      '#options' => $this->getEditorThemes(),
      '#default_value' => $settings['editorTheme'],
    ];

    $form['themes']['previewTheme'] = [
      '#type' => 'select',
      '#title' => $this->t('Preview'),
      '#options' => [
        'default' => $this->t('Light (default)'),
        'dark' => $this->t('Dark'),
      ],
      '#default_value' => $settings['previewTheme'],
    ];

    // Toolbar.
    $form['toolbar'] = [
      '#type' => 'details',
      '#title' => $this->t('Toolbar'),
      '#group' => 'editor][settings][tabs',
    ];

    $form['toolbar']['toolbar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $settings['toolbar'],
    ];

    $form['toolbar']['toolbarAutoFixed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto Fixed'),
      '#default_value' => $settings['toolbarAutoFixed'],
      '#description' => $this->t('Keeps the toolbar at the top when scrolling.'),
      '#states' => [
        'visible' => [
          '[data-drupal-selector="edit-editor-settings-toolbar-toolbar"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['toolbar']['toolbarIcons'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom'),
      '#default_value' => implode(', ', $settings['toolbarIcons']),
      '#states' => [
        'visible' => [
          '[data-drupal-selector="edit-editor-settings-toolbar-toolbar"]' => ['checked' => TRUE],
        ],
      ],
      '#description' => $this->t('A comma separated value (CSV) list of toolbar icon/plugin names. To separate them into groups, use a pipe (|) in between icons.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->unsetValue('tabs');
    $values = $form_state->getValues();
    $settings = [];
    foreach (['general', 'themes', 'toolbar'] as $k) {
      $settings += $values[$k];
      $form_state->unsetValue($k);
    }

    // Normalize toolbar icons into an array.
    if (isset($settings['toolbarIcons']) && is_string($settings['toolbarIcons'])) {
      $settings['toolbarIcons'] = array_map('trim', explode(',', Xss::filterAdmin($settings['toolbarIcons'])));
    }

    foreach ($settings as $k => $v) {
      $form_state->setValue($k, $v);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getJSSettings(Editor $editor) {
    $settings = $editor->getSettings();

    $settings['lang'] = [
      'description' => $this->t('Open source online Markdown editor.'),
      'tocTitle' => $this->t('Table of Contents'),
      'toolbar' => [
        'undo' => $this->t('Undo'),
        'redo' => $this->t('Redo'),
        'bold' => $this->t('Bold'),
        'del' => $this->t('Strikethrough'),
        'italic' => $this->t('Italic'),
        'quote' => $this->t('Block quote'),
        'ucwords' => $this->t('Words first letter convert to uppercase'),
        'uppercase' => $this->t('Selection text convert to uppercase'),
        'lowercase' => $this->t('Selection text convert to lowercase'),
        'h1' => $this->t('Heading 1'),
        'h2' => $this->t('Heading 2'),
        'h3' => $this->t('Heading 3'),
        'h4' => $this->t('Heading 4'),
        'h5' => $this->t('Heading 5'),
        'h6' => $this->t('Heading 6'),
        'list-ul' => $this->t('Unordered list'),
        'list-ol' => $this->t('Ordered list'),
        'hr' => $this->t('Horizontal rule'),
        'link' => $this->t('Link'),
        'reference-link' => $this->t('Reference link'),
        'image' => $this->t('Image'),
        'code' => $this->t('Code inline'),
        'preformatted-text' => $this->t(
          'Preformatted text / Code block (Tab indent)'
        ),
        'code-block' => $this->t('Code block (Multi-languages)'),
        'table' => $this->t('Tables'),
        'datetime' => $this->t('Datetime'),
        'emoji' => $this->t('Emoji'),
        'html-entities' => $this->t('HTML Entities'),
        'pagebreak' => $this->t('Page break'),
        'watch' => $this->t('Unwatch'),
        'unwatch' => $this->t('Watch'),
        'preview' => $this->t('HTML Preview'),
        'fullscreen' => $this->t('Fullscreen'),
        'clear' => $this->t('Clear'),
        'search' => $this->t('Search'),
        'help' => $this->t('Help'),
        'info' => $this->t(
          'About %editor_label',
          ['%editor_label' => $editor->label()]
        ),
        'superscript' => $this->t('Superscript'),
        'subscript' => $this->t('Subscript'),
      ],
      'buttons' => [
        'enter' => $this->t('Enter'),
        'cancel' => $this->t('Cancel'),
        'close' => $this->t('Close'),
      ],
      'dialog' => [
        'link' => [
          'title' => $this->t('Link'),
          'url' => $this->t('Address'),
          'urlTitle' => $this->t('Title'),
          'urlEmpty' => $this->t('Error: Please fill in the link address.'),
        ],
        'referenceLink' => [
          'title' => $this->t('Reference link'),
          'name' => $this->t('Name'),
          'url' => $this->t('Address'),
          'urlId' => $this->t('ID'),
          'urlTitle' => $this->t('Title'),
          'nameEmpty' => $this->t("Error: Reference name can't be empty."),
          'idEmpty' => $this->t('Error: Please fill in reference link id.'),
          'urlEmpty' => $this->t(
            'Error: Please fill in reference link url address.'
          ),
        ],
        'image' => [
          'title' => $this->t('Image'),
          'url' => $this->t('Address'),
          'link' => $this->t('Link'),
          'alt' => $this->t('Title'),
          'uploadButton' => $this->t('Upload'),
          'imageURLEmpty' => $this->t(
            "Error: picture url address can't be empty."
          ),
          'uploadFileEmpty' => $this->t(
            'Error: upload pictures cannot be empty!'
          ),
          'formatNotAllowed' => $this->t(
            'Error: only allows to upload pictures file, upload allowed image file format:'
          ),
        ],
        'preformattedText' => [
          'title' => $this->t('Preformatted text / Codes'),
          'emptyAlert' => $this->t(
            'Error: Please fill in the Preformatted text or content of the codes.'
          ),
        ],
        'codeBlock' => [
          'title' => $this->t('Code block'),
          'selectLabel' => $this->t('Languages: '),
          'selectDefaultText' => $this->t('Select a code language...'),
          'otherLanguage' => $this->t('Other languages'),
          'unselectedLanguageAlert' => $this->t(
            'Error: Please select the code language.'
          ),
          'codeEmptyAlert' => $this->t(
            'Error: Please fill in the code content.'
          ),
        ],
        'htmlEntities' => [
          'title' => $this->t('HTML Entities'),
        ],
        'help' => [
          'title' => $this->t('Help'),
        ],
        'table' => [
          'title' => $this->t('Tables'),
          'cellsLabel' => $this->t('Cells'),
          'alignLabel' => $this->t('Align'),
          'rows' => $this->t('Rows'),
          'cols' => $this->t('Cols'),
          'aligns' => [
            $this->t('Default'),
            $this->t('Left align'),
            $this->t('Center align'),
            $this->t('Right align'),
          ],
        ],
      ],
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    $libraries = [
      'donl_markdown/editor_md',
      'donl_markdown/donl_markdown',
    ];
    return $libraries;
  }

}
