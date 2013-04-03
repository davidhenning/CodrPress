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

        $this->update($data);
    }

    public function setBaseDir($baseDir)
    {
        $this->set('BaseDir', $baseDir);
    }

    public function getBaseDir()
    {
        return $this->get('BaseDir');
    }

    public function getConfDir()
    {
        return realpath($this->getBaseDir() . '/config/');
    }

    public function sanitize($data)
    {
        if ($data === null) {
            return null;
        }

        if (is_array($data)) {
            $sanitizedData = [];

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
