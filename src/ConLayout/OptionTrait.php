<?php
namespace ConLayout;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
trait OptionTrait
{
    /**
     * 
     * @param array $data
     * @param string $path
     * @param mixed $default
     * @param string $delimiter
     * @return mixed
     */
    protected function getOption(array $data, $path, $default = null, $delimiter = '/')
    {
        $found = true;
        $path = explode($delimiter, $path);
        for ($x = 0; ($x < count($path) and $found); $x++) {
            $key = $path[$x];
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $found = false;
            }
        }
        if ($found === false) {
            return $default;
        }
        return $data;
    }
}
