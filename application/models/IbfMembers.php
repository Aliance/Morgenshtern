<?php
class Application_Model_IbfMembers extends Morgenshtern_Db_Table_Abstract 
{
	protected $_name = 'ibf_members';
    protected $_dependentTables = array( 'Application_Model_IbfMembersConverge', 'Application_Model_IbfMorgenshternMembers', 'Application_Model_Api' );

	public function getMember( $name )
	{
		$user = $this->fetchRow( array( 'name = ?' => $name ) );
		$converge = $user->findDependentRowset( 'Application_Model_IbfMembersConverge', 'password' )->current();
		return array_merge( $user->toArray(), $converge->toArray() );
	}

	public function getStaffMembers()
	{
		$rows = $this->fetchAll( 'mgroup IN(6,7,8)' );
		$staff = array();
		foreach ( $rows as $row ) {
			$member = array(
				'id'   => $row['id'],
				'nick' => $this->convert( $row['name'] )
			);
			array_push( $staff, $member );
		}
		return $staff;
	}

	/**
	 *
	 *
	 * @param  integer $userId
	 * @throws Zend_Exception
	 * @return array
	 */
	public function getValues4API( $userId )
	{
		if ( ! is_integer( $userId ) ) {
			throw new Zend_Exception( 'Первый аргумент должен быть числом!' );
		}
		$row = $this->find( $userId )->current();
		if ( null === ( $result = $row->findDependentRowset( 'Application_Model_IbfMorgenshternMembers', 'user' )->current() ) ) {
			throw new Zend_Exception( 'Наша система ещё не успела добавить информацию о Вашем чаре в базу данных.', -1 );
		}
		return array(
			'id'   => $result['member_id'],
			'nick' => $result['nick'],
			'clan' => $result['clan']
		);
	}
}