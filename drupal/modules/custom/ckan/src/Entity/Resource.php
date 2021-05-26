<?php

namespace Drupal\ckan\Entity;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 *
 */
class Resource {

  /**
   * The internal unique CKAN id.
   *
   * @var string
   */
  public $id;

  /**
   * The internal CKAN package_id.
   *
   * @var string
   */
  public $package_id;

  /**
   * @var string
   */
  public $url;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $description;

  /**
   * @var string
   */
  public $metadata_language;

  /**
   * @var string[]
   */
  public $language;

  /**
   * @var string
   */
  public $license_id;

  /**
   * @var string
   */
  public $format;

  /**
   * @var int|null
   */
  public $position;

  /**
   * @var int|null
   */
  public $size;

  /**
   * @var string[]|null
   */
  public $download_url;

  /**
   * @var string|null
   */
  public $mimetype;

  /**
   * @var string|null
   */
  public $release_date;

  /**
   * @var string|null
   */
  public $rights;

  /**
   * @var string|null
   */
  public $status;

  /**
   * @var bool
   */
  public $link_status;

  /**
   * @var string|null
   */
  public $link_status_last_checked;

  /**
   * @var string|null
   */
  public $modification_date;

  /**
   * @var string[]|null
   */
  public $linked_schemas;

  /**
   * @var string|null
   */
  public $hash;

  /**
   * @var string|null
   */
  public $hash_algorithm;

  /**
   * @var string[]|null
   */
  public $documentation;

  /**
   * @var string|null
   */
  public $resourceType;

  /**
   * @var string|null
   */
  public $distribution_type;

