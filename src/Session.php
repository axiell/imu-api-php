<?php
/* KE Software Open Source Licence
** 
** Notice: Copyright (c) 2011-2013 KE SOFTWARE PTY LTD (ACN 006 213 298)
** (the "Owner"). All rights reserved.
** 
** Licence: Permission is hereby granted, free of charge, to any person
** obtaining a copy of this software and associated documentation files
** (the "Software"), to deal with the Software without restriction,
** including without limitation the rights to use, copy, modify, merge,
** publish, distribute, sublicense, and/or sell copies of the Software,
** and to permit persons to whom the Software is furnished to do so,
** subject to the following conditions.
** 
** Conditions: The Software is licensed on condition that:
** 
** (1) Redistributions of source code must retain the above Notice,
**     these Conditions and the following Limitations.
** 
** (2) Redistributions in binary form must reproduce the above Notice,
**     these Conditions and the following Limitations in the
**     documentation and/or other materials provided with the distribution.
** 
** (3) Neither the names of the Owner, nor the names of its contributors
**     may be used to endorse or promote products derived from this
**     Software without specific prior written permission.
** 
** Limitations: Any person exercising any of the permissions in the
** relevant licence will be taken to have accepted the following as
** legally binding terms severally with the Owner and any other
** copyright owners (collectively "Participants"):
** 
** TO THE EXTENT PERMITTED BY LAW, THE SOFTWARE IS PROVIDED "AS IS",
** WITHOUT ANY REPRESENTATION, WARRANTY OR CONDITION OF ANY KIND, EXPRESS
** OR IMPLIED, INCLUDING (WITHOUT LIMITATION) AS TO MERCHANTABILITY,
** FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. TO THE EXTENT
** PERMITTED BY LAW, IN NO EVENT SHALL ANY PARTICIPANT BE LIABLE FOR ANY
** CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
** TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
** SOFTWARE OR THE USE OR OTHER DEALINGS WITH THE SOFTWARE.
** 
** WHERE BY LAW A LIABILITY (ON ANY BASIS) OF ANY PARTICIPANT IN RELATION
** TO THE SOFTWARE CANNOT BE EXCLUDED, THEN TO THE EXTENT PERMITTED BY
** LAW THAT LIABILITY IS LIMITED AT THE OPTION OF THE PARTICIPANT TO THE
** REPLACEMENT, REPAIR OR RESUPPLY OF THE RELEVANT GOODS OR SERVICES
** (INCLUDING BUT NOT LIMITED TO SOFTWARE) OR THE PAYMENT OF THE COST OF SAME.
*/
require_once dirname(__FILE__) . '/IMu.php';
require_once IMu::$api . '/Exception.php';
require_once IMu::$api . '/Stream.php';
require_once IMu::$api . '/Trace.php';

/*!
** Manages a connection to an IMu server.
**
** The server’s host name and port can be specified by setting properties on
** the object or by setting class-based default properties.
**
** @usage
**   require_once IMu::$lib . '/Session.php';
** @end
**
** @since 1.0
**
** @example Connect to a default server.
**
** @code
**   $session = new IMuSession();
**   $session->connect();
*/
class IMuSession
{
	/* Static Properties */
	/*!
	** @property defaultHost string
	**   The default host to connect to if no object-specific host has been
	**   supplied.
	*/
	public static function
	getDefaultHost()
	{
		return self::$_defaultHost;
	}

	public static function
	setDefaultHost($host)
	{
		self::$_defaultHost = $host;
	}

	/*!
	** @property defaultPort int
	**   The number of the port used to create a connection if no
	**   object-specific host has been supplied.
	*/
	public static function
	getDefaultPort()
	{
		return self::$_defaultPort;
	}

	public static function
	setDefaultPort($port)
	{
		self::$_defaultPort = $port;
	}

	/*!
	** @property defaultTimeout int
	**   The default timeout value (in seconds).
	**   This timeout is used if the constructor is called without a timeout 
	**   argument.
	*/
	public static function
	getDefaultTimeout()
	{
		return self::$_defaultTimeout;
	}

	public static function
	setDefaultTimeout($timeout)
	{
		self::$_defaultTimeout = $timeout;
	}

