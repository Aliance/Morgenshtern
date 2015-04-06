<?php
/**
 * API модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_Api extends Zend_Db_Table_Abstract {
    protected $_name = 'api';
	protected $_referenceMap    = array(
        'user' => array(
            'columns'           => 'api_user_id',
            'refTableClass'     => 'Application_Model_IbfMembers',
            'refColumns'        => 'id'
        )
    );

	/**
	 *
	 *
	 * @param  integer $userId
	 * @throws Zend_Exception
	 * @return boolean
	 */
	public function isVerified( $userId )
	{
		if ( ! is_integer( $userId ) ) {
			throw new Zend_Exception( 'Первый аргумент должен быть числом!' );
		}
		$where = array( 'api_user_id = ?' => $userId );
		if ( null !== ( $result = $this->fetchRow( $where ) ) ) {
			if ( null === ( $row = $result->findParentRow( 'Application_Model_IbfMembers', 'user' ) ) ) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 *
	 * @param  integer $userId
	 * @param  array $values
	 * @throws Zend_Exception
	 * @return array
	 */
	public function mergeValues( $userId, $values )
	{
		if ( ! is_integer( $userId ) ) {
			throw new Zend_Exception( 'Первый аргумент должен быть числом!' );
		}
		if ( ! is_array( $values ) ) {
			throw new Zend_Exception( 'Первый аргумент должен массивом!' );
		}
		$where = array( 'api_user_id = ?' => $userId );
		if ( null === ( $result = $this->fetchRow( $where ) ) ) {
			return $values;
		} else {
			$values2merge = array(
				'key'      => $result['api_key'],
				'approved' => $result['api_approved']
			);
			return array_merge( $values, $values2merge );
		}
	}
}