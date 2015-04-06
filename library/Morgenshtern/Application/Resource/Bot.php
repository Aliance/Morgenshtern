<?php
class Morgenshtern_Application_Resource_Bot 
    extends Zend_Application_Resource_ResourceAbstract
{
    protected $_bot     = null;

    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $options = $this->getOptions();
		
		$manager = $bootstrap->getResource( 'CacheManager' );
		$cache = $manager->getCache( 'main' );
		
		$logger = $bootstrap->getResource( 'Logger' );
		
		$this->_bot = new Morgenshtern_Bot();
		$this->_bot->setCache( $cache );
		$this->_bot->setLogger( $logger );

        return $this->_bot;
    }

    /**
     * Overloading: intercept calls to init<botname>() methods
     *
     * @param  string $method
     * @param  array  $args
     * @return Morgenshtern_Bot
     * @throws Morgenshtern_Bot_Exception On invalid method name
     */
    public function __call( $method, $args )
    {
        if ( 4 < strlen( $method ) && 'init' === substr( $method, 0, 4 ) ) {
            $bot = strtolower( substr( $method, 4 ) );
			$options = $this->_options['chars'][ $bot ];
			$options['cookiejar'] = $this->_options['cookie_path'] . '/' . $bot . '.cookies.txt';
            return $this->_bot->init( $options );
        }

        throw new Morgenshtern_Bot_Exception( 'Invalid init method "' . $method . '"' );
    }

    /**
     * Initialize and return a random bot
     *
     * @return Morgenshtern_Bot
     */
    public function getRandomBot()
    {
        $botCount = count( $this->_options['chars'] );
		$rndIndex = mt_rand( 0, $botCount - 1 );
		$botConfig = array_slice( $this->_options['chars'], $rndIndex, 1, true );
        $bot = key( $botConfig );

		$options = $this->_options['chars'][ $bot ];
		$options['cookiejar'] = $this->_options['cookie_path'] . '/' . $bot . '.cookies.txt';
		return $this->_bot->init( $options );
    }
}