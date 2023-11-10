<?php

namespace Drupal\commerce_coupon_conditions\Entity;

use Drupal\commerce\ConditionGroup;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface;
use Drupal\commerce\Plugin\Commerce\Condition\ParentEntityAwareInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_promotion\Entity\Coupon as CommerceCoupon;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Class Coupon.
 *
 * @package Drupal\commerce_coupon_conditions\Entity
 */
class Coupon extends CommerceCoupon implements CouponInterface {

  /**
   * {@inheritdoc}
   */
  public function setConditions(array $conditions) {
    $this->set('conditions', []);
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionInterface) {
        $this->get('conditions')->appendItem([
          'target_plugin_id' => $condition->getPluginId(),
          'target_plugin_configuration' => $condition->getConfiguration(),
        ]);
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    $conditions = [];
    foreach ($this->get('conditions') as $field_item) {
      /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItemInterface $field_item */
      $condition = $field_item->getTargetInstance();
      if ($condition instanceof ParentEntityAwareInterface) {
        $condition->setParentEntity($this);
      }
      $conditions[] = $condition;
    }
    return $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditionOperator() {
    return $this->get('condition_operator')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditionOperator($condition_operator) {
    $this->set('condition_operator', $condition_operator);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function available(OrderInterface $order) {
    $available = parent::available($order);

    // If parent returns TRUE, check conditions.
    if ($available) {
      return $this->applies($order);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(OrderInterface $order) {

    $conditions = $this->getConditions();
    if (!$conditions) {
      // Promotions without conditions always apply.
      return TRUE;
    }
    // Filter the conditions just in case there are leftover order item
    // conditions (which have been moved to offer conditions).
    $conditions = array_filter($conditions, function ($condition) {
      /** @var \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface $condition */
      return $condition->getEntityTypeId() == 'commerce_order';
    });
    $condition_group = new ConditionGroup($conditions, $this->getConditionOperator());

    return $condition_group->evaluate($order);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['conditions'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_condition')
      ->setLabel(t('Conditions'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_conditions',
        'weight' => 6,
        'settings' => [
          'entity_types' => ['commerce_order'],
        ],
      ]);

    $fields['condition_operator'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Condition operator'))
      ->setDescription(t('The condition operator.'))
      ->setRequired(TRUE)
      ->setSetting('allowed_values', [
        'AND' => t('All conditions must pass'),
        'OR' => t('Only one condition must pass'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue('AND');

    return $fields;
  }

}
