<?php

namespace Drupal\ckan\Entity;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 *
 */
class Dataset {

  /**
   * The internal unique CKAN id.
   *
   * @var string|null
   */
  public $id;

  /**
   * @var string
   */
  public $owner_org;

  /**
   * @var Catalog|null
   */
  public $catalog;

  /**
   * @var string
   */
  public $identifier;

  /**
   * @var string[]
   */
  public $alternate_identifier = [];

  /**
   * @var string[]
   */
  public $language = [];

  /**
   * @var string|null
   */
  public $source_catalog;

  /**
   * @var string
   */
  public $authority;

  /**
   * @var string
   */
  public $publisher;

  /**
   * @var string|null
   */
  public $contact_point_email;

  /**
   * @var string|null
   */
  public $contact_point_address;

  /**
   * @var string
   */
  public $contact_point_name;

  /**
   * @var string|null
   */
  public $contact_point_phone;

  /**
   * @var string|null
   */
  public $contact_point_website;

  /**
   * @var string|null
   */
  public $contact_point_title;

  /**
   * @var string|null
   */
  public $access_rights;

  /**
   * @var string|null
   */
  public $url;

  /**
   * @var string[]
   */
  public $conforms_to = [];

  /**
   * @var string[]
   */
  public $related_resource = [];

  /**
   * @var string[]
   */
  public $source = [];

  /**
   * @var string|null
   */
  public $version;

  /**
   * @var string[]
   */
  public $version_notes = [];

  /**
   * @var string[]
   */
  public $has_version = [];

  /**
   * @var string[]
   */
  public $is_version_of = [];

  /**
   * @var string|null
   */
  public $legal_foundation_ref;

  /**
   * @var string|null
   */
  public $legal_foundation_uri;

  /**
   * @var string|null
   */
  public $legal_foundation_label;

  /**
   * @var string|null
   */
  public $frequency;

  /**
   * @var string[]
   */
  public $provenance = [];

  /**
   * @var string[]
   */
  public $documentation = [];

  /**
   * @var string[]
   */
  public $sample = [];

  /**
   * @var string
   */
  public $license_id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $title;

  /**
   * @var string
   */
  public $notes;

  /**
   * @var Tag[]
   */
  public $tags = [];

  /**
   * @var string
   */
  public $metadata_language;

  /**
   * @var string|null
   */
  public $metadata_modified;

  /**
   * @var string[]
   */
  public $theme = [];

  /**
   * @var string
   */
  public $modified;

  /**
   * @var string|null
   */
  public $issued;

  /**
   * @var string[]
   */
  public $spatial_scheme = [];

  /**
   * @var string[]
   */
  public $spatial_value = [];

  /**
   * @var string|null
   */
  public $temporal_label;

  /**
   * @var string|null
   */
  public $temporal_start;

  /**
   * @var string|null
   */
  public $temporal_end;

  /**
   * @var string|null
   */
  public $dataset_status;

  /**
   * The status of the resource links.
   *
   * 1 => OK
   * 2 => Partially OK
   * 3 => Not OK.
   *
   * @var int
   */
  public $dataset_link_status;

  /**
   * @var string|null
   */
  public $date_planned;

  /**
   * @var Resource[]
   */
  public $resources;

  /**
   * @var bool
   */
  public $private;

  /**
   * @var bool
   */
  public $high_value;

  /**
   * @var bool
   */
  public $base_register;

  /**
   * @var bool
   */
  public $reference_data;

  /**
   * @var bool
   */
  public $national_coverage;

  /**
   * @var string|null
   */
  public $dataset_quality;

  /**
   * @var string|null
   */
  public $creator_user_id;

  /**
   * @var string|null
   */
  public $restrictions_statement;

  /**
   * @return string|null
   */
  public function getId(): ?string {
    return $this->id;
  }

