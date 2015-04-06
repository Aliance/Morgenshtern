<?php

/**
 * Concrete class for clan bot
 *
 * @category   Morgenshtern
 * @package    Morgenshtern_Bot
 * @copyright  Copyright (c) 2010 Aliance spb (http://www.morgenshtern.com)
 * @license    http://www.gnu.org/copyleft/lesser.html     LGPL
 */
class Morgenshtern_Bot extends Morgenshtern_Bot_Abstract
{
    /**
     * Connect to combats.com
     *
     * @return string
     */
    public function connect()
    {
        $logger = $this->getLogger();
		$logger->bot( 'Авторизация бота: ' . $this->getLogin() );

		/* ШАГ 1 */
        $randomCity = $this->getRandomServer();
        $url = sprintf( 'http://%scity.combats.com/enter.pl', $randomCity );
        $post = sprintf( 'login=%s&psw=%s', urlencode( $this->getLogin() ), $this->getPassword() );

        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $post
        );

        $page = $this->curlExec( $options );

        $data = $this->getSessionId( $page );

        if ( ! is_array( $data ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( '1 argument must be an array.' );
        }

        /* ШАГ 2 */
        $url = sprintf( 'http://%scity.combats.com/enter.pl', $this->_servers[ $data['city_id'] ] );
        $post = sprintf( 'from=%s&sid=%s', $data['from'], $data['sid'] );
        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_HEADER     => true,
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_COOKIEJAR  => $this->getCookiejar()
        );

        $page = $this->curlExec( $options );

        if ( eregi( "battlepsw=([[:digit:].]+)", $page, $results ) ) {
            $battlepsw = $results[1];
        }

