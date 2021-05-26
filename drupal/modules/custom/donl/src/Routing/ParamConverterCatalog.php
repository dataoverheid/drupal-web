<?PHP

namespace Drupal\donl\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Catalog param converter
 */
class ParamConverterCatalog implements ParamConverterInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!empty($value)) {
      $properties = [
        'machine_name' => $value,
        'type' => 'catalog',
      ];
      if ($nodes = $this->nodeStorage->loadByProperties($properties)) {
        /* @var \Drupal\node\NodeInterface $node */
        $node = reset($nodes);
        if ($node->getType() === 'catalog') {
          return $node;
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'donl-catalog';
  }

}
