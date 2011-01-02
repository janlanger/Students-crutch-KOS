<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */
use Nette\Security\IAuthenticator;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;


/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class UsersModel extends \Nette\Object implements IAuthenticator
{

	/**
	 * Performs an authentication
	 * @param  array
	 * @return IIdentity
	 * @throws NAuthenticationException
	 */
	public function authenticate(array $credentials)
	{

		$username = (isset($credentials[self::USERNAME])?$credentials[self::USERNAME]:NULL);
		$password = (isset($credentials[self::PASSWORD])?sha1($credentials[self::PASSWORD]):NULL);

		$row = dibi::fetch('SELECT * FROM [:main:users] WHERE [username]=%s', $username);

		if (!$row) {
			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $password) {
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new Identity($row->id, $row->role, $row);
	}

}
