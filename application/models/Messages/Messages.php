<?php

/**
 * Description of Messages.
 *
 * @package Application\Models\Messages
 */
class Messages extends Table {

    /** @var string table name */
    public $tableName = 'messages';

    /** @var string primary key */
    public $primaryKey = 'id';

    /********************* relationships *********************/

    /** @example: $this->findAddresses()->where(1); // will associate with table 'addresses' */

    /** @var array a "one-to-one association" with another table(s) through primary keys */
    //public $hasOne;

    /** @var array a "one-to-many association" with another table(s), e.g. a blog post has many comments */
    public $hasMany = array('nodes');

    /********************* validation *********************/

    /** @var array validates the presence of column data */
    //public $validatesPresenceOf = array('id');

    /** @var array validates the length of columns */
    //public $validatesLengthOf = array(array('password' => 5));

    /** @var array validates uniqueness of columns */
    //public $validatesUniquenessOf = array('username');

    /** @var array validates regex format of a column */
    //public $validatesFormatOf = array(array('zip' => '/^([0-9]{5})(-[0-9]{4})?$/i'));

    public function markRead($messageId, $readBy, $userId) {
        // have we read this?
        if (empty($readBy) || strpos($readBy, "!{$userId}!") === FALSE) {
            // add our id to the bunch
            $readBy .= "!{$userId}!";
            // strip potential double '!'
            $readBy = str_replace("!!", "!", $readBy);
            // update
            $this->update()->set(array('read_by' => $readBy))->where($messageId);
        }
    }

    public function generateNewName(array $posted) {
        foreach ($posted as $parent => $text) {
            if (strlen($text) > $longest) {
                $longest = strlen($name = preg_replace('/\s\s+/', ' ', $text));
            }
        }
        return ($longest > 70) ? substr($name, 0, 70) . '...' : $name;
    }

}