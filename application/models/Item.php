<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_Item extends Zend_Db_Table_Abstract {
    protected $_name = 'item';
	protected $_dependentTables = array( 'Application_Model_ItemRequirement', 'Application_Model_ItemModifier', 'Application_Model_ItemOption' );
	public function getItem( $item )
	{
		$where = array(
			'item_title = ?' => $item,
			'item_section_id IN( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 )'
		);
		if ( null === ( $row = $this->fetchRow( $where ) ) ) {
			return false;
		}
		$itemRequirementsRow = $row->findDependentRowset( 'Application_Model_ItemRequirement', 'item' );
		$requirements = array();
		foreach ( $itemRequirementsRow as $requirement ) {
			$requirementsRow = $requirement->findParentRow( 'Application_Model_Requirement', 'requirement' );
			$requirement = array(
				'title' => $requirementsRow['requirement_title'],
				'value' => $requirement['item_requirement_value']
			);
			array_push( $requirements, $requirement );
		}
		$itemModifiersRow = $row->findDependentRowset( 'Application_Model_ItemModifier', 'item' );
		$modifiers = array();
		foreach ( $itemModifiersRow as $modifier ) {
			$modifiersRow = $modifier->findParentRow( 'Application_Model_Modifier', 'modifier' );
			$modifier = array(
				'title' => $modifiersRow['modifier_title'],
				'value' => $modifier['item_modifier_value']
			);
			array_push( $modifiers, $modifier );
		}
		$itemOptionsRow = $row->findDependentRowset( 'Application_Model_ItemOption', 'item' );
		$options = array();
		foreach ( $itemOptionsRow as $option ) {
			$optionsRow = $option->findParentRow( 'Application_Model_Option', 'option' );
			$option = array(
				'title' => $optionsRow['option_title'],
				'value' => $option['item_option_value']
			);
			array_push( $options, $option );
		}
		$item = array(
			'id'           => $row['item_id'],
			'title'        => $row['item_title'],
			'price'        => $row['item_price'],
			'weight'       => $row['item_weight'],
			'durability'   => $row['item_durability'],
			'requirements' => $requirements,
			'modifiers'    => $modifiers,
			'options'      => $options
		);
		return $item;
	}
	public function getItems()
	{
		$rows = $this->fetchAll();
		$items = array();
		foreach ( $rows as $row ) {
			$itemRequirementsRow = $row->findDependentRowset( 'Application_Model_ItemRequirement', 'item' );
			$requirements = array();
			foreach ( $itemRequirementsRow as $requirement ) {
				$requirementsRow = $requirement->findParentRow( 'Application_Model_Requirement', 'requirement' );
				$requirement = array(
					'title' => $requirementsRow['requirement_title'],
					'value' => $requirement['item_requirement_value']
				);
				array_push( $requirements, $requirement );
			}
			$itemModifiersRow = $row->findDependentRowset( 'Application_Model_ItemModifier', 'item' );
			$modifiers = array();
			foreach ( $itemModifiersRow as $modifier ) {
				$modifiersRow = $modifier->findParentRow( 'Application_Model_Modifier', 'modifier' );
				$modifier = array(
					'title' => $modifiersRow['modifier_title'],
					'value' => $modifier['item_modifier_value']
				);
				array_push( $modifiers, $modifier );
			}
			$item = array(
				'id'           => $row['item_id'],
				'title'        => $row['item_title'],
				'price'        => $row['item_price'],
				'weight'       => $row['item_weight'],
				'durability'   => $row['item_durability'],
				'requirements' => $requirements,
				'modifiers'    => $modifiers
			);
			array_push( $items, $item );
		}
		return $items;
	}
}