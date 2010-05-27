<?php

/**
 * Description of Users.
 *
 * @package Application\Models\Users
 */
class Users extends Table {

    /** @var string table name */
    public $tableName = 'users';

    /** @var string primary key */
    public $primaryKey = 'id';

    /********************* relationships *********************/

    /** @example: $this->findAddresses()->where(1); // will associate with table 'addresses' */

    /** @var array a "one-to-one association" with another table(s) through primary keys */
    //public $hasOne;

    /** @var array a "one-to-many association" with another table(s), e.g. a blog post has many comments */
    //public $hasMany;

    /********************* validation *********************/

    /** @var array validates the presence of column data */
    //public $validatesPresenceOf = array('id');

    /** @var array validates the length of columns */
    //public $validatesLengthOf = array(array('password' => 5));

    /** @var array validates uniqueness of columns */
    //public $validatesUniquenessOf = array('username');

    /** @var array validates regex format of a column */
    //public $validatesFormatOf = array(array('zip' => '/^([0-9]{5})(-[0-9]{4})?$/i'));

}