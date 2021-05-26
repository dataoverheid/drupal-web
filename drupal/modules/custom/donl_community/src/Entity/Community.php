<?php

namespace Drupal\donl_community\Entity;

/**
 *
 */
class Community {

  /**
   * @var int
   */
  protected $nid;

  /**
   * @var string
   */
  protected $title;

  /**
   * @var string
   */
  protected $identifier;

  /**
   * @var string
   */
  protected $machineName;

  /**
   * @var string
   */
  protected $backgroundImage;

  /**
   * @var string
   */
  protected $colour;

  /**
   * @var string
   */
  protected $description;

  /**
   * @var string
   */
  protected $shortName;

  /**
   * @var array
   */
  protected $themes;

  /**
   * @return int
   */
  public function getNid(): int {
    return $this->nid;
  }

  /**
   * @param int $nid
   */
  public function setNid(int $nid): void {
    $this->nid = $nid;
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
   * @return string
   */
  public function getMachineName(): string {
    return $this->machineName;
  }

  /**
   * @param string $machineName
   */
  public function setMachineName(string $machineName): void {
    $this->machineName = $machineName;
  }

  /**
   * @return string
   */
  public function getBackgroundImage(): string {
    return $this->backgroundImage;
  }

  /**
   * @param string $backgroundImage
   */
  public function setBackgroundImage(string $backgroundImage): void {
    $this->backgroundImage = $backgroundImage;
  }

  /**
   * @return array
   */
  public function getThemes(): array {
    return $this->themes;
  }

  /**
   * @param array $themes
   */
  public function setThemes(array $themes): void {
    $this->themes = $themes;
  }

  /**
   * @return string
   */
  public function getColour() {
    return $this->colour ?? 'green';
  }

  /**
   * @param string $colour
   */
  public function setColour(string $colour) {
    $this->colour = $colour;
  }

  /**
   * @return string|\Drupal\Component\Render\MarkupInterface
   */
  public function getDescription() {
    return $this->description ?? '';
  }

  /**
   * @param string|\Drupal\Component\Render\MarkupInterface $htmlDescription
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * @return string
   */
  public function getShortName(): string {
    return $this->shortName ?? '';
  }

  /**
   * @param string $shortName
   */
  public function setShortName(string $shortName) {
    $this->shortName = $shortName;
  }

}
