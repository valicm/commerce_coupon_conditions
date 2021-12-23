<?php

namespace Drupal\commerce_coupon_conditions\Entity;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\commerce_promotion\Entity\CouponInterface as CommerceCouponInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides an interface for defining coupon entities.
 */
interface CouponInterface extends CommerceCouponInterface {

  /**
   * Gets the conditions.
   *
   * @return \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[]
   *   The conditions.
   */
  public function getConditions();

  /**
   * Sets the conditions.
   *
   * @param \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[] $conditions
   *   The conditions.
   *
   * @return $this
   */
  public function setConditions(array $conditions);

  /**
   * Gets the condition operator.
   *
   * @return string
   *   The condition operator. Possible values: AND, OR.
   */
  public function getConditionOperator();

  /**
   * Sets the condition operator.
   *
   * @param string $condition_operator
   *   The condition operator.
   *
   * @return $this
   */
  public function setConditionOperator($condition_operator);

  /**
   * Checks whether the coupon can be applied to the given order.
   *
   * Ensures that the promotion is compatible with other
   * promotions on the order, and that the conditions pass.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return bool
   *   TRUE if coupon can be applied, FALSE otherwise.
   */
  public function applies(OrderInterface $order);

}
