<?php if (!defined('FARI')) die();

/**
 * User authentication.
 *
 * @package   Application\Models\Auth
 */
class AuthAuth {

    /**
     * Authenticate credentials using Fari_AuthenticatorSimple
     * @param string $username
     * @param string $password
     * @param string $token (optional)
     * @return TestUser on success or TestUserNotAuthenticatedException thrown
     */
    function __construct($username, $password, $token=NULL) {
        $authenticator = new Fari_AuthenticatorSimple();
        // authenticator authenticates...
        if ($authenticator->authenticate($username, $password, $token) != TRUE) {
            throw new AuthUserNotAuthenticatedException();
        } else {
            // return the sweet beans
            return new AuthUser();
        }
    }

}