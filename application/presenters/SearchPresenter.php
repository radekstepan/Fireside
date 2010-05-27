<?php if (!defined('FARI')) die();

/**
 * Description of Search.
 *
 * @package   Application\Presenters
 */
final class SearchPresenter extends Fari_ApplicationPresenter {

    private $user;
    private $userRow;

    /********************* filters *********************/

    public $beforeFilter = array(
        array('userRow' => array('index'))
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
        $this->bag->q = $query = strtolower($this->request->getQuery('q'));

        $messages = new Messages();
        $this->bag->nodes = $messages->findNodes()->orderBy('created_at DESC')
            ->where(array('text' => "*{$query}*"));

        $this->renderAction('index');
    }

}