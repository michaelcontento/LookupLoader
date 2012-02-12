<?php

/**
 * Extracts all available classnames from a single file or a whole directory.
 *
 * @author Michael Contento <michaelcontento@gmail.com>
 * @see    https://github.com/michaelcontento/LookupLoader
 */
class PhpClassExtractor {
    /**
     * @var string
     */
    const CLASS_PATTERN = "/^\s*class (?P<classname>\w+)/im";

    /**
     * @param string $filepath
     * @return array
     */
    public function fromFile($filepath) 
    {
        return $this->getClassNamesInFile($filepath);
    }

    /**
     * @param string $directory
     * @return array
     */
    public function getFromDirectoryRecursive($directory) 
    {
        return $this->getClassMapFromIterator(
            $this->getRecursiveDirectoryIterator($directory)
        );
    }
   
    /**
     * @param string $directory
     * @return Iterator
     */
    public function getRecursiveDirectoryIterator($directory) 
    {
        return new PhpFileFilterIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS 
                )
            )
       );
    }

    /**
     * @param string $filepath
     * @return array 
     */
    private function getClassNamesInFile($filepath) 
    {
        $content = file_get_contents($filepath);
        if ($content === false) {
            return array();
        }

        $matches = array();
        $success = preg_match_all(self::CLASS_PATTERN, $content, $matches);
        if (!$success) {
            return array();
        }

        return $matches["classname"];
    }

    /**
     * @param Iterator $iter
     * @return array
     */
    private function getClassMapFromIterator(Iterator $iter) 
    {
        $map = array();

        foreach ($iter as $filepath => $fileinfo) {
            $foundClassNames = $this->getClassNamesInFile($filepath);
            foreach ($foundClassNames as $classname) {
                // Class does not exists and we just store the filepath
                if (!array_key_exists($classname, $map)) {
                    $map[$classname] = $filepath;
                    continue;
                }

                // We've already found this classname ...

                // ... but this is the second time and we need to create the
                // array in the map for this class
                if (!is_array($map[$classname])) {
                    $map[$classname] = array($map[$classname], $filepath);
                    continue;
                }

                // ... or if there are more than two paths for a file we can
                // simply append the current filepath to the list
                $map[$classname][] = $filepath;
            }
        }

        return $map;
    }
}
