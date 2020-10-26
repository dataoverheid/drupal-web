<?php

namespace Drupal\donl_migrate\Commands;

use Drupal\ckan\CkanRequestInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_value_list\ValueListInterface;
use Drush\Commands\DrushCommands;

/**
 *
 */
class CleanupApplication extends DrushCommands {

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   *
   */
  public function __construct(CkanRequestInterface $ckanRequest, EntityTypeManagerInterface $entityTypeManager, ValueListInterface $valueList) {
    parent::__construct();
    $this->ckanRequest = $ckanRequest;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->valueList = $valueList;
  }

  /**
   * Cleanup the fields for applications.
   *
   * @command donl_migrate:cleanup-application
   * @aliases donl-migrate-cleanup-application
   * @usage donl_migrate:cleanup-application
   *   Cleanup the fields for applications.
   */
  public function cleanup() {
    $nodes = $this->nodeStorage->loadByProperties(['type' => 'appliance']);

    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $node) {
      // Update themes.
      $id = $node->get('field_theme')->getValue()[0]['value'] ?? NULL;
      if ($theme = $this->getTheme($id)) {
        $node->set('theme', $theme);
      }

      // Update datasets.
      $identifiers = [];
      $oldDatasets = $node->get('field_datasets')->getValue() ?? [];
      foreach ($oldDatasets as $v) {
        $v = explode('/', $v['uri']);
        $uuid = end($v);
        if ($dataset = $this->ckanRequest->getDataset($uuid)) {
          $identifiers[] = $dataset->identifier ?? '';
        }
      }
      $node->set('datasets', $identifiers);

      $node->Save();
    }
  }

  /**
   *
   */
  private function getTheme($id) {
    $themeMapping = [
      0 => 'http://standaarden.overheid.nl/owms/terms/Verkeer_(thema)',
      1 => 'http://standaarden.overheid.nl/owms/terms/Water_(verkeer-thema)',
      2 => 'http://standaarden.overheid.nl/owms/terms/Spoor',
      3 => 'http://standaarden.overheid.nl/owms/terms/Weg_(thema)',
      4 => 'http://standaarden.overheid.nl/owms/terms/Luchtvaart',
      5 => 'http://standaarden.overheid.nl/owms/terms/Werk_(thema)',
      6 => 'http://standaarden.overheid.nl/owms/terms/Levensloop',
      7 => 'http://standaarden.overheid.nl/owms/terms/Ontslag_(thema)',
      8 => 'http://standaarden.overheid.nl/owms/terms/Arbeidsvoorwaarden',
      9 => 'http://standaarden.overheid.nl/owms/terms/Werkgelegenheid',
      10 => 'http://standaarden.overheid.nl/owms/terms/Arbeidsomstandigheden_(thema)',
      11 => 'http://standaarden.overheid.nl/owms/terms/Economie',
      12 => 'http://standaarden.overheid.nl/owms/terms/Bouwnijverheid',
      13 => 'http://standaarden.overheid.nl/owms/terms/Industrie_(thema)',
      14 => 'http://standaarden.overheid.nl/owms/terms/Ondernemen',
      15 => 'http://standaarden.overheid.nl/owms/terms/Overige_economische_sectoren',
      16 => 'http://standaarden.overheid.nl/owms/terms/Markttoezicht',
      17 => 'http://standaarden.overheid.nl/owms/terms/ICT',
      18 => 'http://standaarden.overheid.nl/owms/terms/Transport_(thema)',
      19 => 'http://standaarden.overheid.nl/owms/terms/Handel',
      20 => 'http://standaarden.overheid.nl/owms/terms/Toerisme',
      21 => 'http://standaarden.overheid.nl/owms/terms/Huisvesting_(thema)',
      22 => 'http://standaarden.overheid.nl/owms/terms/Bouwen_en_verbouwen',
      23 => 'http://standaarden.overheid.nl/owms/terms/Huren_en_verhuren',
      24 => 'http://standaarden.overheid.nl/owms/terms/Kopen_en_verkopen',
      25 => 'http://standaarden.overheid.nl/owms/terms/Openbare_orde_en_veiligheid',
      26 => 'http://standaarden.overheid.nl/owms/terms/Terrorisme_(thema)',
      27 => 'http://standaarden.overheid.nl/owms/terms/Rampen',
      28 => 'http://standaarden.overheid.nl/owms/terms/Staatsveiligheid',
      29 => 'http://standaarden.overheid.nl/owms/terms/Politie_brandweer_en_hulpdiensten',
      30 => 'http://standaarden.overheid.nl/owms/terms/Criminaliteit',
      31 => 'http://standaarden.overheid.nl/owms/terms/Onderwijs_en_wetenschap',
      32 => 'http://standaarden.overheid.nl/owms/terms/Onderzoek_en_wetenschap',
      33 => 'http://standaarden.overheid.nl/owms/terms/Basisonderwijs_(thema)',
      34 => 'http://standaarden.overheid.nl/owms/terms/Hoger_onderwijs_(thema)',
      35 => 'http://standaarden.overheid.nl/owms/terms/Voortgezet_onderwijs_(thema)',
      36 => 'http://standaarden.overheid.nl/owms/terms/Beroepsonderwijs_(thema)',
      37 => 'http://standaarden.overheid.nl/owms/terms/Overige_vormen_van_onderwijs',
      38 => 'http://standaarden.overheid.nl/owms/terms/Natuur_en_milieu',
      39 => 'http://standaarden.overheid.nl/owms/terms/Lucht',
      40 => 'http://standaarden.overheid.nl/owms/terms/Natuur-_en_landschapsbeheer',
      41 => 'http://standaarden.overheid.nl/owms/terms/Bodem',
      42 => 'http://standaarden.overheid.nl/owms/terms/Energie',
      43 => 'http://standaarden.overheid.nl/owms/terms/Geluid_(thema)',
      44 => 'http://standaarden.overheid.nl/owms/terms/Afval_(thema)',
      45 => 'http://standaarden.overheid.nl/owms/terms/Stoffen',
      46 => 'http://standaarden.overheid.nl/owms/terms/Financien',
      47 => 'http://standaarden.overheid.nl/owms/terms/Financieel_toezicht',
      48 => 'http://standaarden.overheid.nl/owms/terms/Begroting',
      49 => 'http://standaarden.overheid.nl/owms/terms/Inkomensbeleid',
      50 => 'http://standaarden.overheid.nl/owms/terms/Belasting',
      51 => 'http://standaarden.overheid.nl/owms/terms/Cultuur_en_recreatie',
      52 => 'http://standaarden.overheid.nl/owms/terms/Recreatie_(thema)',
      53 => 'http://standaarden.overheid.nl/owms/terms/Media',
      54 => 'http://standaarden.overheid.nl/owms/terms/Religie',
      55 => 'http://standaarden.overheid.nl/owms/terms/Sport_(thema)',
      56 => 'http://standaarden.overheid.nl/owms/terms/Cultuur_(thema)',
      57 => 'http://standaarden.overheid.nl/owms/terms/Kunst_(thema)',
      58 => 'http://standaarden.overheid.nl/owms/terms/Zorg_en_gezondheid',
      59 => 'http://standaarden.overheid.nl/owms/terms/Geneesmiddelen_en_medische_hulpmiddelen',
      60 => 'http://standaarden.overheid.nl/owms/terms/Gezondheidsrisico\'s',
      61 => 'http://standaarden.overheid.nl/owms/terms/Ethiek',
      62 => 'http://standaarden.overheid.nl/owms/terms/Jongeren_(gezondheid-thema)',
      63 => 'http://standaarden.overheid.nl/owms/terms/Ziekten_en_behandelingen',
      64 => 'http://standaarden.overheid.nl/owms/terms/Voeding',
      65 => 'http://standaarden.overheid.nl/owms/terms/Verzekeringen',
      66 => 'http://standaarden.overheid.nl/owms/terms/Migratie_en_integratie',
      67 => 'http://standaarden.overheid.nl/owms/terms/Immigratie_(thema)',
      68 => 'http://standaarden.overheid.nl/owms/terms/Nederlanderschap_(thema)',
      69 => 'http://standaarden.overheid.nl/owms/terms/Tijdelijk_verblijf',
      70 => 'http://standaarden.overheid.nl/owms/terms/Integratie_(thema)',
      71 => 'http://standaarden.overheid.nl/owms/terms/Emigratie_(thema)',
      72 => 'http://standaarden.overheid.nl/owms/terms/Recht_(thema)',
      73 => 'http://standaarden.overheid.nl/owms/terms/Burgerlijk_recht',
      74 => 'http://standaarden.overheid.nl/owms/terms/Rechtspraak',
      75 => 'http://standaarden.overheid.nl/owms/terms/Bestuursrecht',
      76 => 'http://standaarden.overheid.nl/owms/terms/Staatsrecht',
      77 => 'http://standaarden.overheid.nl/owms/terms/Bezwaar_en_klachten',
      78 => 'http://standaarden.overheid.nl/owms/terms/Strafrecht',
      79 => 'http://standaarden.overheid.nl/owms/terms/Bestuur',
      80 => 'http://standaarden.overheid.nl/owms/terms/Rijksoverheid',
      81 => 'http://standaarden.overheid.nl/owms/terms/Parlement',
      82 => 'http://standaarden.overheid.nl/owms/terms/Gemeenten',
      83 => 'http://standaarden.overheid.nl/owms/terms/Koninklijk_Huis_(thema)',
      84 => 'http://standaarden.overheid.nl/owms/terms/Waterschappen',
      85 => 'http://standaarden.overheid.nl/owms/terms/De_Nederlandse_Antillen_en_Aruba',
      86 => 'http://standaarden.overheid.nl/owms/terms/Organisatie_en_beleid',
      87 => 'http://standaarden.overheid.nl/owms/terms/Provincies',
      88 => 'http://standaarden.overheid.nl/owms/terms/Internationaal',
      89 => 'http://standaarden.overheid.nl/owms/terms/Internationale_samenwerking_(thema)',
      90 => 'http://standaarden.overheid.nl/owms/terms/Defensie_(thema)',
      91 => 'http://standaarden.overheid.nl/owms/terms/Reizen',
      92 => 'http://standaarden.overheid.nl/owms/terms/Ontwikkelingssamenwerking',
      93 => 'http://standaarden.overheid.nl/owms/terms/Militaire_missies',
      94 => 'http://standaarden.overheid.nl/owms/terms/Europese_zaken',
      95 => 'http://standaarden.overheid.nl/owms/terms/Sociale_zekerheid',
      96 => 'http://standaarden.overheid.nl/owms/terms/Gezin_en_kinderen',
      97 => 'http://standaarden.overheid.nl/owms/terms/Nabestaanden',
      98 => 'http://standaarden.overheid.nl/owms/terms/Ziekte_en_arbeidsongeschiktheid',
      99 => 'http://standaarden.overheid.nl/owms/terms/Jongeren_(thema)',
      100 => 'http://standaarden.overheid.nl/owms/terms/Ouderen',
      101 => 'http://standaarden.overheid.nl/owms/terms/Werkloosheid',
      102 => 'http://standaarden.overheid.nl/owms/terms/Landbouw_(thema)',
      103 => 'http://standaarden.overheid.nl/owms/terms/Voedselkwaliteit',
      104 => 'http://standaarden.overheid.nl/owms/terms/Dieren_(thema)',
      105 => 'http://standaarden.overheid.nl/owms/terms/Planten',
      106 => 'http://standaarden.overheid.nl/owms/terms/Ruimte_en_infrastructuur',
      107 => 'http://standaarden.overheid.nl/owms/terms/Netwerken',
      108 => 'http://standaarden.overheid.nl/owms/terms/Waterkeringen_en_waterbeheer',
      109 => 'http://standaarden.overheid.nl/owms/terms/Ruimtelijke_ordening',
    ];
    return $themeMapping[$id] ?? '';
  }

}
