<?php

/**
 * Description of Layouts.
 *
 * @package Application\Models\Layouts
 */
class Layouts extends Fari_File {

    /**
     * Overwrite in children to parse files according to our style.
     * @param array $listing
     * @param DirectoryIterator $item
     */
    public function pushItem(&$listing, DirectoryIterator $entry) {
        if (substr($filename = $entry->getFilename(), -4) == '.css') {
            // a reset stylesheet, always used
            if (strpos($stylename = $entry->getBasename('.css'), 'tripoli') !== FALSE);
            else array_push($listing, $stylename);
        }
    }

}