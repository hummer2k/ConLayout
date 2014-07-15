<?php
namespace ConLayout;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
trait OptionTrait
{
    /**
     * retrieve array option by path
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
        $tempEscaped = '::||::';
        $path = str_replace('\\' . $delimiter, $tempEscaped, $path);
        $path = explode($delimiter, $path);
        for ($i = 0; ($i < count($path) && $found); $i++) {
            $key = str_replace($tempEscaped, $delimiter, $path[$i]);
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
