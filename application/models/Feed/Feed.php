<?php

/**
 * Description of Feed.
 *
 * @package Application\Models\Feed
 */
class Feed extends Fari_FeedAtom {

    public function __construct() {
        parent::__construct('Fireside');

        // latest messages
        $messages = new Messages();
        $latestMessages = $messages->find()->orderBy('id DESC')->limit(20)->where(array('id' => '>0'));

        foreach ($latestMessages as $message) {
            $item = array(
                'http://' . $_SERVER['HTTP_HOST'] . WWW_DIR .
                "/messages/show/{$message['topics_id']}/{$message['id']}" => array(
                    'title' => $message['name'],
                    'content' => 'Message by ' . $message['user'] . ' on ' . $message['created_at'],
                )
            );
            $this->atomise($item);
        }

        $this->generate();
    }

}