<?php if (!defined('FARI')) die();

/**
 * Authenticated user.
 *
 * @example   This object will throw an exception if user is not authenticated, use in admin
 * @package   Application\Models\Auth
 */
class AuthUser extends Fari_AuthenticatorSimple {

    private $table;

    /**
     * Check that user is authenticated.
     * @throws AuthUserNotAuthenticatedException
     */
    public function __construct() {
        // construct the db table
        $this->table = new Table('users');
        // call the authenticator
        parent::__construct($this->table);

        // no entry, we are not logged in, fail the constructor
        if (!$this->isAuthenticated()) throw new AuthUserNotAuthenticatedException();
    }

    /**
     * Fetch row from 'users' table.
     * @return array
     */
    public function getUser() {
        return $this->table->findFirst()->where(array('username' => $this->getCredentials()));
    }

}
