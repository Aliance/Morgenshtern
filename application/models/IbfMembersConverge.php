<?php
class Application_Model_IbfMembersConverge extends Morgenshtern_Db_Table_Abstract 
{
	protected $_name = 'ibf_members_converge';
	protected $_referenceMap    = array(
        'password' => array(
            'columns'           => 'converge_id',
            'refTableClass'     => 'Application_Model_IbfMembers',
            'refColumns'        => 'id'
        )
    );
}