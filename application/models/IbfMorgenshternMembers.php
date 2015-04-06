<?php
class Application_Model_IbfMorgenshternMembers extends Morgenshtern_Db_Table_Abstract 
{
	protected $_name = 'ibf_morgenshtern_members';
	protected $_referenceMap    = array(
        'user' => array(
            'columns'           => 'id',
            'refTableClass'     => 'Application_Model_IbfMembers',
            'refColumns'        => 'id'
        )
    );

	public function getStaff()
	{
		$char = new Morgenshtern_Char;
		$staff = array();
		$membersModel = new Application_Model_IbfMembers;
		$members = $membersModel->getStaffMembers();
		foreach ( $members as $member ) {
			$info = $this->fetchRow( 'member_id = ' . (int) $member['id'] );
			$data = array(
				'nick'     => $char->format( (int) $member['id'] ),
				'raw_nick' => $this->convert( $member['nick'] ),
				'rank'     => $this->convert( $info['rank'] ),
				'room'     => $this->convert( $info['room'] ),
				'battle'   => $info['battle'],
				'city'     => $char->formatCity( $info['city'] ),
				'online'   => $info['online']
			);
			if ( ! empty( $info['battle'] ) ) {
				$data['battle'] = $char->formatBattle( $info['battle'], $info['city'] );
			}
			array_push( $staff, $data );
		}
		return $staff;
	}

	public function getOnlineStaff()
	{
		$char = new Morgenshtern_Char;
		$staff = array();
		$membersModel = new Application_Model_IbfMembers;
		$members = $membersModel->getStaffMembers();
		foreach ( $members as $member ) {
			$info = $this->fetchRow( 'member_id = ' . (int) $member['id'] );
			if ( ! $info['online'] ) {
				continue;
			}
			$data = array(
				'nick'     => $char->format( (int) $member['id'] ),
				'raw_nick' => $this->convert( $member['nick'] ),
				'rank'     => $this->convert( $info['rank'] ),
				'room'     => $this->convert( $info['room'] ),
				'battle'   => $info['battle'],
				'city'     => $char->formatCity( $info['city'] ),
				'online'   => $info['online']
			);
			if ( ! empty( $info['battle'] ) ) {
				$data['battle'] = $char->formatBattle( $info['battle'], $info['city'] );
			}
			array_push( $staff, $data );
		}
		return $staff;
	}

	public function getOnlineClanStaff( $clan )
	{
		$char = new Morgenshtern_Char;
		$staff = array();
		$members = $this->fetchAll( 'clan = ' . $this->getAdapter()->quote( $clan ) );
		foreach ( $members as $member ) {
			if ( ! $member['online'] ) {
				continue;
			}
			$data = array(
				'nick'     => $char->format( $member['nick'] ),
				'raw_nick' => $this->convert( $member['nick'] ),
				'rank'     => $this->convert( $member['rank'] ),
				'room'     => $this->convert( $member['room'] ),
				'battle'   => $member['battle'],
				'city'     => $char->formatCity( $member['city'] ),
				'online'   => $member['online']
			);
			if ( ! empty( $member['battle'] ) ) {
				$data['battle'] = $char->formatBattle( $member['battle'], $member['city'] );
			}
			array_push( $staff, $data );
		}
		return $staff;
	}
	public function updateChar( $id, $info )
	{
		$data = array(
			'member_id'     => $id,
			'nick'          => $this->convert( $info['nick'], true ),
			'level'         => $info['level'],
			'city'          => $info['city'],
			'vicrory'       => $info['vicrory'],
			'defeat'        => $info['defeat'],
			'withdraw'      => $info['withdraw']
		);
		$data['clan'] = isset( $info['clan'] ) ? $info['clan'] : '';
		$data['align'] = isset( $info['align'] ) ? $info['align'] : 0;
		$data['online'] = isset( $info['online'] ) ? 1 : 0;
		$data['room'] = isset( $info['room'] ) ? $this->convert( $info['room'], true ) : '';
		$data['rank'] = isset( $info['rank'] ) ? $this->convert( $info['rank'], true ) : '';
		$data['battle'] = isset( $info['battle'] ) ? $info['battle'] : '';
		$data['name'] = isset( $info['name'] ) ? $this->convert( $info['name'], true ) : '';
		if ( isset( $info['pet_type'] ) ) {
			$data['pet_type'] = $info['pet_type'];
			$data['pet_name'] = $this->convert( $info['pet_name'], true );
			$data['pet_level'] = $info['pet_level'];
		} else {
			$data['pet_type'] = '';
			$data['pet_name'] = '';
			$data['pet_level'] = '';
		}
		$data['scrolls'] = isset( $info['scrolls'] ) ? $info['scrolls'] : '';
		try {
			$date_registry = new Zend_Date( $info['date_registry'] );
			#$ftime = strptime( $info['date_registry'], '%d.%m.%y %H:%M' );
			#$timestamp = mktime( $ftime['tm_hour'], $ftime['tm_min'], $ftime['tm_sec'], 1, $ftime['tm_yday'] + 1, $ftime['tm_year'] + 1900 ); 
			$_data = array(
				'char_id'       => $info['id'],
				'native_city'   => $info['birthplace'],
				'date_registry' => $date_registry->toString( 'Y-m-d H:i:s' ),
				#'date_registry' => date( 'Y-m-d H:i:s', $timestamp ),
				'sex'           => $info['sex']
			);
			$dataInsert = array_merge( $data, $_data );
			$this->insert( $dataInsert );
		} catch ( Zend_Db_Statement_Exception $e ) {
			if ( $id == 0 ) {
				$where = 'nick = ' . $this->getAdapter()->quote( $data['nick'] );
				unset( $data['nick'] );
			} else {
				$where = 'member_id = ' . (int) $id;
			}
			$this->update( $data, $where );
		}
	}
}