  /**
   * @param string|null $id
   */
  public function setId(string $id): void {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getOwnerOrg(): string {
    return $this->owner_org;
  }

  /**
   * @param string $owner_org
   */
  public function setOwnerOrg(string $owner_org): void {
    $this->owner_org = $owner_org;
  }

  /**
   * @return Catalog|null
   */
  public function getCatalog(): ?Catalog {
    return $this->catalog;
  }

  /**
   * @param \Drupal\ckan\Entity\Catalog|null $catalog
   */
  public function setCatalog($catalog) {
    $this->catalog = $catalog;
  }

  /**
   * @return string
   */
  public function getIdentifier(): string {
    return $this->identifier;
  }

  /**
   * @param string $identifier
   */
  public function setIdentifier(string $identifier): void {
    $this->identifier = $identifier;
  }

  /**
   * @return string[]
   */
  public function getAlternateIdentifier(): array {
    return $this->alternate_identifier;
  }

  /**
   * @param string[] $alternate_identifier
   */
  public function setAlternateIdentifier(array $alternate_identifier): void {
    $this->alternate_identifier = $alternate_identifier;
  }

  /**
   * @return string[]
   */
  public function getLanguage(): array {
    return $this->language;
  }

  /**
   * @param string[] $language
   */
  public function setLanguage(array $language): void {
    $this->language = $language;
  }

  /**
   * @return string|null
   */
  public function getSourceCatalog(): ?string {
    return $this->source_catalog;
  }

  /**
   * @param string|null $source_catalog
   */
  public function setSourceCatalog(string $source_catalog = NULL): void {
    $this->source_catalog = $source_catalog;
  }

  /**
   * @return string
   */
  public function getAuthority(): string {
    return $this->authority;
  }

  /**
   * @param string $authority
   */
  public function setAuthority(string $authority): void {
    $this->authority = $authority;
  }

  /**
   * @return string
   */
  public function getPublisher(): string {
    return $this->publisher;
  }

  /**
   * @param string $publisher
   */
  public function setPublisher(string $publisher): void {
    $this->publisher = $publisher;
  }

  /**
   * @return string|null
   */
  public function getContactPointEmail(): ?string {
    return $this->contact_point_email;
  }

  /**
   * @param string|null $contact_point_email
   */
  public function setContactPointEmail(string $contact_point_email = NULL): void {
    $this->contact_point_email = $contact_point_email;
  }

  /**
   * @return string|null
   */
  public function getContactPointAddress(): ?string {
    return $this->contact_point_address;
  }

  /**
   * @param string|null $contact_point_address
   */
  public function setContactPointAddress(string $contact_point_address = NULL): void {
    $this->contact_point_address = $contact_point_address;
  }

  /**
   * @return string
   */
  public function getContactPointName(): string {
    return $this->contact_point_name;
  }

  /**
   * @param string $contact_point_name
   */
  public function setContactPointName(string $contact_point_name): void {
    $this->contact_point_name = $contact_point_name;
  }

  /**
   * @return string|null
   */
  public function getContactPointPhone(): ?string {
    return $this->contact_point_phone;
  }

  /**
   * @param string|null $contact_point_phone
   */
  public function setContactPointPhone(string $contact_point_phone = NULL): void {
    $this->contact_point_phone = $contact_point_phone;
  }

  /**
   * @return string|null
   */
  public function getContactPointWebsite(): ?string {
    return $this->contact_point_website;
  }

  /**
   * @param string|null $contact_point_website
   */
  public function setContactPointWebsite(string $contact_point_website = NULL): void {
    $this->contact_point_website = $contact_point_website;
  }

  /**
   * @return string|null
   */
  public function getContactPointTitle(): ?string {
    return $this->contact_point_title;
  }

  /**
   * @param string|null $contact_point_title
   */
  public function setContactPointTitle(string $contact_point_title = NULL): void {
    $this->contact_point_title = $contact_point_title;
  }

  /**
   * @return string|null
   */
  public function getAccessRights(): ?string {
    return $this->access_rights;
  }

  /**
   * @param string|null $access_rights
   */
  public function setAccessRights(string $access_rights = NULL): void {
    $this->access_rights = $access_rights;
  }

  /**
   * @return string|null
   */
  public function getUrl(): ?string {
    return $this->url;
  }

  /**
   * @param string|null $url
   */
  public function setUrl(string $url = NULL): void {
    $this->url = $url;
  }

  /**
   * @return string[]
   */
  public function getConformsTo(): array {
    return $this->conforms_to;
  }

  /**
   * @param string[] $conforms_to
   */
  public function setConformsTo(array $conforms_to): void {
    $this->conforms_to = $conforms_to;
  }

  /**
   * @return string[]
   */
  public function getRelatedResource(): array {
    return $this->related_resource;
  }

  /**
   * @param string[] $related_resource
   */
  public function setRelatedResource(array $related_resource): void {
    $this->related_resource = $related_resource;
  }

  /**
   * @return string[]
   */
  public function getSource(): array {
    return $this->source;
  }

  /**
   * @param string[] $source
   */
  public function setSource(array $source): void {
    $this->source = $source;
  }

  /**
   * @return string|null
   */
  public function getVersion(): ?string {
    return $this->version;
  }

  /**
   * @param string|null $version
   */
  public function setVersion(string $version = NULL): void {
    $this->version = $version;
  }

  /**
   * @return string[]
   */
  public function getVersionNotes(): array {
    return $this->version_notes;
  }

  /**
   * @param string[] $versionNotes
   */
  public function setVersionNotes(array $versionNotes): void {
    $this->version_notes = $versionNotes;
  }

  /**
   * @return string[]
   */
  public function getHasVersion(): array {
    return $this->has_version;
  }

  /**
   * @param string[] $has_version
   */
  public function setHasVersion(array $has_version): void {
    $this->has_version = $has_version;
  }

  /**
   * @return string[]
   */
  public function getIsVersionOf(): array {
    return $this->is_version_of;
  }

  /**
   * @param string[] $is_version_of
   */
  public function setIsVersionOf(array $is_version_of): void {
    $this->is_version_of = $is_version_of;
  }

  /**
   * @return string|null
   */
  public function getLegalFoundationRef(): ?string {
    return $this->legal_foundation_ref;
  }

  /**
   * @param string|null $legal_foundation_ref
   */
  public function setLegalFoundationRef(string $legal_foundation_ref = NULL): void {
    $this->legal_foundation_ref = $legal_foundation_ref;
  }

  /**
   * @return string|null
   */
  public function getLegalFoundationUri(): ?string {
    return $this->legal_foundation_uri;
  }

  /**
   * @param string|null $legal_foundation_uri
   */
  public function setLegalFoundationUri(string $legal_foundation_uri = NULL): void {
    $this->legal_foundation_uri = $legal_foundation_uri;
  }

  /**
   * @return string|null
   */
  public function getLegalFoundationLabel(): ?string {
    return $this->legal_foundation_label;
  }

  /**
   * @param string|null $legal_foundation_label
   */
  public function setLegalFoundationLabel(string $legal_foundation_label = NULL): void {
    $this->legal_foundation_label = $legal_foundation_label;
  }

  /**
   * @return string|null
   */
  public function getFrequency(): ?string {
    return $this->frequency;
  }

  /**
   * @param string|null $frequency
   */
  public function setFrequency(string $frequency = NULL): void {
    $this->frequency = $frequency;
  }

  /**
   * @return string[]
   */
  public function getProvenance(): array {
    return $this->provenance;
  }

  /**
   * @param string[] $provenance
   */
  public function setProvenance(array $provenance): void {
    $this->provenance = $provenance;
  }

  /**
   * @return string[]
   */
  public function getSample(): array {
    return $this->sample;
  }

  /**
   * @param string[] $sample
   */
  public function setSample(array $sample): void {
    $this->sample = $sample;
  }

  /**
   * @return string[]
   */
  public function getDocumentation(): array {
    return $this->documentation;
  }

  /**
   * @param string[] $documentation
   */
  public function setDocumentation(array $documentation): void {
    $this->documentation = $documentation;
  }

  /**
   * @return string
   */
  public function getLicenseId(): string {
    return $this->license_id;
  }

  /**
   * @param string $license_id
   */
  public function setLicenseId(string $license_id): void {
    $this->license_id = $license_id;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name): void {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * @param string $title
   */
  public function setTitle(string $title): void {
    $this->title = $title;
  }

  /**
   * @return string
   */
  public function getNotes(): string {
    return $this->notes;
  }

  /**
   * @param string $notes
   */
  public function setNotes(string $notes): void {
    $this->notes = $notes;
  }

  /**
   * @return Tag[]
   */
  public function getTags(): ?array {
    return $this->tags;
  }

  /**
   * @param Tag[] $tags
   */
  public function setTags(array $tags): void {
    $this->tags = $tags;
  }

  /**
   * @return string
   */
  public function getMetadataLanguage(): string {
    return $this->metadata_language;
  }

  /**
   * @param string $metadata_language
   */
  public function setMetadataLanguage(string $metadata_language): void {
    $this->metadata_language = $metadata_language;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getMetadataModified(): ?DrupalDateTime {
    return $this->formatDate($this->metadata_modified);
  }

  /**
   * @param string|null $metadata_modified
   */
  public function setMetadataModified(string $metadata_modified = NULL): void {
    $this->metadata_modified = $metadata_modified;
  }

  /**
   * @return string[]
   */
  public function getTheme(): array {
    return $this->theme;
  }

  /**
   * @param string[] $theme
   */
  public function setTheme(array $theme): void {
    $this->theme = $theme;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getModified(): ?DrupalDateTime {
    return $this->formatDate($this->modified);
  }

  /**
   * @param string $modified
   */
  public function setModified(string $modified): void {
    $this->modified = $modified;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getIssued(): ?DrupalDateTime {
    return $this->formatDate($this->issued);
  }

  /**
   * @param string|null $issued
   */
  public function setIssued(string $issued = NULL): void {
    $this->issued = $issued;
  }

  /**
   * @return string[]
   */
  public function getSpatialScheme(): array {
    return $this->spatial_scheme;
  }

  /**
   * @param string[] $spatial_scheme
   */
  public function setSpatialScheme(array $spatial_scheme): void {
    $this->spatial_scheme = $spatial_scheme;
  }

  /**
   * @return string[]
   */
  public function getSpatialValue(): array {
    return $this->spatial_value;
  }

  /**
   * @param string[] $spatial_value
   */
  public function setSpatialValue(array $spatial_value): void {
    $this->spatial_value = $spatial_value;
  }

  /**
   * @return string|null
   */
  public function getTemporalLabel(): ?string {
    return $this->temporal_label;
  }

  /**
   * @param string|null $temporal_label
   */
  public function setTemporalLabel(string $temporal_label = NULL): void {
    $this->temporal_label = $temporal_label;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getTemporalEnd(): ?DrupalDateTime {
    return $this->formatDate($this->temporal_end);
  }

  /**
   * @param string|null $temporal_end
   */
  public function setTemporalEnd(string $temporal_end = NULL): void {
    $this->temporal_end = $temporal_end;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getTemporalStart(): ?DrupalDateTime {
    return $this->formatDate($this->temporal_start);
  }

  /**
   * @param string|null $temporal_start
   */
  public function setTemporalStart(string $temporal_start = NULL): void {
    $this->temporal_start = $temporal_start;
  }

  /**
   * @return string|null
   */
  public function getDatasetStatus(): ?string {
    return $this->dataset_status;
  }

  /**
   * @param string|null $dataset_status
   */
  public function setDatasetStatus(string $dataset_status = NULL): void {
    $this->dataset_status = $dataset_status;
  }

  /**
   * @return int
   */
  public function getDatasetLinkStatus(): int {
    return $this->dataset_link_status;
  }

  /**
   * @param int $dataset_link_status
   */
  public function setDatasetLinkStatus(int $dataset_link_status): void {
    $this->dataset_link_status = $dataset_link_status;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getDatePlanned(): ?DrupalDateTime {
    return $this->formatDate($this->date_planned);
  }

  /**
   * @param string|null $date_planned
   */
  public function setDatePlanned(string $date_planned = NULL): void {
    $this->date_planned = $date_planned;
  }

  /**
   * @return Resource[]
   */
  public function getResources(): array {
    return $this->resources;
  }

  /**
   * @param Resource[] $resources
   */
  public function setResources(array $resources): void {
    $this->resources = $resources;
  }

  /**
   * @param bool
   */
  public function setPrivate(bool $private): void {
    $this->private = $private;
  }

  /**
   * @return bool
   */
  public function getPrivate(): bool {
    return $this->private;
  }

  /**
   * @param bool
   */
  public function setHighValue(bool $high_value): void {
    $this->high_value = $high_value;
  }

  /**
   * @return bool
   */
  public function getHighValue(): bool {
    return $this->high_value;
  }

  /**
   * @param bool
   */
  public function setBaseRegister(bool $base_register): void {
    $this->base_register = $base_register;
  }

  /**
   * @return bool
   */
  public function getBaseRegister(): bool {
    return $this->base_register;
  }

  /**
   * @param bool
   */
  public function setReferenceData(bool $reference_data): void {
    $this->reference_data = $reference_data;
  }

  /**
   * @return bool
   */
  public function getReferenceData(): bool {
    return $this->reference_data;
  }

  /**
   * @param bool $national_coverage
   */
  public function setNationalCoverage(bool $national_coverage): void {
    $this->national_coverage = $national_coverage;
  }

  /**
   * @return bool
   */
  public function getNationalCoverage(): bool {
    return $this->national_coverage;
  }

  /**
   * @return string|null
   */
  public function getDatasetQuality(): ?string {
    return $this->dataset_quality;
  }

  /**
   * @param string|null $dataset_quality
   */
  public function setDatasetQuality(?string $dataset_quality): void {
    $this->dataset_quality = $dataset_quality;
  }

  /**
   * @return string|null
   */
  public function getCreatorUserId(): ?string {
    return $this->creator_user_id;
  }

  /**
   * @param string|null $creator_user_id
   */
  public function setCreatorUserId(?string $creator_user_id): void {
    $this->creator_user_id = $creator_user_id;
  }

  /**
   * @return string|null
   */
  public function getRestrictionsStatement(): ?string {
    return $this->restrictions_statement;
  }

  /**
   * @param string|null $restrictions_statement
   */
  public function setRestrictionsStatement(?string $restrictions_statement): void {
    $this->restrictions_statement = $restrictions_statement;
  }

  /**
   * Returns the object as Array.
   *
   * @return array
   */
  public function toArray() {
    $array = (array) $this;

    // We need to rename these fields a CKAN uses a different name than us.
    $array['organization'] = $array['catalog'];
    $array['basis_register'] = $array['base_register'];
    $array['referentie_data'] = $array['reference_data'];
    unset($array['base_register'], $array['reference_data'], $array['catalog'], $array['dataset_link_status']);

    // These fields are only used within Drupal.
    return $array;
  }

  /**
   * Helper function to turn the ISO date string into an DrupalDateTime object.
   *
   * @param string $string
   *   The date as an ISO date string.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  private function formatDate($string): ?DrupalDateTime {
    if (!empty($string)) {
      return new DrupalDateTime($string);
    }

    return NULL;
  }

}
