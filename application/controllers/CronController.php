<?php
class CronController extends Zend_Controller_Action 
{
    protected $_cache = null;
    protected $_logger = null;
    protected $_bot = null;
    protected $_botPlugin = null;
    public function preDispatch()
    {
        error_reporting( E_ALL );
        set_time_limit( 120 );
    }
    public function init()
    {
        $bootstrap = $this->getInvokeArg( 'bootstrap' );
        $manager = $bootstrap->getResource( 'CacheManager' );
        $this->_cache = $manager->getCache( 'block' );
        $this->_logger = $bootstrap->getResource( 'Logger' );
        $this->_bot = $bootstrap->getResource( 'Bot' );
        $this->_botPlugin = $bootstrap->getPluginResource( 'Bot' );

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
    }
    public function clansAction()
    {
        $options = array(
           #CURLOPT_URL            => 'http://suncity.combats.com/clans_inf.pl?allclans',
            CURLOPT_URL            => 'http://capitalcity.combats.com/encicl/clans.html',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE     => $this->_bot->getCookiejar()
        );
        $page = $this->_bot->curlExec( $options );

        $re = '#<li><img src="http:\/\/img.combats.com\/i\/align(?:[[:digit:][:punct:]]*).gif" alt="" width="12" height="15">\n<img src="http:\/\/img.combats.com\/i\/klan\/([[:alpha:]]+).gif" alt="" width="24" height="15">\n&nbsp;&nbsp;<b><a href="klan\/(?:\1).html">(?:\1)<\/a><\/b><\/li>#';

        if ( preg_match_all( $re, $page, $matches ) ) {
        
            $clansModel = new Application_Model_Clans;
            array_shift( $matches );
            foreach ( $matches[0] as $clan ) {
                $data = array(
                    'clan' => trim( $clan )
                );
                try {
                    $clansModel->insert( $data );
                    $this->_logger->info( 'Добавлен новый клан: ' . $data['clan'] );
                } catch ( Zend_Db_Statement_Exception $e ) {
                    continue;
                }
            }

        }
    }
    public function staffAction()
    {
		$membersModel = new Application_Model_IbfMembers;
		$staffModel = new Application_Model_IbfMorgenshternMembers;
		$members = $membersModel->getStaffMembers();
		foreach ( $members as $member ) {
			try {
				$info = $this->_bot->getCharInfo( $member['nick'] );
			} catch ( Morgenshtern_Bot_Exception $e ) {
				$this->_logger->bot( $e->getMessage() );
				continue;
			} catch ( Exception $e ) {
				continue;
			}
			$staffModel->updateChar( $member['id'], $info );
		}
    }
	public function alliesAction()
    {
		$membersModel = new Application_Model_IbfMembers;
		$staffModel = new Application_Model_IbfMorgenshternMembers;
		$diplomacyModel = new Application_Model_Diplomacy;
		$allies = $diplomacyModel->getAllies();
		foreach ( $allies as $clan ) {
			try {
				$staff = $this->_bot->getClanStaff( $clan['clan'] );
			} catch ( Morgenshtern_Bot_Exception $e ) {
				$this->_logger->bot( $e->getMessage() );
				continue;
			} catch ( Exception $e ) {
				continue;
			}
			foreach ( $staff as $char ) {
				if ( null === ( $row = $membersModel->getMember( $char['nick'] ) ) ) {
					$staffModel->updateChar( 0, $char );
				} else {
					$staffModel->updateChar( $row['id'], $char );
				}
			}
		}
    }
	public function charsAction()
    {
		$staffModel = new Application_Model_IbfMorgenshternMembers;
		$rows = $staffModel->fetchAll( null, 'added ASC', 10 );
		foreach( $rows as $char ) {
			try {
				$nick = iconv( 'windows-1251', 'utf-8//IGNORE', $char['nick'] );
				$info = $this->_bot->getCharInfo( $nick );
			} catch ( Morgenshtern_Bot_Exception $e ) {
				$this->_logger->bot( $e->getMessage() );
				continue;
			} catch ( Exception $e ) {
				continue;
			}
			$staffModel->updateChar( $char['member_id'], $info );
		}
    }
	public function pingBotAction()
	{
		$bots = array( 'capital', 'angels', 'sun', 'sand', 'moon', 'dreams', 'old' );
		foreach ( $bots as $login ) {
			$char = ucfirst( $login );
			$method = 'init' . $char;
			$bot = $this->_botPlugin->{$method}();
			try {
				$bot->update();
			} catch ( Morgenshtern_Bot_Exception $e ) {
				$this->_logger->bot( 'Cron error: ' . $e->getMessage() . ' [' . $login . ']' );
			}
		}
	}
}



/*
array(8) {
  [7] => array(17) {
    ["chilvary"] => array(5) {
      [0] => array(3) {
        ["title"] => string(11) "Angels city"
        ["dsc"] => string(38) "Рыцарь первого круга"
        ["img"] => string(14) "misc/zn2_1.gif"
      }
      [1] => array(3) {
        ["title"] => string(11) "Demons city"
        ["dsc"] => string(38) "Рыцарь первого круга"
        ["img"] => string(14) "misc/zn3_1.gif"
      }
      [2] => array(3) {
        ["title"] => string(13) "Emeralds city"
        ["dsc"] => string(38) "Рыцарь первого круга"
        ["img"] => string(14) "misc/zn6_1.gif"
      }
      [3] => array(3) {
        ["title"] => string(8) "Mooncity"
        ["dsc"] => string(38) "Рыцарь первого круга"
        ["img"] => string(14) "misc/zn9_1.gif"
      }
      [4] => array(3) {
        ["title"] => string(21) "Храм Знаний"
        ["dsc"] => string(48) "Посвященный первого круга"
        ["img"] => string(17) "misc/znrune_1.gif"
      }
    }
  }
}
*/