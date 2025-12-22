<?php

namespace App\Http\Helpers;

class XmlResponseHelper
{
    /**
     * Convert an array or object to XML string.
     * @param string $rootElement
     * @param array|object $data
     * @return string
     */
    public static function toXml($rootElement, $data)
    {
        $xml = new \SimpleXMLElement("<$rootElement/>");
        self::arrayToXml((array)$data, $xml);
        return $xml->asXML();
    }

    private static function arrayToXml(array $data, \SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                // Use singular for numeric keys (for lists)
                $childKey = is_numeric($key) ? 'item' : $key;
                $child = $xml->addChild($childKey);
                self::arrayToXml((array)$value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
    }
}
