<?php

/**
 * Abstract class for Morgenshtern_Bot to help enforce private constructs.
 *
 * @category   Morgenshtern
 * @package    Morgenshtern_Bot
 * @copyright  Copyright (c) 2010 Aliance spb (http://www.morgenshtern.com)
 * @license    http://www.gnu.org/copyleft/lesser.html     LGPL
 */
abstract class Morgenshtern_Bot_Abstract implements Morgenshtern_Bot_Interface
{
    /**
     * UserAgent constant
     */
    const IE = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)';

    /**
     * Referer URL constant
     */
    const REFERER = 'http://www.morgenshtern.com';

    /**
     * Validator errors constants
     */
    const REDIRECT              = 'top.location.href="/index.html"';
    const JS_REDIRECT           = "top.location = '/index.html';";
    const CHAT_LINK             = 'Ваше сообщение заблокировано т.к. содержит ссылку на страницу отличную от';
    const NOT_HERE              = 'error=not_here';
    const SERVICE_UNAVAILIABLE  = 'Service Unavailable';
    const INTERNAL_SERVER_ERROR = 'Internal Server Error';
    const IP_BANNED             = 'Ваш IP временно заблокирован';
    const BAD_REQUEST           = 'Bad Request';
    const NO_LOGIN              = 'Не указан логин персонажа';
    const NO_PASSWORD           = 'Укажите пароль';
    const WRONG_PASSWORD        = 'Неверный пароль для';
    const LOGIN_NOT_FOUND       = 'не найден в базе';
    const CHAR_NOT_FOUND        = 'не найден';
    const OTHER_ERROR           = 'Произошла ошибка';

    /**
     * Сервера Бойцовского Клуба
     * @var array
     */
    protected $_servers = array(
        'capital', 'angels', 'demons', 'devils', 'sun', 
        'sand', 'moon', 'emeralds', 'old', 'dreams', 'low'
    );
    
    /**
     * Curl Handler
     * @var resource
     */
    protected $_handler = null;
    
    /**
     * Char login
     * @var string
     */
    protected $_login = null;
    
    /**
     * Char password
     * @var string
     */
    protected $_password = null;
    
    /**
     * Char city
     * @var string
     */
    protected $_city = null;
    
    /**
     * Cookie object
     * @var stdClass
     */
    public $cookie = null;
    
    /**
     * Cookie Jar File
     * @var string
     */
    protected $_cookiejar = null;
    
    /**
     * Cache
     * @var Zend_Cache_Core
     */
    protected $_cache = null;
    
    /**
     * Logger
     * @var Zend_Log
     */
    protected $_logger = null;

    /**
     * Constructor
     *
     * Accepts an array of options
     *
     * @param  array $options
     * @throws Morgenshtern_Bot_Exception if $options is not an array
     * @return Morgenshtern_Bot_Abstract
     */
    public function init( $options = null )
    {
        if ( null !== $options ) {
            if ( is_array( $options ) ) {
                $this->setOptions( $options );
            } else {
                require_once 'Morgenshtern/Bot/Exception.php';
                throw new Morgenshtern_Bot_Exception( 'Options error: 1 parameter must be an array.' );
            }
        }
        return $this;
    }

    /**
     * cUrl Iinitialize
     *
     * Initialize the curl connection
     *
     * @throws Morgenshtern_Bot_Exception if some init errors happend
     * @return resource
     */
    public function curlInit()
    {
        try {
            $handler = curl_init();
        } catch ( Exception $e ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Curl error: cannot init the connection.' );
        }
        $this->_handler = $handler;
        return $this->_handler;
    }

    /**
     * cUrl exec
     *
     * Execute the curl connection
     *
     * @param  array $options
     * @throws Morgenshtern_Bot_Exception if $options is not an array
     * @return string
     */
    public function curlExec( $options = array() )
    {
        if ( ! is_array( $options ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'cUrl exec error: 1 parameter must be an array.' );
        }
        if ( null === $this->_handler ) {
            $this->curlInit();
        }
        $defaults = array(
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT      => self::IE,
            CURLOPT_REFERER        => self::REFERER,
            CURLOPT_TIMEOUT        => 5
        );
        foreach ( $defaults as $constant => $value ) {
            if ( ! array_key_exists( $constant, $options ) ) {
                $options[ $constant ] = $value;
            }
        }
        curl_setopt_array( $this->_handler, $options );

        $page = curl_exec( $this->_handler );
        $page = $this->validate( $page, $options );

        $this->curlDestruct();

        return $page;
    }

    /**
     * Set options en masse
     *
     * @param  array $options
     * @throws Morgenshtern_Bot_Exception if argument is not an array
     * @return void
     */
    public function setOptions( $options )
    {
        if ( ! is_array( $options ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Options error: setOptions() expects an array.' );
        }

        foreach ( $options as $key => $value ) {
            $method = 'set' . ucfirst( $key );
            if ( method_exists( $this, $method ) ) {
                $this->$method( $value );
            }
        }
    }

    /**
     * Login setter
     *
     * @param  string $value
     * @throws Morgenshtern_Bot_Exception if login is not set
     * @return Morgenshtern_Bot_Abstract
     */
    public function setLogin( $value )
    {
        if ( null === $value OR empty( $value ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Login error: login must be set.' );
        }
        $this->_login = trim( $value );
        return $this;
    }

    /**
     * Login getter
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Password setter
     *
     * @param  string $value
     * @throws Morgenshtern_Bot_Exception if password is not set
     * @return Morgenshtern_Bot_Abstract
     */
    public function setPassword( $value )
    {
        if ( null === $value OR empty( $value ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Password error: password must be set.' );
        }
        $this->_password = trim( $value );
        return $this;
    }

    /**
     * Password getter
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * City setter
     *
     * @param  string $value
     * @throws Morgenshtern_Bot_Exception
     * @return Morgenshtern_Bot_Abstract
     */
    public function setCity( $value )
    {
        if ( null === $value OR empty( $value ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'City error: city must be set.' );
        }
        if ( ! in_array( $value, $this->_servers ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'City error: unknown city "' . $value . '".' );
        }
        $this->_city = trim( $value );
        return $this;
    }

    /**
     * City getter
     *
     * @return string
     */
    public function getCity()
    {
        return $this->_city;
    }

    /**
     * Returns city url
     *
     * @return string
     */
    public function getCityURL()
    {
        return 'http://' . strtolower( $this->_city ) . 'city.combats.com';
    }

    /**
     * Cookie setter
     *
     * @param  string $value
     * @throws Morgenshtern_Bot_Exception
     * @return Morgenshtern_Bot_Abstract
     */
    public function setCookiejar( $value )
    {
        if ( ! is_string( $value ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Cookie error: cookie must be a string.' );
        }
        $this->_cookiejar = trim( $value );
        return $this;
    }

    /**
     * Cookie getter
     *
     * @return string
     */
    public function getCookiejar()
    {
        return $this->_cookiejar;
    }

    /**
     * Cache setter
     *
     * @param  Zend_Cache_Core $cache
     * @throws Morgenshtern_Bot_Exception
     * @return Morgenshtern_Bot_Abstract
     */
    public function setCache( $cache )
    {
        if ( ! $cache instanceof Zend_Cache_Core ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Cache error: cache must be an instance of Zend_Cache_Core.' );
        }
        $this->_cache = $cache;
        return $this;
    }

    /**
     * Cache getter
     *
     * @return object
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Logger setter
     *
     * @param  Zend_Log $logger
     * @throws Morgenshtern_Bot_Exception
     * @return Morgenshtern_Bot_Abstract
     */
    public function setLogger( $logger )
    {
        if ( ! $logger instanceof Zend_Log ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Logger error: logger must be an instance of Zend_Log.' );
        }
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Logger getter
     *
     * @return object
     */
    public function getLogger()
    {
        return $this->_logger;
    }
    
    /**
     * Get random server
     *
     * @param string $city
     * @return string
     */
    public function getRandomServer()
    {
        $city = $this->getCity();
        $cityId = array_search( $city, $this->_servers );
        $randomCityId = rand( 0, count( $this->_servers ) - 1 );
        if ( $randomCityId == $cityId ) {
            return $this->getRandomServer( $city );
        }
        return $this->_servers[ $randomCityId ];
    }

    /**
     * Validate on errors
     *
     * Check if the page contains some exception
     *
     * @param string $cityId
     * @throws Morgenshtern_Bot_Exception
     * @return void
     */
    public function validate( $page, $options )
    {
        $error = curl_errno( $this->_handler );
        if ( $error != 0 ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Validate error [' . $error . ']: ' 
                                                . curl_error( $this->_handler ) . '.' );
        }

		$page = $this->_recode( $page, 'windows-1251', 'utf-8' );

        switch ( true ) {
            case eregi( self::REDIRECT, $page ):
                $this->connect();
                return $this->curlExec( $options );
            break;
            case eregi( self::JS_REDIRECT, $page ):
                $this->connect();
                return $this->curlExec( $options );
            break;
            case eregi( self::CHAT_LINK, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: невозможно выполнить '
                                                    . 'запрошенное действие из текущей комнаты.' );
            break;
            case eregi( self::NOT_HERE, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: невозможно выполнить '
                                                    . 'запрошенное действие из текущей комнаты.' );
            break;
            case eregi( self::SERVICE_UNAVAILIABLE, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Service Unavailable.' );
            break;
            case eregi( self::INTERNAL_SERVER_ERROR, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Internal Server Error.' );
            break;
            case eregi( self::IP_BANNED, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: IP временно заблокирован.' );
            break;
            case eregi( self::BAD_REQUEST, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Bad Request.' );
            break;
            case eregi( self::NO_LOGIN, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Не указан логин персонажа.' );
            break;
            case eregi( self::NO_PASSWORD, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Не указан пароль персонажа' );
            break;
            case eregi( self::WRONG_PASSWORD, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Неправильный пароль.' );
            break;
            case eregi( self::LOGIN_NOT_FOUND, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Неправильное имя пользователя.' );
            break;
            case eregi( self::CHAR_NOT_FOUND, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: Персонаж с таким логином не найден.' );
            break;
            case eregi( self::OTHER_ERROR, $page ):
                throw new Morgenshtern_Bot_Exception( 'Validate error: some error happened.' );
            break;
            default:
                return $page;
            break;
        }
    }
    
    /**
     * Get random float-like value
     *
     * @return string
     */
    public function getRand()
    {
        return '0.' . mt_rand();
    }

    protected function _recode( $value, $from = 'utf-8', $to = 'windows-1251' ) {
        return iconv( $from, $to . '//IGNORE', $value );
    }
	
	protected function _translit( $string )
	{
		$charFromMap = array(
			'А', 'Б', 'В', 'Г', 
			'Д', 'Е', 'Ё', 'Ж', 
			'З', 'И', 'Й', 'К', 
			'Л', 'М', 'Н', 'О', 
			'П', 'Р', 'С', 'Т', 
			'У', 'Ф', 'Х', 'Ц', 
			'Ч', 'Ш', 'Щ', 'Э', 
			'Ю', 'Я',
			'а', 'б', 'в', 'г', 
			'д', 'е', 'ё', 'ж', 
			'з', 'и', 'й', 'к', 
			'л', 'м', 'н', 'о', 
			'п', 'р', 'с', 'т', 
			'у', 'ф', 'х', 'ц', 
			'ч', 'ш', 'щ', 'э', 
			'ю', 'я'
		);
		$charToMap = array(
			'A', 'B', 'V', 'G', 
			'D', 'E', 'E', 'ZH', 
			'Z', 'I', 'Y', 'K', 
			'L', 'M', 'N', 'O', 
			'P', 'R', 'S', 'T', 
			'U', 'F', 'H', 'C', 
			'CH', 'SH', 'SHCH', 
			'E', 'YU', 'YA',
			'a', 'b', 'v', 'g', 
			'd', 'e', 'e', 'zh', 
			'z', 'i', 'y', 'k', 
			'l', 'm', 'n', 'o', 
			'p', 'r', 's', 't', 
			'u', 'f', 'h', 'c', 
			'ch', 'sh', 'shch', 
			'e', 'yu', 'ya'
		);
		return str_replace( $charFromMap, $charToMap, $string );
	}
	
	public function formatCacheId( $prefix = '', $id )
	{
		if ( preg_match( '#[^a-zA-Z0-9_]#', $id ) ) {
			$id = $this->_translit( $id );
			$id = preg_replace( '#[^a-zA-Z0-9_]#', '', $id );
		}
		return $prefix . $id;
	}

    /**
     * cUrl Destructor
     *
     * Close currently opened curl connection
     *
     * @return void
     */
    public function curlDestruct()
    {
        if ( null !== $this->_handler ) {
            curl_close( $this->_handler );
            $this->_handler = null;
        }
    }
}