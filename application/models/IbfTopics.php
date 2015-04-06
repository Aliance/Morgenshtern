<?php
class Application_Model_IbfTopics extends Morgenshtern_Db_Table_Abstract 
{
    protected $_name = 'ibf_topics';

    public function getTopicList( $cat, $page = 1 )
    {
        $forums = $this->_getForumIds( $cat );

        $topics = new stdClass;
        $topics->rows = array();

        if ( count( $forums ) == 0 ) {
            return $topics;
        }
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( array( 't' => 'ibf_topics' ) )
               ->joinLeft( array( 'p' => 'ibf_posts' ), 't.topic_firstpost = p.pid' )
               ->joinLeft( array( 'm' => 'ibf_members' ), 'm.id = p.author_id', array( 'member_id' => 'id', 'member_name' => 'members_display_name', 'mgroup', 'email' ) )
               ->joinLeft( array( 'f' => 'ibf_forums' ), 't.forum_id = f.id', array( 'forum_id' => 'id', 'forum_name' => 'name', 'use_html' ) )
               ->where( 't.approved = 1' )
               ->where( 't.forum_id IN (' . implode( ', ', $forums ) . ')' )
               ->where( 't.state != "link"' )
               ->order( 't.pinned DESC')
               ->order( 't.start_date DESC' );
        
        $adapter = new Zend_Paginator_Adapter_DbTableSelect($select);

        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(5);
        $paginator->setPageRange(10);
        $topics->paginator = $paginator;
        
        $char = new Morgenshtern_Char();
        foreach ( $paginator->getCurrentItems() as $row )
        {
            $topic = array();
            $topic['starter_name'] = $char->format( $row['starter_name'] );
            $start_date = new Zend_Date( $row['start_date'] );
            $topic['start_date']  = $start_date->toString( 'd M Y H:i' );
            $topic['title'] = $this->convert( $row['title'] );
            $topic['forum_name'] = $this->convert( $row['forum_name'] );
            if ( $row['pinned'] == 1 ) {
                $topic['news_title'] = sprintf( 'Важно: %s — %s', $row['title'], $row['forum_name'] );
            } else {
                $topic['news_title'] = sprintf( '%s — %s', $row['title'], $row['forum_name'] );
            }
            $topic['link-topic']  = 'http://forum.morgenshtern.com/index.php?showtopic=' . $row['tid'];
            $topic['link-forum']  = 'http://forum.morgenshtern.com/index.php?showforum=' . $row['forum_id'];
            $topic['posts'] = $row['posts'];
            $topic['post'] = $this->convert( $row['post'] );
            $re = '#\[expand\] (.*?) \[/expand\]#xmi';
            $topic['post'] = preg_replace( $re, '<div class="expand">Показать скрытый текст</div><div style="display: none">\\1</div>', $row['post'] );
            
            array_push( $topics->rows, $topic );
        }

        return $topics;
    }
    protected function _getForumIds( $cat = 'all' )
    {
        $forums = array();
        switch ( $cat )
        {
            case 'all':
            default:
                $forums = array( 23, 32, 33, 34, 35, 52 );
                break;
            case 'innovation':
                $forums = array( 32 );
                break;
            case 'analytics':
                $forums = array( 33 );
                break;
            case 'review':
                $forums = array( 34 );
                break;
            case 'cnf':
                $forums = array( 35 );
                break;
            case 'miscellaneous':
                $forums = array( 23 );
                break;
            case 'holidays':
                $forums = array( 52 );
                break;
        }
        return $forums;
    }
}
