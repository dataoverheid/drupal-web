<?PHP

namespace Drupal\ckan\Routing;

use Drupal\ckan\CkanRequestInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 *
 */
class ParamConverterDataset implements ParamConverterInterface {

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   *
   */
  public function __construct(CkanRequestInterface $ckanRequest) {
    $this->ckanRequest = $ckanRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!empty($value)) {
      return $this->ckanRequest->getDataset($value);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] == 'ckan-dataset';
  }

}
