<?php namespace TwitchApi;

use ReflectionClass;
use RuntimeException;

class Application implements Contracts\Application {

	/**
	 * The API base url.
	 *
	 * @var string
	 */
	public static $api = 'https://api.twitch.tv/kraken';

	/**
	 * Whether to verify the server certificate or not.
	 *
	 * @var boolean
	 */
	public $verifypeer = true;

	/**
	 * Whether to use Twitch API v3 or not.
	 *
	 * @var boolean
	 */
	public $v3 = true;

	/**
	 * Holds the Twitch application client id.
	 *
	 * @var string
	 */
	private static $client;

	/**
	 * Holds the Twitch application client secret.
	 *
	 * @var string
	 */
	private static $secret;

	/**
	 * Holds the Twitch application scopes.
	 *
	 * @var array
	 */
	private static $scopes;

	/**
	 * Holds the Twitch application redirect URI.
	 *
	 * @var string
	 */
	private static $redirect;

	/**
	 * Holds all the bindings.
	 *
	 * @var array
	 */
	protected static $bindings;

	/**
	 * Holds all the instance bindings.
	 *
	 * @var array
	 */
	protected static $instances;

	/**
	 * Create a new instance.
	 *
	 * @param  string  $client
	 * @param  string  $secret
	 * @param  array  $scopes
	 * @param  string  $redirect
	 */
	public function __construct($client, $secret, array $scopes, $redirect)
	{
		static::$client = $client;
		static::$secret = $secret;
		static::$scopes = $scopes;
		static::$redirect = $redirect;

		$this->bind('TwitchApi\Contracts\Request', 'TwitchApi\Request');
		$this->bind('TwitchApi\Contracts\Response', 'TwitchApi\Response');
		$this->bind('TwitchApi\Contracts\User', 'TwitchApi\User');
		$this->bind('TwitchApi\Contracts\Channel', 'TwitchApi\Channel');
	}

	/**
	 * Return the request implementation.
	 *
	 * @return \TwitchApi\Contracts\Request
	 */
	public function request()
	{
		return $this->instance('TwitchApi\Contracts\Request');
	}

	/**
	 * Bind a concrete class to an abstract contract.a
	 *
	 * @param  string  $abstract
	 * @param  string  $concrete
	 * @return void
	 */
	public function bind($abstract, $concrete)
	{
		static::$bindings[$abstract] = $concrete;
	}

	/**
	 * Retrieve a concrete class, or instantiate a new one if necessary.
	 *
	 * @param  string  $abstract
	 * @param  array  $parameters  []
	 * @return \StdClass
	 */
	public function instance($abstract, array $parameters = [])
	{
		if (isset(static::$instances[$abstract]))
		{
			return static::$instances[$abstract];
		}

		return static::$instances[$abstract] = $this->make($abstract, $parameters);
	}

	/**
	 * Instantiate a new concrete class from an abstract contract.
	 *
	 * @param  string  $abstract
	 * @param  array  $parameters  []
	 * @return \StdClass
	 */
	public function make($abstract, array $parameters = [])
	{
		if ( ! isset(static::$bindings[$abstract]))
		{
			throw new RuntimeException("No concrete class has been bound for abstract '${abstract}'");
		}

		return $this->instantiate(static::$bindings[$abstract], $parameters);
	}

	/**
	 * Instantiate a concrete class with parameters.
	 *
	 * @param  string  $concrete
	 * @param  array  $parameters
	 * @return \StdClass
	 */
	private function instantiate($concrete, array $parameters)
	{
		$class = new ReflectionClass($concrete);

		array_unshift($parameters, $this);

		return $class->newInstanceArgs($parameters);
	}

	/**
	 * Retrieve the Twitch API url.
	 *
	 * @return string
	 */
	public static function api()
	{
		return static::$api;
	}

	/**
	 * Retrieve the Twitch application client id.
	 *
	 * @return string
	 */
	public static function client()
	{
		return static::$client;
	}

	/**
	 * Retrieve the Twitch application client secret.
	 *
	 * @return string
	 */
	public static function secret()
	{
		return static::$secret;
	}

	/**
	 * Retrieve the Twitch application scopes.
	 *
	 * @return string
	 */
	public static function scopes()
	{
		return implode(' ', static::$scopes);
	}

	/**
	 * Retrieve the Twitch application redirect URI.
	 *
	 * @return string
	 */
	public static function redirect()
	{
		return static::$redirect;
	}

	/**
	 * Filter the array using the given callback.
	 *
	 * @param  array  $array
	 * @param  callable  $callback
	 * @return array
	 */
	public static function where($array, callable $callback)
	{
		$filtered = [];

		foreach ($array as $key => $value)
		{
			if (call_user_func($callback, $key, $value)) $filtered[$key] = $value;
		}

		return $filtered;
	}

}
