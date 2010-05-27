<?php

/**
 * Description of Nodes.
 *
 * @package Application\Models\Nodes
 */
class Nodes extends Table {

    /** @var string table name */
    public $tableName = 'nodes';

    /** @var string primary key */
    public $primaryKey = 'id';

    private $filters = array();

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

    public function saveMessage($parentId, $text, $topicId, $messageId, $userName, &$mailer=NULL) {
        // explode text based on paragraphs to get nodes... for now
        $nodes = explode("\n", $text);
        foreach ($nodes as $node) {
            // we could have blank lines...
            $node = trim($node);
            if (!empty($node)) {
                // apply filters to the text
                $node = $this->applyFilters($node);
                // save new node
                $nodeId = $this->save(array('topics_id' => $topicId, 'text' => $node,
                        'messages_id' => $messageId, 'user' => $userName));
                // update the parrent
                $parentText = $this->updateParent($parentId, $nodeId);

                // add node to the mail message
                if ($mailer instanceof Mailer) $mailer->addMessageNode($node, $parentText);
            }
        }
    }

    private function applyFilters($text) {
        foreach ($this->filters as $filter) {
            $filter = 'filter' . ucfirst($filter);
            $text = $this->$filter($text);
        }
        return $text;
    }

    private function filterLinkify($text) {
        $urls = explode(' ', $text); $containsLink = FALSE;
        foreach ($urls as &$link) {
            if (Fari_Filter::isURL($link)) {
                $containsLink = TRUE;

                // do we have a YouTube video?
                // source: http://www.youtube.com/watch?v=nBBMnY7mANg&feature=popular
                // target: <img src="http://img.youtube.com/vi/nBBMnY7mANg/0.jpg" alt="0">
                if (stripos(strtolower($link), 'youtube') !== FALSE) {
                    $url = parse_url($link);
                    parse_str($url[query], $query);
                    // replace link with an image 'boosted' link :)
                    $link = '<a class="youtube" target="_blank" href="' . $link .
                            '"><img src="http://img.youtube.com/vi/' . $query['v'] . '/0.jpg" alt="YouTube"></a>';
                } else {
                    // plain old link
                    $link = '<a target="_blank" href="' . $link . '">' . $link . '</a>';
                }

                // convert so we can insert into DB
                $link = Fari_Escape::html($link);
            }
        }
        if ($containsLink) return implode(' ', $urls);
        else return $text;
    }

    private function updateParent($parentId, $nodeId) {
        // we use '0' to save new messages under
        if ($parentId > 0) {
            $parentNode = $this->findFirst()->where($parentId);
            $parentNode['child_id'] = (empty($parentNode['child_id'])) ? $nodeId : "{$parentNode['child_id']},{$nodeId}";
            $this->set($parentNode)->update()->where($parentId);

            return $parentNode['text'];
        } else {
            return '';
        }
    }

}