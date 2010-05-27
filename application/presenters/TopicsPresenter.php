<?php if (!defined('FARI')) die();

/**
 * Description of Topics.
 *
 * @package   Application\Presenters
 */
final class TopicsPresenter extends Fari_ApplicationPresenter {

    /** var Topics Table connection */
    private $topics;

    private $user;
    private $userRow;

    /********************* filters *********************/

    public $beforeFilter = array(
        array('userRow' => array('index', 'show', 'create', 'new'))
    );

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

        // setup table connection
        $this->topics = new Topics();
    }

    public function filterUserRow() {
        $this->bag->userRow = $this->userRow = $this->user->getUser();
    }

    /********************* actions *********************/

    /** Responsible for presenting a collection back to the user. */
	public function actionIndex($p) {
        $this->bag->topics = $this->topics->orderBy('updated_at DESC')->find()
        ->where(array('is_archived' => 0));

        // I can haz all topics that not like read by me concat plz
        $messages = $this->topics->findMessages()->select('group_concat(topics_id)')
            ->where(array('read_by' => "!*!{$this->userRow['id']}!*"));
        // parse
        $this->bag->unread = (!empty($messages)) ? $messages = explode(',', current(current($messages))) : array();

        $this->renderAction();
    }

    /** Responsible for showing a single specific object to the user. */
	public function actionShow($topicId) {
        $this->bag->topic = $this->topics->findFirst()->orderBy('updated_at DESC')->where($topicId);
        $this->bag->messages = $this->topics->findMessages()->orderBy('messages.id DESC')->where($topicId);
        $this->renderAction();
    }

    /** Responsible for providing the user with an empty form to create a new object. */
	public function actionNew() {
        $this->renderAction();
    }

    /** Receives the form submission from the new action and creates the new object. */
	public function actionCreate() {
        // create a new message
        $messages = new Messages();
        $name = $messages->generateNewName($this->request->getPost());

        if (empty($name)) {
            $this->flashFail = 'The message cannot be blank';
            $this->redirectTo('topics/new');
        }

        $stamp = date("Y-m-d H:i:s", mktime());

        $save['name'] = $this->userRow['name'];

        // create new topic
        $save['name'] = $this->request->getPost('name');
        $save['created_at'] = $save['updated_at'] = $stamp;
        $save['created_by'] = $this->userRow['name'];
        $save['is_archived'] = 0;

        $topicId = $this->topics->save($save);

        // create new message... read by us
        $newId = $messages->save(array('topics_id' => $topicId, 'created_at' => $stamp, 'name' => $name,
                'user' => $this->userRow['name'], 'read_by' => "!{$this->userRow['id']}!"));


        // create new mailer
        //$mailer = new Mailer();

        // save the text
        $nodes = new Nodes();
        $nodes->saveMessage(0, $this->request->getPost(0), $topicId, $newId, $this->userRow['name'], $mailer);

        // send email to all recipients
        //$users = new Users();
        //foreach ($users->select('email')->findAll() as $user) $recipients[] = $user['email'];
        //$mailer->sendMessage($recipients, $newId, $topicId, $this->topics);

        // redirect to the new message
        $this->redirectTo("messages/show/{$topicId}/{$newId}");
    }

    /** Responsible for providing a form populated with a specific object to edit. */
	public function actionEdit() { }

    /** Receives the form submission from the edit action and updates the specific object. */
	public function actionUpdate() { }

    /** Deletes the specified object from the database. */
	public function actionDelete($topicId) {
        if ($this->topics->set(array('is_archived' => 1))->update()->where($topicId) > 0) {
            $this->flashSuccess = 'The topic has been archived';
        } else {
            $this->flashFail = 'Could not archive the topic';
        }
        $this->redirectTo('/');
    }

}