<?php
final class ApiController extends Zend_Controller_Action
{
    protected $_model = null;
    protected $_bot = null;
    protected $_options = array();

	protected function _getUserId()
	{
		// temp
		return 1;

		$bootstrap = $this->getInvokeArg( 'bootstrap' );
		$auth = $bootstrap->getResource( 'Auth' );
		return (int) $auth->getIdentity()->user_id;
	}
	public function init()
    {
        $this->_options = array(
            'login'     => 'Morg Sun',
            'password'  => 'R59QjIt1Qz',
            'city'      => 'sun',
            'cookiejar' => APPLICATION_PATH . '/data/bot/sun.cookies.txt'
        );
        $this->_bot = new Morgenshtern_Bot( $this->_options );
		$this->_model = new Application_Model_Api();
    }
	public function indexAction() 
    {
		$this->view->headTitle()->prepend( 'API' );
		$this->view->headLink()->appendStylesheet( '/css/api.index.css', 'screen' );
    }
	public function registrationAction() 
    {
		$this->view->headTitle()->prepend( 'Регистрация API для клана' );
		$this->view->headLink()->appendStylesheet( '/css/global.messages.css', 'screen' );
		#$this->view->headLink()->appendStylesheet( '/css/api.registration.css', 'screen' );

		$userId = $this->_getUserId();
		if ( $this->_model->isVerified( $userId ) ) {
			$this->_redirect( '/api/verified' );
		}

		$userModel = new Application_Model_IbfMembers();
		$this->view->char = new Morgenshtern_Char();
		try {
			$this->view->values = $userModel->getValues4API( $userId );
		} catch ( Zend_Exception $e ) {
			if ( $e->getCode() === -1 ) {
				// тут стоит вызвать обновление чара
			}
			$this->getResponse()->setBody( '<div class="warn">' . $e->getMessage() . '</div>' );
			$this->_helper->viewRenderer->setNoRender();
		}
    }
	public function joinAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$referer = $this->getRequest()->getHeader( 'REFERER' );

		$scheme = $this->getRequest()->getScheme();
		$host = $this->getRequest()->getHttpHost();
		$expected = $scheme . '://' . $host . '/api/registration';

		if ( $referer != $expected ) {
			throw new Zend_Exception( 'Invalid referrer' );
		}

		$userId = $this->_getUserId();
		if ( ! $this->_model->isVerified( $userId ) ) {
			$userModel = new Application_Model_IbfMembers();
			$values = $userModel->getValues4API( $userId );

			$data = array(
				'api_user_id' => $values['id'],
				'api_key'     => md5( $values['nick'] . mt_rand() ),
				'api_ip'      => $this->getRequest()->getClientIp(),
				'api_clan'    => $values['clan']
			);

			$this->_model->insert( $data );
		}

		$this->_redirect( '/api/verified' );
	}
	public function verifiedAction()
	{
		$this->view->headLink()->appendStylesheet( '/css/global.messages.css', 'screen' );
		$this->view->headTitle()->prepend( 'Управление API' );
		
		$userId = $this->_getUserId();
		if ( ! $this->_model->isVerified( $userId ) ) {
			$this->_redirect( '/api/registration' );
		}

		$userModel = new Application_Model_IbfMembers();
		$values = $userModel->getValues4API( $userId );
		$values = $this->_model->mergeValues( $userId, $values );
		$this->view->values = $values;
	}
	public function agreementAction() 
    {
		$this->view->headTitle()->prepend( 'Пользовательское соглашение на использование кланового API' );
		#$this->view->headLink()->appendStylesheet( '/css/api.agreement.css', 'screen' );
    }
    public function staffAction()
    {
        $this->_helper->layout->disableLayout();

        $format = $this->_request->getParam( 'format', 'xml' );
        if ( null === ( $clan = $this->_request->get( 'clan' ) ) ) {
            throw new Zend_Exception( 'Не передан обязательный параметр.' );
        }
        if ( null === ( $key = $this->_request->get( 'key' ) ) ) {
            throw new Zend_Exception( 'Не передан обязательный параметр.' );
        }

        $this->_bot->ping();
        $staff = $this->_bot->getClanStaff( $clan );

        switch ( strtolower( $format ) ) {
            case 'xml':
                $this->getResponse()->setHeader( 'Content-Type', 'text/xml; charset=utf-8' );
                $dom = new DOMDocument( '1.0', 'utf-8' );
                $root = $dom->createElement( 'clan' );
                $dom->appendChild( $root );
                $root->appendChild( $dom->createElement( 'title', htmlentities( $clan ) ) );
                $staffNode = $dom->createElement( 'staff' );
                $root->appendChild( $staffNode );
                foreach ( $staff as $char ) {
                    $charNode = $dom->createElement( 'char' );
					/*
					if ( @$char['online'] ) {
						$onlineAttr = $dom->createAttribute( 'online' );
						$onlineAttrText = $dom->createTextNode( 'true' );
						$onlineAttr->appendChild( $onlineAttrText );
						$charNode->appendChild( $onlineAttr );
					}
					*/
                    $charNode->appendChild( $dom->createElement( 'id', intval( $char['id'] ) ) );
                    $charNode->appendChild( $dom->createElement( 'nick', htmlspecialchars( iconv( 'windows-1251', 'utf-8', $char['nick'] ) ) ) );
                    $charNode->appendChild( $dom->createElement( 'level', intval( $char['level'] ) ) );
                    $charNode->appendChild( $dom->createElement( 'city', htmlspecialchars( $char['city'] ) ) );
					$rank = empty( $char['rank'] ) ? '': iconv( 'windows-1251', 'utf-8', $char['rank'] );
                    $charNode->appendChild( $dom->createElement( 'rank', htmlspecialchars( $rank ) ) );
                    $online = empty( $char['online'] ) ? 'false' : 'true';
					$charNode->appendChild( $dom->createElement( 'online', $online ) );
                    $room = empty( $char['room'] ) ? '' : iconv( 'windows-1251', 'utf-8', $char['room'] );
					$charNode->appendChild( $dom->createElement( 'room', $room ) );
                    $battle = empty( $char['battle'] ) ? '' : $char['battle'];
					$charNode->appendChild( $dom->createElement( 'battle', $battle ) );
                    $staffNode->appendChild( $charNode );
                }
                print( $dom->saveXML() );
            break;
            case 'xml':
                throw new Zend_Exception( 'json format is out of service right now. it will be accepted soon.' );
            break;
            default:
                throw new Zend_Exception( 'Данный формат не поддерживается.' );
            break;
        }
    }
}