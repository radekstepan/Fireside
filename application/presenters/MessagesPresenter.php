<?php if (!defined('FARI')) die();

/**
 * Description of Messages.
 *
 * @package   Application\Presenters
 */
final class MessagesPresenter extends Fari_ApplicationPresenter {

    private $user;
    private $userRow;
    
    private $messages;
    private $topics;
    private $nodes;

    /********************* filters *********************/

    public $beforeFilter = array(
        array('userRow' => array('show', 'create'))
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

        $this->messages = new Messages();
        $this->topics = new Topics();
        $this->nodes = new Nodes();
    }

    public function filterUserRow() {
        $this->bag->userRow = $this->userRow = $this->user->getUser();
    }

    /********************* actions *********************/

    /** Responsible for presenting a collection back to the user. */
	public function actionIndex($p) { }

    /** Responsible for showing a single specific object to the user. */
	public function actionShow($topicId, $currentMessageId) {
        // fetch the oldest unread message...
        if ($currentMessageId == 'unread') {
            // fetch 2 actually so we can link to a newer message also
            $result = $this->messages->find()->limit(2)->orderBy('created_at ASC')
                ->where(array('topics_id' => $topicId, 'read_by' => "!*!{$this->userRow['id']}!*"));
            $message = $result[0];
            // the first newer message
            $this->bag->newerMessage = $result[1];
        } else {
            $message = $this->messages->findFirst()->where($currentMessageId);
            // the first newer message
            $this->bag->newerMessage = $this->messages->findFirst()->orderBy('created_at ASC')
                ->where(array('topics_id' => $topicId, 'read_by' => "!*!{$this->userRow['id']}!*",
                        'id' => "NOT IN({$currentMessageId})"));
        }

        if (!empty($message)) {
            $this->bag->message = $message;
            
            // prepend (optional) previous message
            if (($this->bag->previousMessage = $previousMessage = $this->messages->findLast()->where(
                array('topics_id' => $topicId, 'child_id' => "*!{$message['id']}!*")
            ))) $messageId = "{$previousMessage['id']}," . $message['id'];
            else $messageId = $message['id'];

            $result = $this->nodes->find()->where(array(
                    'topics_id' => $topicId, 'messages_id' => "IN ({$messageId})"
                ));
            $this->bag->nodes = $result;
            $this->bag->count = count($result);

            $this->bag->topic = $this->topics->findFirst()->where($topicId);

            // mark the message as read
            $this->messages->markRead($message['id'], $message['read_by'], $this->userRow['id']);

            $this->renderAction();
        } else {
            $this->redirectTo("topics/{$topicId}");
        }
    }

    /** Responsible for providing the user with an empty form to create a new object. */
	public function actionNew() { }

    /** Receives the form submission from the new action and creates the new object. */
	public function actionCreate($topicId, $messageId) {
        $stamp = date("Y-m-d H:i:s", mktime());

        // form a new message name
        $name = $this->messages->generateNewName($this->request->getPost());
        if (!empty($name)) {
            // create new message... read by us
            $newId = $this->messages->save(array('topics_id' => $topicId, 'created_at' => $stamp,
                    'user' => $this->userRow['name'], 'read_by' => "!{$this->userRow['id']}!", 'name' => $name));

            // update our parent
            $parent = $this->messages->findFirst()->where($messageId);
            // (append) new child_id field...
            // ...add delimiter on both sides so we have an exact LIKE query when searching ;)
            $childId = (!empty($parent['child_id'])) ? $parent['child_id'].$newId.'!' : '!'.$newId.'!';
            $this->messages->update()->set(array('child_id' => $childId))->where($messageId);

            // update the topic timestamp and user
            $this->topics->update()->set(array('updated_at' => $stamp, 'updated_by' => $this->userRow['name']))
                ->where($topicId);

            // create new mailer
            //$mailer = new Mailer();

            // save the text
            foreach ($this->request->getPost() as $parentId => $text) {
                $this->nodes->saveMessage($parentId, $text, $topicId, $newId, $this->userRow['name'], $mailer);
            }

            // send email to all recipients
            //$users = new Users();
            //foreach ($users->select('email')->findAll() as $user) $recipients[] = $user['email'];
            //$mailer->sendMessage($recipients, $newId, $topicId, $this->topics);

            // redirect to the new message
            $this->redirectTo("messages/show/{$topicId}/{$newId}");
        } else {
            $this->flashFail = 'The message cannot be blank';
            $this->redirectTo("messages/show/{$topicId}/{$messageId}");
        }
    }

    /** Responsible for providing a form populated with a specific object to edit. */
	public function actionEdit() { }

    /** Receives the form submission from the edit action and updates the specific object. */
	public function actionUpdate() { }

    /** Deletes the specified object from the database. */
	public function actionDelete() { }

    public function actionPing() {
        $this->renderJson('pong');
    }

}