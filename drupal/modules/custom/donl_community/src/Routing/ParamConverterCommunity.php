<?PHP

namespace Drupal\donl_community\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 *
 */
class ParamConverterCommunity implements ParamConverterInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!empty($value)) {
      if ($nodes = $this->nodeStorage->loadByProperties(['machine_name' => $value])) {
        /** @var \Drupal\node\NodeInterface $node */
        $node = reset($nodes);
        if ($node->getType() === 'community') {
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
    return !empty($definition['type']) && $definition['type'] == 'donl-community';
  }

}
