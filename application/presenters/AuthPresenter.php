<?php if (!defined('FARI')) die();

/**
 * User login and signoff.
 *
 * @package   Application\Presenters
 */
final class AuthPresenter extends Fari_ApplicationPresenter {

    /**#@+ where to redirect on successful login? */
    const ADMIN = 'topics';
    /**#@-*/

	public function actionIndex($p) {
        $this->actionLogin();
    }

	/**
	 * User sign-in/login
	 */
	public function actionLogin() {
        // authenticate user if form data POSTed
        if ($this->request->getPost('username')) {
            $username = Fari_Decode::accents($this->request->getPost('username'));
            $password = Fari_Decode::accents($this->request->getPost('password'));

            try {
                $user = new AuthAuth($username, $password, $this->request->getPost('token'));
                
                // redirect us to the route originally requested
                if (isset($_SESSION['Route'])) {
                    $route = $_SESSION['Route'];
                    unset($_SESSION['Route']);
                    $this->redirectTo($route);
                } else {
                    $this->redirectTo('/' . self::ADMIN);
                }
            } catch (AuthUserNotAuthenticatedException $e) {
                $this->flashFail = "Sorry, your username or password wasn't recognized";
            }
        }

		// create token & display login form
		$this->bag->token = Fari_FormToken::create();
		$this->renderAction('login');
	}

	/**
	 * Destroy user session.
	 */
    public function actionLogout() {
        try {
            $user = new AuthUser();
            $user->signOut();
            $this->flashSuccess = "You have been logged out";
        } catch (AuthUserNotAuthenticatedException $e) {
            $this->flashSuccess = 'You are already logged out';
        }

        // create token & display login form
        $this->bag->token = Fari_FormToken::create();
		$this->renderAction('login');
	}

}