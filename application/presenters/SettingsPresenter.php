<?php if (!defined('FARI')) die();

/**
 * Description of Settings.
 *
 * @package   Application\Presenters
 */
final class SettingsPresenter extends Fari_ApplicationPresenter {

    private $user;
    private $userRow;

    /********************* filters *********************/

    public $beforeFilter = array(
        array('userRow' => array('index', 'edit', 'update'))
    );

    /** var filters to apply to these actions before they are called */
    //public $beforeFilter = array(
    //    array('nameOfFilter' => array('nameOfAction'))
    //);

    /** var filters to apply after all processing has occured */
    //public $afterFilter = array(
    //    array('nameOfFilter' => array('nameOfAction'))
    //);

    /**
     * Applied automatically before any action is called.
     * @example use it to authenticate users or setup locales
     */
    public function filterStartup() {
        try {
            $this->user = new AuthUser();
        } catch (AuthUserNotAuthenticatedException $e) {
            $_SESSION['Route'] = $this->request->getQuery('route');
            $this->redirectTo('/auth/');
        }
    }

    public function filterUserRow() {
        $this->bag->userRow = $this->userRow = $this->user->getUser();
    }

    /********************* actions *********************/

    /** Responsible for presenting a collection back to the user. */
	public function actionIndex($p) {
        $this->actionEdit();
    }

    /** Responsible for showing a single specific object to the user. */
	public function actionShow() { }

    /** Responsible for providing the user with an empty form to create a new object. */
	public function actionNew() { }

    /** Receives the form submission from the new action and creates the new object. */
	public function actionCreate() { }

    /** Responsible for providing a form populated with a specific object to edit. */
	public function actionEdit() {
        // fetch stylesheets
        $layouts = new Layouts();
        $this->bag->stylesheets = $layouts->listDirectory('public');

        $this->renderAction('edit');
    }

    /** Receives the form submission from the edit action and updates the specific object. */
	public function actionUpdate() {
        if ($this->request->isPost()) {
            // valid stylesheet?
            $layouts = new Layouts();
            $post = $this->request->getPost();
            if (in_array($post['layout'], $layouts->listDirectory('public'))) {
                // update our cached user row
                $this->userRow['layout'] = $post['layout'];
                // passwords identical?
                if (!empty($post['password1'])) {
                    if ($post['password1'] == $post['password2']) {
                        $this->userRow['password'] = sha1($post['password1']);
                    } else {
                        $this->flashFail = 'The passwords do not match';
                        $this->redirectTo('/settings');
                    }
                }
                $users = new Users();
                $users->update()->set($this->userRow)->where($this->userRow['id']);
                $this->flashSuccess = 'Settings updated';
            } else {
				$this->flashFail = 'Invalid stylesheet';
			}
        }
        $this->redirectTo('/settings');
    }

    /** Deletes the specified object from the database. */
	public function actionDelete() { }

}
