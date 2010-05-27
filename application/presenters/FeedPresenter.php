<?php if (!defined('FARI')) die();

/**
 * Description of Feed.
 *
 * @package   Application\Presenters
 */
final class FeedPresenter extends Fari_ApplicationPresenter {

    /********************* filters *********************/

    /**
     * Applied automatically before any action is called.
     * @example use it to authenticate users or setup locales
     */
    public function filterStartup() {
        try {
            $user = new AuthUser();
        } catch (AuthUserNotAuthenticatedException $e) {
            $_SESSION['Route'] = $this->request->getQuery('route');
            $this->redirectTo('/auth/');
        }
    }

    /********************* actions *********************/

    /** Responsible for presenting a collection back to the user. */
	public function actionIndex($p) {
        new Feed();
    }

    /** Responsible for showing a single specific object to the user. */
	public function actionShow() { }

    /** Responsible for providing the user with an empty form to create a new object. */
	public function actionNew() { }

    /** Receives the form submission from the new action and creates the new object. */
	public function actionCreate() { }

    /** Responsible for providing a form populated with a specific object to edit. */
	public function actionEdit() { }

    /** Receives the form submission from the edit action and updates the specific object. */
	public function actionUpdate() { }

    /** Deletes the specified object from the database. */
	public function actionDelete() { }

}