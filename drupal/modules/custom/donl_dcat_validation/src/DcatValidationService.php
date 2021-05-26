<?php

namespace Drupal\donl_dcat_validation;

use DCAT_AP_DONL\DCATBoolean;
use DCAT_AP_DONL\DCATChecksum;
use DCAT_AP_DONL\DCATContactPoint;
use DCAT_AP_DONL\DCATControlledVocabularyEntry;
use DCAT_AP_DONL\DCATDataset;
use DCAT_AP_DONL\DCATDateTime;
use DCAT_AP_DONL\DCATDistribution;
use DCAT_AP_DONL\DCATLegalFoundation;
use DCAT_AP_DONL\DCATLiteral;
use DCAT_AP_DONL\DCATNumber;
use DCAT_AP_DONL\DCATTemporal;
use DCAT_AP_DONL\DCATURI;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Validate resources against the DCAT standards.
 */
class DcatValidationService implements DcatValidationServiceInterface {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $filestorage;

  /**
   * DcatValidationService constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger channel factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerChannelFactory, EntityTypeManagerInterface $entityTypeManager) {
    $this->logger = $loggerChannelFactory->get('ckan_request');
    $this->filestorage = $entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function dataset(array $values): array {
    $validationMessages = [];

    $DcatDataset = new DCATDataset();
    try {
      // Required fields.
      $DcatDataset->setIdentifier(new DCATURI($values['identifier'] ?? ''));
      $DcatDataset->setTitle(new DCATLiteral($values['title'] ?? ''));
      $DcatDataset->setDescription(new DCATLiteral($values['notes']['value'] ?? ''));
      $DcatDataset->setModificationDate(new DCATDateTime(date('Y-m-d') . 'T' . date('H:i:s')));
      $DcatDataset->setAuthority(new DCATControlledVocabularyEntry($values['authority'] ?? '', 'DONL:Organization'));
      $DcatDataset->setPublisher(new DCATControlledVocabularyEntry($values['publisher'] ?? '', 'DONL:Organization'));
      $DcatDataset->setLicense(new DCATControlledVocabularyEntry($values['license'] ?? '', 'DONL:License'));
      $DcatDataset->setMetadataLanguage(new DCATControlledVocabularyEntry($values['metadata_language'] ?? '', 'DONL:Language'));

      foreach ($values['language'] ?? [] as $language) {
        $DcatDataset->addLanguage(new DCATControlledVocabularyEntry($language, 'DONL:Language'));
      }
      foreach ($values['theme'] ?? [] as $theme) {
        $DcatDataset->addTheme(new DCATControlledVocabularyEntry($theme, 'Overheid:Taxonomiebeleidsagenda'));
      }

      $contactPoint = new DCATContactPoint();
      if (!empty($values['contact_point_name'])) {
        $contactPoint->setFullName(new DCATLiteral($values['contact_point_name']));
      }
      if (!empty($values['contact_point_email'])) {
        $contactPoint->setEmail(new DCATLiteral($values['contact_point_email']));
      }
      if (!empty($values['contact_point_address'])) {
        $contactPoint->setAddress(new DCATLiteral($values['contact_point_address']));
      }
      if (!empty($values['contact_point_phone'])) {
        $contactPoint->setPhone(new DCATLiteral($values['contact_point_phone']));
      }
      if (!empty($values['contact_point_title'])) {
        $contactPoint->setTitle(new DCATLiteral($values['contact_point_title']));
      }
      if (!empty($values['contact_point_website'])) {
        $contactPoint->setWebpage(new DCATURI($values['contact_point_website']));
      }
      $DcatDataset->setContactPoint($contactPoint);

      // Optional fields.
      $DcatDataset->setHighValue(new DCATBoolean(empty($values['high_value']) ? 'false' : 'true'));
      $DcatDataset->setBasisRegister(new DCATBoolean(empty($values['base_register']) ? 'false' : 'true'));
      $DcatDataset->setReferentieData(new DCATBoolean(empty($values['reference_data']) ? 'false' : 'true'));
      $DcatDataset->setNationalCoverage(new DCATBoolean(empty($values['national_coverage']) ? 'false' : 'true'));
      if (!empty($values['access_rights'])) {
        $DcatDataset->setAccessRights(new DCATControlledVocabularyEntry($values['access_rights'], 'Overheid:Openbaarheidsniveau'));
      }
      if (!empty($values['frequency'])) {
        $DcatDataset->setFrequency(new DCATControlledVocabularyEntry($values['frequency'], 'Overheid:Frequency'));
      }
      if (!empty($values['source_catalog'])) {
        $DcatDataset->setSourceCatalog(new DCATControlledVocabularyEntry($values['source_catalog'], 'DONL:Catalogs'));
      }
      if (!empty($values['dataset_status'])) {
        $DcatDataset->setDatasetStatus(new DCATControlledVocabularyEntry($values['dataset_status'], 'Overheid:DatasetStatus'));
      }
      if (!empty($values['url'])) {
        $DcatDataset->setLandingPage(new DCATURI($values['url']));
      }
      if (!empty($values['alternate_identifier']) && is_array($values['alternate_identifier'])) {
        foreach ($values['alternate_identifier'] as $alternateIdentifier) {
          if ($alternateIdentifier !== '') {
            $DcatDataset->addAlternativeIdentifier(new DCATURI($alternateIdentifier));
          }
        }
      }
      if (!empty($values['related_resource']) && is_array($values['related_resource'])) {
        foreach ($values['related_resource'] as $relatedResource) {
          if ($relatedResource !== '') {
            $DcatDataset->addRelatedResource(new DCATURI($relatedResource));
          }
        }
      }
      if (!empty($values['provenance']) && is_array($values['provenance'])) {
        foreach ($values['provenance'] as $provenance) {
          if ($provenance !== '') {
            $DcatDataset->addProvenance(new DCATURI($provenance));
          }
        }
      }
      if (!empty($values['source']) && is_array($values['source'])) {
        foreach ($values['source'] as $source) {
          if ($source !== '') {
            $DcatDataset->addSource(new DCATURI($source));
          }
        }
      }
      if (!empty($values['sample']) && is_array($values['sample'])) {
        foreach ($values['sample'] as $sample) {
          if ($sample !== '') {
            $DcatDataset->addSample(new DCATURI($sample));
          }
        }
      }
      if (!empty($values['conforms_to']) && is_array($values['conforms_to'])) {
        foreach ($values['conforms_to'] as $conformsTo) {
          if ($conformsTo !== '') {
            $DcatDataset->addConformsTo(new DCATURI($conformsTo));
          }
        }
      }
      if (!empty($values['tags'])) {
        foreach (explode(',', $values['tags']) as $tag) {
          $DcatDataset->addKeyword(new DCATLiteral($tag));
        }
      }
      if (!empty($values['version'])) {
        $DcatDataset->setVersion(new DCATLiteral($values['version']));
      }
      if (!empty($values['version_notes']) && is_array($values['version_notes'])) {
        foreach ($values['version_notes'] as $versionNotes) {
          if ($versionNotes !== '') {
            $DcatDataset->addVersionNotes(new DCATLiteral($versionNotes));
          }
        }
      }
      if (!empty($values['documentation']) && is_array($values['documentation'])) {
        foreach ($values['documentation'] as $documentation) {
          if ($documentation !== '') {
            $DcatDataset->addDocumentation(new DCATLiteral($documentation));
          }
        }
      }

      if (!empty($values['date_planned']) && $values['date_planned'] instanceof DrupalDateTime) {
        $DcatDataset->setDatePlanned(new DCATDateTime($values['date_planned']->format('Y-m-d\TH:i:s')));
      }

      if (!empty($values['temporal_label']) || !empty($values['temporal_start'] || !empty($values['temporal_end']))) {
        $temporal = new DCATTemporal();
        if (!empty($values['temporal_label'])) {
          $temporal->setLabel(new DCATLiteral($values['temporal_label']));
        }
        if (!empty($values['temporal_start']) && $values['temporal_start'] instanceof DrupalDateTime) {
          $temporal->setStart(new DCATDateTime($values['temporal_start']->format('Y-m-d\TH:i:s')));
        }
        if (!empty($values['temporal_end']) && $values['temporal_end'] instanceof DrupalDateTime) {
          $temporal->setEnd(new DCATDateTime($values['temporal_end']->format('Y-m-d\TH:i:s')));
        }
        $DcatDataset->setTemporal($temporal);
      }

      if (!empty($values['legal_foundation_label']) || !empty($values['legal_foundation_ref'] || !empty($values['legal_foundation_uri']))) {
        $legalFoundation = new DCATLegalFoundation();
        if (!empty($values['legal_foundation_label'])) {
          $legalFoundation->setLabel(new DCATLiteral($values['legal_foundation_label']));
        }
        if (!empty($values['legal_foundation_ref'])) {
          $legalFoundation->setReference(new DCATLiteral($values['legal_foundation_ref']));
        }
        if (!empty($values['legal_foundation_uri'])) {
          $legalFoundation->setUri(new DCATURI($values['legal_foundation_uri']));
        }
        $DcatDataset->setLegalFoundation($legalFoundation);
      }

      // @todo add the "spatial" field.
    }
    catch (\Exception $e) {
      $this->logger->error('Dataset DCAT validation failed. @error', ['@error' => $e]);
    }

    foreach ($DcatDataset->validate()->getMessages() as $message) {
      $renameFields = [
        'accessRights' => 'access_rights',
        'alternativeIdentifier' => 'alternate_identifier',
        'basisRegister' => 'base_register',
        'conformsTo' => 'conforms_to',
        'contactPoint' => 'contact_point',
        'datasetStatus' => 'dataset_status',
        'datePlanned' => 'date_planned',
        'description' => 'notes',
        'highValue' => 'high_value',
        'keyword' => 'tags',
        'landingPage' => 'url',
        'legalFoundation' => 'legal_foundation',
        'metadataLanguage' => 'metadata_language',
        'nationalCoverage' => 'national_coverage',
        'referentieData' => 'reference_data',
        'relatedResource' => 'related_resource',
        'sourceCatalog' => 'source_catalog',
        'versionNotes' => 'version_notes',
      ];

      $messageName = strstr($message, ':', TRUE);
      $fieldName = str_replace(array_keys($renameFields), array_values($renameFields), $messageName);
      $validationMessages[$fieldName][] = substr($message, strlen($messageName) + 2);
    }

    // Ignore warnings about missing data sources.
    unset($validationMessages['distribution']);

    return $validationMessages;
  }

  /**
   * {@inheritdoc}
   */
  public function resource(array $values): array {
    $validationMessages = [];

    $DcatDistribution = new DCATDistribution();
    try {
      // Required fields.
      $DcatDistribution->setTitle(new DCATLiteral($values['name'] ?? ''));
      $DcatDistribution->setDescription(new DCATLiteral($values['description']['value'] ?? ''));
      $DcatDistribution->setLicense(new DCATControlledVocabularyEntry($values['license'] ?? '', 'DONL:License'));
      $DcatDistribution->setMetadataLanguage(new DCATControlledVocabularyEntry($values['metadata_language'] ?? '', 'DONL:Language'));
      $DcatDistribution->setFormat(new DCATControlledVocabularyEntry($values['format'] ?? '', 'MDR:FiletypeNAL'));
      foreach ($values['language'] ?? [] as $language) {
        $DcatDistribution->addLanguage(new DCATControlledVocabularyEntry($language, 'DONL:Language'));
      }

      $url = $values['url'] ?? '';
      if (!empty($values['upload'][0])) {
        if ($file = $this->filestorage->load($values['upload'][0])) {
          $url = file_create_url($file->getFileUri());
        }
      }
      $DcatDistribution->setAccessURL(new DCATURI($url));

      // Optional fields.
      if (!empty($values['mimetype'])) {
        $DcatDistribution->setMediaType(new DCATControlledVocabularyEntry($values['mimetype'], 'IANA:Mediatypes'));
      }
      if (!empty($values['status'])) {
        $DcatDistribution->setStatus(new DCATControlledVocabularyEntry($values['status'], 'ADMS:Distributiestatus'));
      }
      if (!empty($values['distribution_type'])) {
        $DcatDistribution->setDistributionType(new DCATControlledVocabularyEntry($values['distribution_type'], 'DONL:DistributionType'));
      }
      if (!empty($values['rights'])) {
        $DcatDistribution->setRights(new DCATLiteral($values['rights']));
      }
      if (!empty($values['release_date']) && $values['release_date'] instanceof DrupalDateTime) {
        $DcatDistribution->setReleaseDate(new DCATDateTime($values['release_date']->format('Y-m-d\TH:i:s')));
      }
      if (!empty($values['modification_date']) && $values['modification_date'] instanceof DrupalDateTime) {
        $DcatDistribution->setModificationDate(new DCATDateTime($values['modification_date']->format('Y-m-d\TH:i:s')));
      }
      if (!empty($values['size'])) {
        $DcatDistribution->setByteSize(new DCATNumber($values['size'] * 1000));
      }

      if (!empty($values['linked_schemas']) && is_array($values['linked_schemas'])) {
        foreach ($values['linked_schemas'] as $linkedSchemas) {
          if ($linkedSchemas !== '') {
            $DcatDistribution->addLinkedSchemas(new DCATURI($linkedSchemas));
          }
        }
      }
      if (!empty($values['documentation']) && is_array($values['documentation'])) {
        foreach ($values['documentation'] as $documentation) {
          if ($documentation !== '') {
            $DcatDistribution->addDocumentation(new DCATURI($documentation));
          }
        }
      }

      if (!empty($values['download_url']) && is_array($values['download_url'])) {
        foreach ($values['download_url'] as $downloadUrl) {
          if ($downloadUrl !== '') {
            $DcatDistribution->addDownloadURL(new DCATURI($downloadUrl));
          }
        }
      }

      if (!empty($values['hash']) || !empty($values['hash_algorithm'])) {
        $checksum = new DCATChecksum();
        if (!empty($values['hash'])) {
          $checksum->setHash(new DCATLiteral($values['hash']));
        }
        if (!empty($values['hash_algorithm'])) {
          $checksum->setAlgorithm(new DCATLiteral($values['hash_algorithm']));
        }
        $DcatDistribution->setChecksum($checksum);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Resource DCAT validation failed. @error', ['@error' => $e]);
    }

    foreach ($DcatDistribution->validate()->getMessages() as $message) {
      $renameFields = [
        'accessURL' => 'url',
        'byteSize' => 'size',
        'distributionType' => 'distribution_type',
        'downloadURL' => 'download_url',
        'linkedSchema' => 'linked_schemas',
        'mediaType' => 'mimetype',
        'metadataLanguage' => 'metadata_language',
        'modificationDate' => 'modification_date',
        'releaseDate' => 'release_date',
      ];
      if (!empty($values['upload'][0])) {
        $renameFields['accessURL'] = 'upload';
      }

      $messageName = strstr($message, ':', TRUE);
      $fieldName = str_replace(array_keys($renameFields), array_values($renameFields), $messageName);
      $validationMessages[$fieldName][] = substr($message, strlen($messageName) + 2);
    }

    return $validationMessages;
  }

}
