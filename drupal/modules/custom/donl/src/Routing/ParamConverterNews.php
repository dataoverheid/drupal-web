<?PHP

namespace Drupal\donl\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * News param converter
 */
class ParamConverterNews implements ParamConverterInterface {

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
        'type' => 'recent',
      ];
      if ($nodes = $this->nodeStorage->loadByProperties($properties)) {
        /* @var \Drupal\node\NodeInterface $node */
        $node = reset($nodes);
        if ($node->getType() === 'recent') {
          return $node;
        }
      }

      if (is_numeric($value) && ($node = $this->nodeStorage->load($value)) && $node->getType() === 'recent') {
        return $node;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'donl-news';
  }

}
