<?php

/**
 * Data Format Helpers
 *
 * Provides utility functions for converting between different data formats.
 */
if (! function_exists('xml_to_array')) {
    /**
     * Convert a SimpleXMLElement to an associative array
     *
     * @param  SimpleXMLElement  $xml  The XML element to convert
     * @return array The converted array with XML data
     */
    function xml_to_array(SimpleXMLElement $xml)
    {
        $parser = function (SimpleXMLElement $xml, array $collection = []) use (&$parser) {
            $nodes = $xml->children();
            $attributes = $xml->attributes();

            if (count($attributes) !== 0) {
                foreach ($attributes as $attrName => $attrValue) {
                    $collection['attributes'][$attrName] = html_entity_decode(strval($attrValue));
                }
            }

            if ($nodes->count() === 0) {
                return html_entity_decode(strval($xml));
            }

            foreach ($nodes as $nodeName => $nodeValue) {
                if (count($nodeValue->xpath('../'.$nodeName)) < 2) {
                    $collection[$nodeName] = $parser($nodeValue);

                    continue;
                }

                $collection[$nodeName][] = $parser($nodeValue);
            }

            return $collection;
        };

        return [
            $xml->getName() => $parser($xml),
        ];
    }
}
