<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Catalog;
use Drupal\ckan\Entity\Resource;
use Drupal\ckan\Entity\User;
use Drupal\ckan\User\CkanUserInterface;

/**
 *
 */
interface CkanRequestInterface {

  /**
   * Set the ckanUser which will be used for the apiKey.
   *
   * @param \Drupal\ckan\User\CkanUserInterface|null $ckanUser
   *
   * @return CkanRequestInterface
   */
  public function setCkanUser(CkanUserInterface $ckanUser = NULL);

  /**
   * Retrieve a specific dataset.
   *
   * @param string $datasetId
   *
   * @return \Drupal\ckan\Entity\Dataset|null
   */
  public function getDataset($datasetId): ?Dataset;

  /**
   * Retrieve a rdf data for a specific dataset.
   *
   * @param string $datasetId
   *
   * @return string|null
   *   A string with the complete rdf data or NULL on failure.
   */
  public function getDatasetAsRdf($datasetId);

  /**
   * @param string $userId
   * @param int|null $page
   * @param int|null $recordsPerPage
   *
   * @return \Drupal\ckan\Entity\Dataset[]|array
   */
  public function getDatasetByUser(string $userId, int $page = NULL, int $recordsPerPage = NULL): array;

  /**
   * Search through all the datasests.
   *
   * @param int $page
   *   The page of records to show.
   * @param int $recordsPerPage
   *   The amount of records to return per page.
   * @param string $search
   *   The search term to filter the results with.
   * @param string $sort
   *   The field(s) to sort the results with.
   * @param array $activeFacets
   *   Any active facets to filter the results with.
   * @param array $extraFacets
   *   Any extra facets to add.
   *
   * @return array
   *   Array with all the data required for building the search page.
   */
  public function searchDatasets($page, $recordsPerPage, $search, $sort, array $activeFacets, array $extraFacets = []);

  /**
   * Returns the total number of datasets.
   *
   * @return int
   */
  public function totalDatasets(): int;

  /**
   * Get the themes.
   *
   * @param string|null $communityIdentifier
   *
   * @return array
   */
  public function getThemes($communityIdentifier = NULL): array;

  /**
   * Create a new dataset.
   *
   * @param \Drupal\ckan\Entity\Dataset $dataset
   *
   * @return mixed
   */
  public function createDataset(Dataset $dataset);

  /**
   * Update a dataset.
   *
   * @param \Drupal\ckan\Entity\Dataset $dataset
   *
   * @return mixed
   */
  public function updateDataset(Dataset $dataset);

  /**
   * Delete a specific dataset.
   *
   * @param string $datasetId
   *
   * @return mixed
   */
  public function deleteDataset($datasetId);

  /**
   * Retrieve a specific resource.
   *
   * @param string $resourceId
   *
   * @return \Drupal\ckan\Entity\Resource|null
   */
  public function getResource($resourceId);

  /**
   * Create a new resource.
   *
   * @param \Drupal\ckan\Entity\Resource $resource
   *
   * @return mixed
   */
  public function createResource(Resource $resource);

  /**
   * Update a resource.
   *
   * @param \Drupal\ckan\Entity\Resource $resource
   *
   * @return mixed
   */
  public function updateResource(Resource $resource);

  /**
   * Delete a specific resource.
   *
   * @param string $resourceId
   *
   * @return mixed
   */
  public function deleteResource($resourceId);

  /**
   * Retrieve a specific catalog.
   *
   * @param string $catalogId
   *
   * @return \Drupal\ckan\Entity\Catalog|null
   */
  public function getCatalog($catalogId);

  /**
   * Retrieve a specific user.
   *
   * @param string $userId
   *
   * @return \Drupal\ckan\Entity\User|null
   */
  public function getUser($userId): ?User;

  /**
   * Update a user.
   *
   * @param \Drupal\ckan\Entity\User $user
   *
   * @return \Drupal\ckan\Entity\User|bool
   */
  public function updateUser(User $user);

  /**
   * Create a user in CKAN.
   *
   * @param \Drupal\ckan\Entity\User $user
   *
   * @return \Drupal\ckan\Entity\User|bool
   */
  public function createUser(User $user);

  /**
   * @return bool
   */
  public function isErrorUserAlreadyExists(): bool;

  /**
   * Delete a user in CKAN.
   *
   * @param \Drupal\ckan\Entity\User $user
   *
   * @return mixed|bool
   */
  public function deleteUser(User $user);

  /**
   * Activate the user in CKAN.
   *
   * @param \Drupal\ckan\Entity\User $user
   * @param string|null $catalog
   *
   * @return \Drupal\ckan\Entity\User|bool
   */
  public function activateUser(User $user, $catalog = NULL);

  /**
   * Block the user in CKAN.
   *
   * @param \Drupal\ckan\Entity\User $user
   *
   * @return \Drupal\ckan\Entity\User|bool
   */
  public function blockUser(User $user);

  /**
   * Retrieve a list of all organization the user is member of.
   *
   * @param \Drupal\ckan\Entity\User $user
   *
   * @return array|\Drupal\ckan\Entity\Catalog[]
   */
  public function getUserOrganizations(User $user): array;

  /**
   * Add a user as member to specific organization.
   *
   * @param \Drupal\ckan\Entity\User $user
   * @param \Drupal\ckan\Entity\Catalog $organization
   * @param string $role
   *
   * @return mixed|bool
   */
  public function organizationAddMember(User $user, Catalog $organization, $role = 'admin');

  /**
   * Remove the membership of a user to a specific organization.
   *
   * @param \Drupal\ckan\Entity\User $user
   * @param \Drupal\ckan\Entity\Catalog $organization
   *
   * @return mixed|bool
   */
  public function organizationRemoveMember(User $user, Catalog $organization);

  /**
   * Return a list with errors from the last request.
   *
   * @return array
   */
  public function getErrors(): array;

}
