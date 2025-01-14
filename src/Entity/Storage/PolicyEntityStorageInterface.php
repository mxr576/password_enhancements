<?php

namespace Drupal\password_enhancements\Entity\Storage;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\password_enhancements\Entity\PolicyInterface;
use Drupal\password_enhancements\Type\QueryOrder;

/**
 * Defines required methods for the password policy entity storage.
 */
interface PolicyEntityStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Loads a password policy entity by role and priority.
   *
   * @param array $roles
   *   Role IDs.
   * @param \Drupal\password_enhancements\Type\QueryOrder|null $order
   *   The order of the password policies.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface
   *   A password policy entity based on the order, if ordered by 'desc' then it
   *   will return a policy with the highest priority from the given set of
   *   rules, otherwise a password policy with the lowest priority will be
   *   returned.
   */
  public function loadByRoleAndPriority(array $roles = NULL, QueryOrder $order = NULL): ?PolicyInterface;

  /**
   * Load password policy entities by role and priority.
   *
   * @param string[]|null $roles
   *   Role IDs.
   * @param \Drupal\password_enhancements\Type\QueryOrder|null $order
   *   The order of the password policies.
   *
   * @return \Drupal\password_enhancements\Entity\PolicyInterface[]
   *   Password policy entities ordered by the priority. If $order is 'desc'
   *   then the policies will be ordered from highest to lowest based on the
   *   given set of roles, otherwise it will be ordered from lowest to highest.
   */
  public function loadMultipleByRoleAndPriority(array $roles = NULL, QueryOrder $order = NULL): array;

}
