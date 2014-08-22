<?php

/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */
class Mig_Merger
{
    /**
     * The migrations directory
     *
     * @var String
     */
    protected $_dir = null;

    /**
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->_dir = $dir;
    }

    /**
     * @return array
     */
    public function getMergedBodies()
    {
        $files = glob($this->_dir . '/*.php');
        $ups = array();
        $downs = array();

        foreach ($files as $file) {
            $nameArr = explode('_', basename($file));
            $className = 'Migration_' . $nameArr[0];

            require_once($file);

            $class = new ReflectionClass($className);

            $ups[] = '        // From original migration file: ' . basename($file);
            $ups[] = $this->extractMethodBody($file, $class->getMethod('up'));

            $downs[] = $this->extractMethodBody($file, $class->getMethod('down'));
            $downs[] = '        // From original migration file: ' . basename($file);
        }

        return array(
            'up' => implode("\n\n", $ups),
            'down' => implode("\n\n", array_reverse($downs))
        );
    }

    /**
     * @param $file
     * @param ReflectionMethod $method
     * @return string
     */
    public function extractMethodBody($file, ReflectionMethod $method)
    {
        $start = $method->getStartLine() + 1;
        $length = $method->getEndLine() - 1 - $start;
        $source = file($file);

        return implode("", array_slice($source, $start, $length));
    }
}
