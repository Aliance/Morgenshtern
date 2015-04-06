<?php
/**
 * EMERG   = 0;  // Emergency: system is unusable
 * ALERT   = 1;  // Alert: action must be taken immediately
 * CRIT    = 2;  // Critical: critical conditions
 * ERR     = 3;  // Error: error conditions
 * WARN    = 4;  // Warning: warning conditions
 * NOTICE  = 5;  // Notice: normal but significant condition
 * INFO    = 6;  // Informational: informational messages
 * DEBUG   = 7;  // Debug: debug messages
 * BOT     = 8;  // Bot: clan bot messages
 */
class Morgenshtern_Application_Resource_Logger 
    extends Zend_Application_Resource_ResourceAbstract
{
    protected $_logger = null;
    protected $_options = array(
        'table'         => 'log',
        'columnMapping' => array(
            'log_level'     => 'priority',
            'log_message'   => 'message',
            'log_timestamp' => 'timestamp',
            'log_url'       => 'uri',
            'log_ip'        => 'ip',
            'log_user_id'   => 'user'
        )
    );

    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $options = $this->getOptions();

        $bootstrap->bootstrap( 'DB' );
        $db = $bootstrap->getResource( 'DB' );

        $bootstrap->bootstrap( 'Auth' );
		$auth = $bootstrap->getResource( 'Auth' );

        $writer = new Zend_Log_Writer_Db( $db, $options['table'], $options['columnMapping'] );
        $logger = new Zend_Log( $writer );

        $ip = $this->getClientIp( true );
        $logger->setEventItem( 'ip', $ip );

        $userId = $auth->hasIdentity() ? $auth->getIdentity()->id : 0;
        $logger->setEventItem( 'user', $userId );

        $logger->setEventItem( 'uri', $this->getServer( 'REQUEST_URI' ) );
		
		$logger->addPriority( 'BOT', 8 );

        $this->_logger = $logger;
        return $this->_logger;
    }
	
	public function getLogger()
	{
		if ( null === $this->_logger ) {
			$this->init();
		}
		return $this->_logger;
	}

    /**
     * Get the client's IP addres
     *
     * @param  boolean $checkProxy
     * @return string
     */
    public function getClientIp($checkProxy = true)
    {
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->getServer('REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
}
