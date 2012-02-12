<?php

/**
 * Simple hashtable based autoloader for PHP.
 *
 * Example:
 *   // Instanciate and register the loader 
 *   $loader = new LookupLoader();
 *   $loader->register();
 *   
 *   // And simply add some rules
 *   $loader->add("MyClass", "file_with_my_class.php");
 *
 *   // This should work now
 *   $myClass = new MyClass();
 *
 * @author Michael Contento <michaelcontento@gmail.com>
 * @see    https://github.com/michaelcontento/LookupLoader
 */
class LookupLoader
{
    /**
     * @var array
     */
    private $mappings = array();

    /**
     * @param string $classname
     * @param string $filepath
     * @return LookupLoader
     */
    public function add($classname, $filepath)
    {
        if (array_key_exists($classname, $this->mappings)) {
            throw new RuntimeException(
                "A mapping for class $classname already exists."
            );
        }

        return $this->set($classname, $filepath);
    }

    /**
     * @param string $classname
     * @param string $filepath
     * @return LookupLoader
     */
    public function set($classname, $filepath) 
    {
        $this->mappings[$classname] = $filepath;
        return $this;
    }

    /**
     * @param string $classname
     * @return string
     */
    public function get($classname) 
    {
        if (!array_key_exists($classname, $this->mappings)) {
            throw new OutOfBoundsException(
                "No filepath for class '$classname' registered yet."
            );
        }

        return $this->mappings[$classname];
    }

    /**
     * @param string $classname
     * @return LookupLoader
     */
    public function delete($classname) 
    {
        unset($this->mappings[$classname]);
        return $this;
    }

    /**
     * @return LookupLoader
     */
    public function deleteAll() 
    {
        $this->mappings = array();
        return $this;
    }

    /**
     * @return LookupLoader
     */
    public function register() 
    {
        spl_autoload_register(array($this, "load"), true);
        return $this;
    }

    /**
     * @return LookupLoader
     */
    public function unregister() 
    {
        spl_autoload_unregister(array($this, "load")); 
        return $this;  
    }

    /**
     * @param string $classname
     * @return boolean
     */
    public function load($classname) 
    {
        if (class_exists($classname, false) 
        || interface_exists($classname, false)) {
            return true;
        }

        include $this->mapping[$classname];

        return class_exists($classname, false) 
            || interface_exists($classname, false);
    }

    /**
     * @param string $filepath
     * @return LookupLoader
     */
    public function loadFromFile($filepath) 
    {
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException(
                "Unable to find a file called '$filepath'."
            );
        }

        if (!is_readable($filepath)) {
            throw new InvalidArgumentException(
                "Given file '$filepath' is not readable." 
            );
        }
    
        $newMappings = include($filepath);
        if (!is_array($newMappings)) {
            throw new RuntimeException(
                "Specified file '$filepath' does not return a proper array."
            );
        }

        $this->mappings = $newMappings;
        return $this;
    }

    /**
     * @param string $filepath
     * @return LookupLoader
     */
    public function saveToFile($filepath) 
    {
        $bytesWritten = file_put_contents(
            $filepath,
            "<?php\n"
            . "// Classmap for LookupLoader\n"
            . "// Date of generation: " . date("c") . "\n"
            . "return " . var_export($this->mappings, true) . ";",
            LOCK_EX
        );

        if (!$bytesWritten) {
            throw new RuntimeException(
                "Error while saveing new classmap file to '$filepath'."
            );
        }

        return $this;
    }
}