        /* ШАГ 3 */
        $url = sprintf( '%s/buttons.pl?battle=%s', $this->getCityURL(), $battlepsw );
        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_COOKIEFILE => $this->getCookiejar()
        );
        return $this->curlExec( $options );
    }

    /**
     * Ping bot connection
     *
     * @return string
     */
    public function ping()
    {
        $logger = $this->getLogger();
		$logger->bot( 'Пинг бота: ' . $this->getLogin() );

		$url = sprintf( '%s/main.pl?rnd=%s', $this->getCityURL(), $this->getRand() );
        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_HEADER     => true,
            CURLOPT_COOKIEFILE => $this->getCookiejar()
        );
        $page = $this->curlExec( $options );
        if ( eregi( "Content-Length: 228", $page, $results ) ) {
            $this->connect();
            $page = $this->curlExec( $options );
        }
		return $page;
    }
    
    /**
     * Update bot profile
     *
     * @return string
     */
    public function update()
    {
        $logger = $this->getLogger();
		$logger->bot( 'Изменение анкеты бота: ' . $this->getLogin() );

		$options = array(
            CURLOPT_URL        => $this->getCityURL() . '/main.pl?editanketa=1',
            CURLOPT_COOKIEFILE => $this->getCookiejar()
        );

        $page = $this->curlExec( $options );

        $re = "<INPUT type=hidden name=\"sd4\" value=\"([/.:[:alnum:]]+)\">";
		$text  = 'Если Вы получили приватное сообщение от Бота, ';
		$text .= 'ему отвечать бессмысленно - Вас никто не услышит.';
		$text .= PHP_EOL;
		$text .= 'Ответить Вы можете на сайте клана Morgenshtern.';
		$text .= PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
		$text .= PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
		$text .= PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
		$text .= sprintf( '[%s]', date( 'd-m-Y H:i:s' ) );
        if ( eregi( $re, $page, $results ) ) {
            $post = array(
                'sd4' => $results[1],
                'name' => 'Технический персонаж клана Morgenshtern',
                'DD' => '1',
                'MM' => '01',
                'YYYY' => '2000',
                '0day' => '01.01.2000',
                'city' => ucfirst( $this->getCity() ) . 'City',
                'city2' => '',
                'icq' => '',
                'homepage' => 'http://capitalcity.combats.com/encicl/klan/Morgenshtern.html',
                'about' => '',
                'hobby' => $text,
                /*'hobby' => 'Ответственный тарман: Child of sun' . 
                           PHP_EOL . 
                           'Ответственный паладин: Tyoma Man' . 
                           PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . 
                           'Последнее обращение скриптом: ' . date( 'd-m-Y H:i:s' ),*/
                'ChatColor' => 'Blue',
                'batdes' => '1',
                'saveanketa' => 'Сохранить изменения'
            );

            $options = array(
                CURLOPT_URL        => $this->getCityURL() . '/main.pl',
                CURLOPT_COOKIEFILE => $this->getCookiejar(),
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => http_build_query( $post )
            );

            $page = $this->curlExec( $options );
        }
		return $page;
    }

    /**
     * Send message
     *
     * @param  string $msg
     * @param  array|string|null $chars
     * @param  bool $private
     * @throws Morgenshtern_Bot_Exception if 2 argument is not an array
     * @return string
     */
    public function chat( $msg, $chars = null, $private = false )
    {
        if ( null === $chars ) {
            $message = sprintf( '%s', $msg );
        } else {
            $prefix = $private ? 'private' : 'to';
            if ( is_string( $chars ) ) {
                $message = sprintf( '%s [%s] %s', $prefix, $chars, $msg );
            } else if ( is_array( $chars ) ) {
                $message = sprintf( '%s [%s] %s', $prefix, implode( ',', $chars ), $msg );
            } else {
                require_once 'Morgenshtern/Bot/Exception.php';
                throw new Morgenshtern_Bot_Exception( '2 argument must be an array or a string.' );
            }
        }

		$message = str_replace( ' ', '+', $this->_recode( $message ) );
		$text = sprintf( '%s', $message );
        $url = sprintf( '%s/ch.pl?show=1&na=1&lid=-1&text=%s&ver=0.837&aun=1', $this->getCityURL(), $text );
        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_COOKIEFILE => $this->getCookiejar()
        );

        $page = $this->curlExec( $options );
		return $page;
    }

    /**
     * Get the clan staff
     *
     * @param  string $clan
     * @throws Morgenshtern_Bot_Exception if 1 argument is not a string
     * @return array
     */
    public function getClanStaff( $clan = 'Morgenshtern' )
    {
        if ( ! is_string( $clan ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( '1 argument must be a string.' );
        }

		$logger = $this->getLogger();
		$cache = $this->getCache();
		#$cache->remove('staff_' . $clan);
		#$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
		#$cache->clean( Zend_Cache::CLEANING_MODE_MATCHING_TAG, array( 'char' ) );

		$cacheId = $this->formatCacheId( 'staff_', $clan );
		if ( $cache->test( $cacheId ) ) {
			$matches = $cache->load( $cacheId );
			$staff = $this->formatClanStaff( $matches, $clan );
			return $staff;
		}

        $url = sprintf( 'http://capitalcity.combats.com/clans_inf.pl?%s', $clan );
        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_COOKIEFILE => $this->getCookiejar()
        );

        $page = $this->curlExec( $options );
        if ( ereg( 'Нет информации о клане', $this->_recode( $page, 'windows-1251', 'utf-8' ) ) ) {
            $logger->bot( 'Ошибка при получении состава клана ' . $clan );
			require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Проверьте правильность указанного клана.' );
        }

        $staff = array();
        $re = '#\<SCRIPT\>drwfl\(\"(.+?)\",(\d+),\"(\d{1,2})\",(?:.+?),\"' . $clan . '\"\)\<\/SCRIPT\>\<br\>#xm';
        if ( preg_match_all( $re, $page, $matches, PREG_SET_ORDER ) ) {
			$cache->save( $matches, $cacheId, array( 'bot', 'staff' ), null );
			$staff = $this->formatClanStaff( $matches, $clan );
        }

        return $staff;
    }

    /**
     * Get the clan online staff
     *
     * @param  string $clan
     * @throws Morgenshtern_Bot_Exception if 1 argument is not a string
     * @return array
     */
    public function getClanOnlineStaff( $clan = 'Morgenshtern' )
    {
        if ( ! is_string( $clan ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( '1 argument must be a string.' );
        }

		$logger = $this->getLogger();
		$cache = $this->getCache();
		#$cache->remove('staff_' . $clan);
		#$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
		#$cache->clean( Zend_Cache::CLEANING_MODE_MATCHING_TAG, array( 'char' ) );

		$cacheId = $this->formatCacheId( 'staff_', $clan );
		if ( $cache->test( $cacheId ) ) {
			$logger->bot( 'Состав клана ' . $clan . ' взят из кеша.' );
			$staff = $cache->load( $cacheId );
			return $staff;
		}

        $url = sprintf( 'http://capitalcity.combats.com/clans_inf.pl?%s', $clan );
        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_COOKIEFILE => $this->getCookiejar()
        );

        $logger->bot( 'Попытка получить состав клана ' . $clan );
		$page = $this->curlExec( $options );
        if ( ereg( 'Нет информации о клане', $this->_recode( $page, 'windows-1251', 'utf-8' ) ) ) {
            $logger->bot( 'Ошибка при получении состава клана ' . $clan );
			require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( 'Проверьте правильность указанного клана.' );
        }

        $staff = array();
        $re = '#\<SCRIPT\>drwfl\(\"(.+?)\",(\d+),\"(\d{1,2})\",(?:.+?),\"(?:.+?)\"\)\<\/SCRIPT\>\<br\>#xm';
        if ( preg_match_all( $re, $page, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
                if ( count( $staff ) > 0 AND $match[1] === $matches[0][1] ) {
                    continue;
                }
                $data = $this->getCharInfo( $match[1] );
                if ( isset( $data['online'] ) ) {
					array_push( $staff, $data );
				}
            }
			####################################################################
			######### Временное решение пока не все соклане со значком #########
			####################################################################
			if ( $clan == 'Morgenshtern' ) {
				$restStaff = array( 'Мхаэль', 'A_N_D', 'Atton', 'bamboocha', 
				                    'MstB', 'Mr Heracl', 'Pashka-Rubashka', 
									'Parazitas', 'Ples', 'Runner-up', 
									'SOKOS', 'WARDOC', 'Агварес', 'HEBckuu', 
									'Вендет', 'Друндул', 'Рыжий кош' );
				foreach ( $restStaff as $match ) {
					$data = $this->getCharInfo( $match );
					if ( isset( $data['online'] ) ) {
						array_push( $staff, $data );
					}
				}
			}
			####################################################################
        }
		$cache->save( $staff, $cacheId, array( 'bot', 'staff' ), 120 );

        return $staff;
    }

    /**
     * Format the clan staff
     *
     * @param  string $clan
     * @throws Morgenshtern_Bot_Exception if 1 argument is not a string
     * @return array
     */
    public function formatClanStaff( $matches, $clan )
    {
        $staff = array();

		foreach ( $matches as $match ) {
			if ( count( $staff ) > 0 AND $match[1] === $matches[0][1] ) {
				continue;
			}
			#$data = $this->getCharInfo( $this->_recode( $match[1], 'windows-1251', 'utf-8' ) );
			$data = $this->getCharInfo( $match[1] );
			array_push( $staff, $data );
		}

		####################################################################
		######### Временное решение пока не все соклане со значком #########
		####################################################################
		if ( $clan == 'Morgenshtern' ) {
			$restStaff = array( 'Мхаэль', 'A_N_D', 'Atton', 'bamboocha', 
								'MstB', 'Mr Heracl', 'Pashka-Rubashka', 
								'Parazitas', 'Ples', 'Runner-up', 
								'SOKOS', 'WARDOC', 'Агварес', 'HEBckuu', 
								'Вендет', 'Друндул', 'Рыжий кош' );
			foreach ( $restStaff as $match ) {
				$data = $this->getCharInfo( $match );
				array_push( $staff, $data );
			}
		}
		####################################################################

        return $staff;
    }

    /**
     * Get the char info
     *
     * @param  string $clan
     * @throws Morgenshtern_Bot_Exception if 1 argument is not a string
     * @return array
     */
    public function getCharInfo( $char )
    {		
		if ( ! is_string( $char ) ) {
            require_once 'Morgenshtern/Bot/Exception.php';
            throw new Morgenshtern_Bot_Exception( '1 argument must be a string.' );
        }

		$logger = $this->getLogger();
		$cache = $this->getCache();
		#$cache->clean(Zend_Cache::CLEANING_MODE_ALL);

		$cacheId = $this->formatCacheId( 'char_info_', $char );
		if ( $data = $cache->load( $cacheId ) ) {
			return $data;
		}

		$login = $char;
		if ( preg_match( '#[^a-zA-Z0-9_-\s]#', $login ) ) {
			$login = $this->_recode( $login );
		}
		$login = urlencode( $login );
		$url = sprintf( 'http://%scity.combats.com/inf.pl?%s&short=1', $this->getRandomServer(), $login );
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE     => $this->getCookiejar()
        );

        $page = $this->curlExec( $options );
        
		$data = array();
		$info = explode( "\n", $page );
		#Zend_Debug::dump($info);
		#exit;
		foreach ( $info as $attr ) {
			$params = explode( "=", $attr );
			if ( count( $params ) > 2 ) {
				$newParams = $params;
				$params = array( array_shift( $newParams ), implode( '=', $newParams ) );
			}
			/*
			zodiac=2 
			img=25 
			gamecity_url=http://sandcity.combats.com 
			dex=11 str=25 inst=146 power=75 intel=1 spirit=5 
			_dex=10 _str=24 _inst=107 _power=75 _spirit=5 
			*/
			if ( $params[0] === 'login' ) {
				$data['nick'] = $params[1];
			}
			if ( $params[0] === 'id' ) {
				$data['id'] = $params[1];
			}
			if ( $params[0] === 'sex' ) {
				$data['sex'] = $params[1];
			}
			if ( $params[0] === 'date_registry' ) {
				$data['date_registry'] = $params[1];
			}
			if ( $params[0] === 'level' ) {
				$data['level'] = $params[1];
			}
			if ( $params[0] === 'birthplace' ) {
				$data['birthplace'] = $params[1];
			}
			if ( $params[0] === 'gamecity' ) {
				$data['city'] = $params[1];
			}
			if ( $params[0] === 'align' ) {
				$data['align'] = $params[1];
			}
			if ( $params[0] === 'klan' ) {
				$data['clan'] = $params[1];
			}
			if ( $params[0] === 'vicrory' ) {
				$data['vicrory'] = $params[1];
			}
			if ( $params[0] === 'defeat' ) {
				$data['defeat'] = $params[1];
			}
			if ( $params[0] === 'withdraw' ) {
				$data['withdraw'] = $params[1];
			}
			if ( $params[0] === 'name' ) {
				$data['name'] = $params[1];
			}
			if ( $params[0] === 'pet_type' ) {
				$data['pet_type'] = $params[1];
			}
			if ( $params[0] === 'pet_name' ) {
				$data['pet_name'] = $params[1];
			}
			if ( $params[0] === 'pet_level' ) {
				$data['pet_level'] = $params[1];
			}
			if ( $params[0] === 'scrolls' ) {
				$data['scrolls'] = $params[1];
			}
			if ( $params[0] === 'gamecity_url' ) {
				$data['url'] = $params[1];
			}
			if ( $params[0] === 'login_online' AND $params[1] == '1' ) {
				$data['online'] = true;
			}
			if ( $params[0] === 'rank' ) {
				$data['rank'] = $params[1];
			}
			if ( $params[0] === 'bossklan' ) {
				$data['rank'] = '<strong>глава клана</strong>';
			}
			if ( $params[0] === 'room_name' ) {
				$data['room'] = $params[1];
			}
			if ( $params[0] === 'battle_id' ) {
				$data['battle'] = $params[1];
			}
			if ( $params[0] === 'reputations' ) {
				$chilvary = array();
				$reputations = explode( '|', $params[1] );
				array_pop( $reputations );
				foreach ( $reputations as $reputation ) {
					$details = explode( ',', $reputation );
					$dungeon = array();
					foreach ( $details as $detail ) {
						$param = explode( ':', $detail );
						$dungeon[ $param[0] ] = $param[1];
					}
					array_push( $chilvary, $dungeon );
				}
				$data['chilvary'] = $chilvary;
			}
			if ( $params[0] === 'objects' ) {
				array_shift( $params );
				$objects = explode( ',', implode( '=', $params ) );
				array_pop( $objects );
				$data['objects'] = $objects;
				$cache->remove( $cacheId );
			}
		}
		$cache->save( $data, $cacheId, array( 'bot', 'char' ), 120 );

        return $data;
    }
	
	/**
     * 
     *
     * 
     *
     * @param string $nick
     * @return array
     */
    public function analizeBattle( $nick )
	{
		$chars = array();
		$logger = $this->getLogger();
		$cache = $this->getCache();
		$info = $this->getCharInfo( $nick );
		#Zend_Debug::dump( $info );
		if ( empty( $info['battle'] ) ) {
			return array();
		}
		
		$url = sprintf( '%s/logs.pl?log=%s&p=1', $info['url'], $info['battle'] );
		#$logger->bot( 'Попытка получить информацию о чаре ' . $nick );
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE     => $this->getCookiejar()
        );

        $page = $this->curlExec( $options );
		/*
		echo '<hr>';
		echo htmlentities( $page );
		echo '<hr>';
		*/

		$re = '#\<SCRIPT\>drwfl\(\"(.+?)\",(\d{6,}),\"(\d{1,2})\",(?:.+?),\"(?:[A-Za-z]*?)\"\)\<\/SCRIPT\>#xm';
        if ( preg_match_all( $re, $page, $matches, PREG_SET_ORDER ) ) {
			$matches = $this->arrayUnique( $matches );
			foreach ( $matches as $match ) {
				$char = array( 'login' => $match[1] );
				array_push( $chars, $char );
			}
		}

		$itemModel = new Application_Model_Item;
		#Zend_Debug::dump( $matches );
		foreach ( $chars as $k => $char ) {
			$chars[ $k ]['class'] = '<i>не определён</i>';
			$chars[ $k ]['profileAtack'] = '<i>не определена</i>';
			$chars[ $k ]['weapon'] = '<i>не определено</i>';
			$weapons = array();
			try {
				$info = $this->getCharInfo( $char['login'] );
			} catch ( Morgenshtern_Bot_Exception $e ) {
				continue;
			}

			foreach( $info['objects'] as $item ) {
				list( $title, $description ) = explode( '=', $item );
				$_description = explode( '\n', $description );
				$item = $itemModel->getItem( $_description[0] );
				if ( $item ) {
					foreach ( $item['requirements'] as $requirement ) {
						switch ( $requirement['title'] ) {
							case 'Мастерство владения мечами':
								$weapon = 'Меч';
							break;
							case 'Мастерство владения луком':
								$weapon = 'Лук';
							break;
							case 'Мастерство владения арбалетом':
								$weapon = 'Арбалет';
							break;
							case 'Мастерство владения дубинами, булавами':
								$weapon = 'Дубина';
							break;
							case 'Мастерство владения магическими посохами':
								$weapon = 'Посох';
							break;
							case 'Мастерство владения ножами, кастетами':
								$weapon = 'Кинжал';
							break;
							case 'Мастерство владения топорами, секирами':
								$weapon = 'Топор';
							break;
						}
					}
					if ( isset( $weapon ) ) {
						$_option = array( 'title' => '', 'value' => 0.00 );
						foreach ( $item['options'] as $option ) {
							if ( floatval( $option['value'] ) > $_option['value'] ) {
								$_option = array( 'title' => $option['title'], 'value' => (float) $option['value'] );
							}
						}
						array_push( $weapons, array( 'title' => $weapon, 'item' => $item['title'], 'profileAtack' => $_option ) );
					}
				}
			}
			$weaponsCount = count( $weapons );
			if ( $weaponsCount > 0 ) {
				if ( $weaponsCount == 1 ) {
					$chars[ $k ]['profileAtack'] = $weapons[0]['profileAtack']['title'] . ' (' . $weapons[0]['profileAtack']['value'] . '%)';
					$chars[ $k ]['weapon'] = $weapons[0]['item'] . ' (' . $weapons[0]['title'] . ')';
				} else {
					if ( $weapons[0]['item'] == $weapons[1]['item'] ) {
						$chars[ $k ]['profileAtack'] = $weapons[0]['profileAtack']['title'] . ' (' . $weapons[0]['profileAtack']['value'] . '%)';
						$chars[ $k ]['weapon'] = $weapons[0]['item'] . ' (' . $weapons[0]['title'] . ') x2';
					} else {
						if ( $weapons[0]['profileAtack']['title'] == $weapons[1]['profileAtack']['title']
							 AND
							 $weapons[0]['profileAtack']['value'] == $weapons[1]['profileAtack']['value'] ) {
							$chars[ $k ]['profileAtack'] = $weapons[0]['profileAtack']['title'] . ' (' . $weapons[0]['profileAtack']['value'] . '%)';
						} else if ( $weapons[0]['profileAtack']['title'] == $weapons[1]['profileAtack']['title'] ) {
							$profileAtack = array( $weapons[0]['profileAtack']['value'], $weapons[1]['profileAtack']['value'] );
							asort( $profileAtack );
							$chars[ $k ]['profileAtack'] = $weapons[0]['profileAtack']['title'] . ' (' 
							                             . $profileAtack[0] . ' – ' . $profileAtack[1] . '%%)';
						} else {
							$chars[ $k ]['profileAtack'] = $weapons[0]['profileAtack']['title'] . ' (' . $weapons[0]['profileAtack']['value'] . '%) + '
							                             . $weapons[1]['profileAtack']['title'] . ' (' . $weapons[1]['profileAtack']['value'] . '%)';
						}
						$chars[ $k ]['weapon'] = $weapons[0]['item'] . ' (' . $weapons[0]['title'] . ') + '
											   . $weapons[1]['item'] . ' (' . $weapons[1]['title'] . ')';
					}
				}
			}
			
		}
		return $chars;
	}

    /**
     * Get session ID
     *
     * Parse responce and return stored data
     *
     * @param string $page
     * @return array
     */
    public function getSessionId( $page )
    {
        $data = array( 'city_id' => null, 'from' => null, 'sid' => null );
        if ( eregi( 'http://([A-Za-z]+)city.combats.com/enter.pl', $page, $matches ) ) {
            $data['city_id'] = array_search( $matches[1], $this->_servers );
        }
        if ( eregi( '<input type=hidden name=from  value="([.[:alnum:]]+)"', $page, $matches ) ) {
            $data['from'] = $matches[1];
        }
        if ( eregi( '<input type=hidden name=sid value="([.[:alnum:]]+)"', $page, $matches ) ) {
            $data['sid'] = $matches[1];
        }
        return $data;
    }
	
	public function arrayUnique($myArray) 
	{ 
		if(!is_array($myArray)) 
			   return $myArray; 

		foreach ($myArray as &$myvalue){ 
			$myvalue=serialize($myvalue); 
		} 

		$myArray=array_unique($myArray); 

		foreach ($myArray as &$myvalue){ 
			$myvalue=unserialize($myvalue); 
		} 

		return $myArray; 

	}
}