	/* Constructor */
	/*!
	** Creates a `Session` object with the specified ``host`` and ``port``.
	**
	** @param $host string
	**   The default server host.
	**
	** @param $port string
	**   The default server port.
	**
	** @param $timeout int
	**   The default timeout value (in seconds).
	*/
	public function
	__construct($host = null, $port = null, $timeout = null)
	{
		$this->initialise();
		if ($host !== null)
			$this->_host = $host;
		if ($port !== null)
			$this->_port = $port;
		if ($timeout !== null)
			$this->_timeout = $timeout;
	}

	/* Properties */
	/**
	** @property close bool
	**
	** A flag controlling whether the connection to the server should be closed
	** after the next request. 
	** This flag is passed to the server as part of the next request to allow it
	** to clean up.
	*/
	public function
	getClose()
	{
		if ($this->_close === null)
			return false;
		return $this->_close;
	}

	public function
	setClose($close)
	{
		$this->_close = $close;
	}

	/**
	** @property context string
	**
	** The unique identifier assigned by the server to the current session.
	*/
	public function
	getContext()
	{
		return $this->_context;
	}

	public function
	setContext($context)
	{
		$this->_context = $context;
	}

	/**
	** @property host string
	**
	** The name of the host used to create the connection.
	** Setting this property after the connection has been established has no 
	** effect.
	*/
	public function
	getHost()
	{
		return $this->_host;
	}

	public function
	setHost($host)
	{
		$this->_host = $host;
	}

	/**
	** @property port int
	**
	** The number of the port used to create the connection. 
	** Setting this property after the connection has been established has no 
	** effect.
	*/
	public function
	getPort()
	{
		return $this->_port;
	}

	public function
	setPort($port)
	{
		$this->_port = $port;
	}

	/**
	** @property suspend bool
	**
	** A flag controlling whether the server process handling this session
	** should begin listening on a distinct, process-specific port to ensure a
	** new session connects to the same server process. 
	**
	** This is part of IMu's mechanism for maintaining state. 
	** If this flag is set to ``true``, then after the next request is made to 
	** the server, the `IMuSession`'s **port** property [$<link>(:session:port)]
	** will be altered to the process-specific port number.
	*/
	public function
	getSuspend()
	{
		if ($this->_suspend === null)
			return false;
		return $this->_suspend;
	}

	public function
	setSuspend($suspend)
	{
		$this->_suspend = $suspend;
	}

	/**
	** @property timeout int
	*/
	public function
	getTimeout()
	{
		return $this->_timeout;
	}

	public function
	setTimeout($timeout)
	{
		$this->_timeout = $timeout;
	}

	public function
	__get($name)
	{
		switch ($name)
		{
		  case 'close':
		  	return $this->getClose();
			break;
		  case 'context':
		  	return $this->getContext();
			break;
		  case 'host':
		  	return $this->getHost();
			break;
		  case 'port':
		  	return $this->getPort();
			break;
		  case 'suspend':
		  	return $this->getSuspend();
			break;
		  case 'timeout':
		  	return $this->getTimeout();
			break;
		  default:
		  	throw new IMuException('SessionProperty', $name);
		}
	}

	public function
	__set($name, $value)
	{
		switch ($name)
		{
		  case 'close':
		  	return $this->setClose($value);
			break;
		  case 'context':
		  	return $this->setContext($value);
			break;
		  case 'host':
		  	return $this->setHost($value);
			break;
		  case 'port':
		  	return $this->setPort($value);
			break;
		  case 'suspend':
		  	return $this->setSuspend($value);
			break;
		  case 'timeout':
		  	return $this->setTimeout($value);
			break;
		  default:
		  	throw new IMuException('SessionProperty', $name);
		}
	}

	/* Methods */
	/*!
	** Opens a connection to an IMu server.
	**
	** @throws IMuException
	**   The connection could not be opened.
	*/
	public function
	connect()
	{
		if ($this->_socket !== null)
			return;

		IMuTrace::write(2, 'connecting to %s:%d', $this->_host, $this->_port);
		$socket = @fsockopen($this->_host, $this->_port, $errno, $errstr);
		if ($socket === false)
			throw new IMuException('SessionConnect', $this->_host, $this->_port,
				$errstr);
		IMuTrace::write(2, 'connected ok');
		if ($this->_timeout !== null)
		{
			IMuTrace::write(2, 'setting timeout to %s', $this->_timeout);
			stream_set_timeout($socket, $this->_timeout);
		}
		$this->_socket = $socket;
		$this->_stream = new IMuStream($this->_socket);
	}

