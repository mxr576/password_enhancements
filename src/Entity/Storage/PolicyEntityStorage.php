<?php

namespace Drupal\password_enhancements\Entity\Storage;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\password_enhancements\Entity\PolicyInterface;
use Drupal\password_enhancements\Type\QueryOrder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity storage for password constraint config entity.
 */
class PolicyEntityStorage extends ConfigEntityStorage implements PolicyEntityStorageInterface {

  /**
   * Password constraint entity storage.
   *
   * @var \Drupal\password_enhancements\Entity\Storage\ConstraintEntityStorage
   */
  protected $constraintEntityStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, ConfigEntityStorageInterface $constraint_entity_storage, MemoryCacheInterface $memory_cache) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager, $memory_cache);

    $this->constraintEntityStorage = $constraint_entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): PolicyEntityStorageInterface {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')->getStorage('password_enhancements_constraint'),
      $container->get('entity.memory_cache')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function doDelete($entities): void {
    // Remove any related constraints as well.
    foreach ($entities as $entity) {
      $constraints = $this->constraintEntityStorage->loadByProperties([
        'policy' => $entity->id(),
      ]);
      $this->constraintEntityStorage->delete($constraints);
    }

    parent::doDelete($entities);
  }

  /**
   * Gets entity IDs by the given role and order.
   *
   * The entity IDs will be ordered by their priority based on the order
   * property.
   * The list can be limited to a given set of roles.
   *
   * @param array|null $roles
   *   The roles for which the results should be limited.
   * @param \Drupal\password_enhancements\Type\QueryOrder|null $order
   *   The order of the entities.
   *
   * @return string[]
   *   List of entity IDs.
   */
  protected function getEntityIdsByRoleAndPriority(array $roles, ?QueryOrder $order = NULL): array {
    if ($order === NULL) {
      $order = QueryOrder::desc();
    }

    $query = $this->getQuery()
      ->sort('priority', $order);

    // Load entities with specific roles if set.
    if ($roles !== NULL) {
      $query->condition('role', $roles, 'IN');
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function loadByRoleAndPriority(array $roles = NULL, ?QueryOrder $order = NULL): ?PolicyInterface {
    $entities = $this->getEntityIdsByRoleAndPriority($roles, $order);
    return !empty($entities) ? $this->load(reset($entities)) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleByRoleAndPriority(array $roles = NULL, ?QueryOrder $order = NULL): array {
    return $this->loadMultiple($this->getEntityIdsByRoleAndPriority($roles, $order));
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity): int {
    // Generate ID automatically for the entity.
    $entity->id = $entity->getRole();

    return parent::save($entity);
  }

}
