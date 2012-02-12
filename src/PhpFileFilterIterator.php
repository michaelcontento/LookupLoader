<?php

/**
 * Simple filter iterator that skips all files without the ".php" extension.
 *
 * @author Michael Contento <michaelcontento@gmail.com>
 * @see    https://github.com/michaelcontento/LookupLoader
 */
class PhpFileFilterIterator extends FilterIterator 
{
    /**
     * @return boolean
     */
    public function accept() 
    {
        // TODO: getFilename() is a currently a hidden dependency to the
        // FilterIterator or DirectoryIterator. Check the instance of current
        // element OR refactor this somehow.
        $ext = pathinfo($this->current()->getFilename(), PATHINFO_EXTENSION);
        return strtolower($ext) == "php";
    }
}