  /**
   * @var \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public $created;

  /**
   * @var string|null
   */
  public $previewUrl;

  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id): void {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getPackageId(): string {
    return $this->package_id;
  }

  /**
   * @param string $package_id
   */
  public function setPackageId($package_id): void {
    $this->package_id = $package_id;
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * @param string $url
   */
  public function setUrl($url): void {
    $this->url = $url;
  }

  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * @param string $description
   */
  public function setDescription($description): void {
    $this->description = $description;
  }

  /**
   * @return string
   */
  public function getFormat(): string {
    return $this->format;
  }

  /**
   * @param string $format
   */
  public function setFormat($format): void {
    $this->format = $format;
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
  public function setName($name): void {
    $this->name = $name;
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
  public function setMetadataLanguage($metadata_language): void {
    $this->metadata_language = $metadata_language;
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
  public function setLanguage($language): void {
    $this->language = $language;
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
  public function setLicenseId($license_id): void {
    $this->license_id = $license_id;
  }

  /**
   * @return int
   */
  public function getPosition(): ?int {
    return $this->position;
  }

  /**
   * @param int|null $position
   */
  public function setPosition($position): void {
    $this->position = $position;
  }

  /**
   * @return int|null
   */
  public function getSize(): ?int {
    return $this->size;
  }

  /**
   * @param int|null $size
   */
  public function setSize($size): void {
    $this->size = $size;
  }

  /**
   * @return null|string[]
   */
  public function getDownloadUrl(): ?array {
    return $this->download_url;
  }

  /**
   * @param null|string[] $download_url
   */
  public function setDownloadUrl($download_url): void {
    $this->download_url = $download_url;
  }

  /**
   * @return null|string
   */
  public function getMimetype(): ?string {
    return $this->mimetype;
  }

  /**
   * @param null|string $mimetype
   */
  public function setMimetype($mimetype): void {
    $this->mimetype = $mimetype;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getReleaseDate(): ?DrupalDateTime {
    return $this->formatDate($this->release_date);
  }

  /**
   * @param null|string $release_date
   */
  public function setReleaseDate($release_date): void {
    $this->release_date = $release_date;
  }

  /**
   * @return null|string
   */
  public function getRights(): ?string {
    return $this->rights;
  }

  /**
   * @param null|string $rights
   */
  public function setRights($rights): void {
    $this->rights = $rights;
  }

  /**
   * @return null|string
   */
  public function getStatus(): ?string {
    return $this->status;
  }

  /**
   * @param null|string $status
   */
  public function setStatus($status): void {
    $this->status = $status;
  }

  /**
   * @return bool
   */
  public function getLinkStatus(): bool {
    return $this->link_status;
  }

  /**
   * @param bool $link_status
   */
  public function setLinkStatus($link_status): void {
    $this->link_status = $link_status;
  }

  /**
   * @return null|string
   */
  public function getLinkStatusLastChecked(): ?string {
    return $this->link_status_last_checked;
  }

  /**
   * @param null|string $link_status_last_checked
   */
  public function setLinkStatusLastChecked($link_status_last_checked): void {
    $this->link_status_last_checked = $link_status_last_checked;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getModificationDate(): ?DrupalDateTime {
    return $this->formatDate($this->modification_date);
  }

  /**
   * @param null|string $modification_date
   */
  public function setModificationDate($modification_date): void {
    $this->modification_date = $modification_date;
  }

  /**
   * @return null|string[]
   */
  public function getLinkedSchemas(): ?array {
    return $this->linked_schemas;
  }

  /**
   * @param null|string[] $linked_schemas
   */
  public function setLinkedSchemas($linked_schemas): void {
    $this->linked_schemas = $linked_schemas;
  }

  /**
   * @return null|string
   */
  public function getHash(): ?string {
    return $this->hash;
  }

  /**
   * @param null|string $hash
   */
  public function setHash($hash): void {
    $this->hash = $hash;
  }

  /**
   * @return null|string
   */
  public function getHashAlgorithm(): ?string {
    return $this->hash_algorithm;
  }

  /**
   * @param null|string $hash_algorithm
   */
  public function setHashAlgorithm($hash_algorithm): void {
    $this->hash_algorithm = $hash_algorithm;
  }

  /**
   * @return null|string[]
   */
  public function getDocumentation(): ?array {
    return $this->documentation;
  }

  /**
   * @param null|string[] $documentation
   */
  public function setDocumentation($documentation): void {
    $this->documentation = $documentation;
  }

  /**
   * Returns the object as Array.
   *
   * @return array
   */
  public function toArray() {
    return (array) $this;
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

  /**
   * Return the config type for this resource.
   *
   * @return string
   */
  public function getResourceType(): string {
    if (!empty($this->distribution_type)) {
      return $this->distribution_type;
    }
    if (!empty($this->resourceType)) {
      return $this->resourceType;
    }

    $config = \Drupal::configFactory()->get('ckan.dataset.settings');
    if (in_array($this->getFormat(), $config->get('resource.webservice') ?? [], TRUE)) {
      return $this->resourceType = 'https://data.overheid.nl/distributiontype/webservice';
    }

    if (in_array($this->getFormat(), $config->get('resource.documentation') ?? [], TRUE)) {
      return $this->resourceType = 'https://data.overheid.nl/distributiontype/documentation';
    }

    return $this->resourceType = 'https://data.overheid.nl/distributiontype/download';
  }

  /**
   * @param null|string $distribution_type
   */
  public function setDistributionType($distribution_type) {
    $this->distribution_type = $distribution_type;
  }

  /**
   * @return string|null
   */
  public function getDistributionType(): ?string {
    return $this->distribution_type;
  }

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   */
  public function getCreated(): DrupalDateTime {
    return $this->formatDate($this->created);
  }

  /**
   * @param string $created
   */
  public function setCreated(string $created) {
    $this->created = $created;
  }

  /**
   * @return string|null
   */
  public function getPreviewUrl(): string {
    return $this->previewUrl;
  }

  /**
   * @param string $url
   */
  public function setPreviewUrl(string $url) {
    $this->previewUrl = $url;
  }

}
