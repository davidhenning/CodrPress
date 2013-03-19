<?php

namespace CodrPress;

use Collection\MutableMap;

use Symfony\Component\Yaml\Yaml;

class Config extends MutableMap
{

    public function addConfigFile($resource = null)
    {
        if (!is_file($resource)) {
            throw new \InvalidArgumentException('YAML resource "' . $resource . '" does not exist.');
        }

        $data = Yaml::parse($resource, true);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('YAML resource "' . $resource . '" is not a collection of values.');
        }

        $this->updateProperties($data);
    }

    public function setBaseDir($baseDir)
    {
        $this->setProperty('BaseDir', $baseDir);
    }

    public function getBaseDir()
    {
        return $this->getProperty('BaseDir');
    }

    public function getConfDir()
    {
        return realpath($this->getBaseDir() . '/conf/');
    }

    public function sanitize($data)
    {
        if ($data === null) {
            return null;
        }

        if (is_array($data)) {
            $sanitizedData = array();
            foreach ($data as $key => $value) {
                $sanitizedData[$key] = $this->sanitize($value);
            }

            return $sanitizedData;
        }

        $data = trim($data);
        $data = rawurldecode($data);
        $data = htmlspecialchars($data);
        $data = strip_tags($data);

        return $data;
    }
}