	/*!
	** Closes the connection to the IMu server.
	*/
	public function
	disconnect()
	{
		if ($this->_socket === null)
			return;

		IMuTrace::write(2, 'closing connection');
		@fclose($this->_socket);
		$this->initialise();
	}

	/*!
	** Logs in as the given user with the given password.
	**
	** If the ``$spawn`` parameter is set to ``true``, this will cause the
	** server to create a new child process specifically to handle the newly
	** logged in users's requests.
	**
	** @param $login string
	**   The name of the user to login as.
	**
	** @param $password string
	**   The user's password for authentication.
	**
	** @param $spawn bool
	**   A flag indicating whether the process should create a new child
	**   process specifically for handling the newly logged in user's requests.
	**   This value defaults to ``true``.
	**
	** @throws IMuException
	**   The login request failed.
	**
	** @throws Exception
	**   A low-level socket communication error occurred.
	**
	** @example
	**   Login as user fred with password 'ok!'
	**
	**   @code
	**     $session->login('fred', 'ok!');
	**
	** @example
	**   Login as user fred using server-side authentication.
	**
	**   @code
	**     $session->login('fred');
	*/
	public function
	login($login, $password = null, $group = null, $spawn = true)
	{
		$request = array();
		$request['login'] = $login;
		$request['password'] = $password;
		$request['group'] = $group;
		$request['spawn'] = $spawn;
		return $this->request($request);
	}

	/*!
	** Logs the user out of the server.
	**
	** @since 2.0
	*/
	public function
	logout()
	{
		$request = array();
		$request['logout'] = true;
		return $this->request($request);
	}

	public function
	checkStatus()
	{
		$request = array();
		$request['checkStatus'] = true;
		$response = $this->request($request);

		IMuTrace::write(3, 'checkStatus: EMu server OK');
		IMuTrace::write(2, 'EMu server response... %s', $response);
		return $response;
	}

	/*!
	** Submits a low-level request to the IMu server.
	**
	** @param $request array
	**   An associative array containing the request parameters.
	**
	** @returns array
	**   An associative array containg the server's response.
	**
	** @throws IMuException
	**   A server-side error occurred.
	*/
	public function
	request($request)
	{
		$this->connect();

		if ($this->_close !== null)
			$request['close'] = $this->_close;
		if ($this->_context !== null)
			$request['context'] = $this->_context;
		if ($this->suspend !== null)
			$request['suspend'] = $this->_suspend;

		$this->_stream->put($request);
		$response = $this->_stream->get();
		$type = gettype($response);
		if ($type != 'array')
			throw new IMuException('SessionResponse', $type);

		if (array_key_exists('context', $response))
			$this->_context = $response['context'];
		if (array_key_exists('reconnect', $response))
			$this->_port = $response['reconnect'];

		$disconnect = false;
		if ($this->_close !== null)
			$disconnect = $this->_close;
		if ($disconnect)
			$this->disconnect();

		$status = $response['status'];
		if ($status == 'error')
		{
			IMuTrace::write(2, 'server error %s', $response);

			$id = 'SessionServerError';
			if (array_key_exists('error', $response))
				$id = $response['error'];
			else if (array_key_exists('id', $response))
				$id = $response['id'];

			$e = new IMuException($id);

			if (isset($response['args']))
				$e->setArgs($response['args']);

			if (isset($response['code']))
				$e->setCode($response['code']);

			IMuTrace::write(2, 'throwing exception %s', $e->__toString());

			throw $e;
		}

		return $response;
	}

	private static $_defaultHost = '127.0.0.1';
	private static $_defaultPort = 40000;
	private static $_defaultTimeout = null;	// use system default in php.ini

	private $_close;
	private $_context;
	private $_host;
	private $_port;
	private $_socket;
	private $_stream;
	private $_suspend;
	private $_timeout;

	private function
	initialise()
	{
		$this->_close = null;
		$this->_context = null;
		$this->_host = self::$_defaultHost;
		$this->_port = self::$_defaultPort;
		$this->_socket = null;
		$this->_stream = null;
		$this->_suspend = null;
		$this->_timeout = self::$_defaultTimeout;
	}
}
?>
