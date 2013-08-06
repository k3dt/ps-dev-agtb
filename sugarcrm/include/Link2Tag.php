<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
class Link2Tag
{
    private static $urlRegex = ';\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)));';

    private static $processors = array(
        'Image',
        'OEmbed',
        'OpenGraph',
        'TwitterCard',
        'Hcard',
        'Webpage',
    );

    private static $cache = array();

    /**
     * Converts video, image, and other links embedded in text to html tags.
     * @param string $text
     * @return string
     */
    public static function convert($text)
    {
        if (preg_match(self::$urlRegex, $text, $matches)) {
            foreach (self::$processors as $processor) {
                $ret = call_user_func(__CLASS__ . '::process' . $processor, $matches[1]);
                if (!empty($ret)) {
                    return array(
                        'value' => $text,
                        'embed' => $ret,
                    );
                }
            }
        }
    }

    protected static function processOEmbed($uri)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(self::fetch($uri));
        $linkTags = $dom->getElementsByTagName('link');
        $oembeds = array();
        foreach ($linkTags as $tag) {
            if (!($tag instanceof DOMElement)) {
                continue;
            }

            $type = $tag->getAttribute('type');
            if (strpos($type, '+oembed') !== false) {
                $oembeds[$type] = $tag->getAttribute('href');
            }
        }

        if (isset($oembeds['application/json+oembed'])) {
            return json_decode(self::fetch($oembeds['application/json+oembed']), true);
        }

        if (isset($oembeds['text/xml+oembed'])) {
            $xml = new DOMDocument();
            $xml->loadXML(self::fetch($oembeds['text/xml+oembed']));
            $oembedTags = $xml->getElementsByTagName('oembed');
            $obj = array();
            foreach ($oembedTags as $oembedTag) {
                if (!($oembedTag instanceof DOMElement)) {
                    continue;
                }

                while ($oembedTag->hasChildNodes()) {
                    $child = $oembedTag->firstChild;
                    $obj[$child->nodeName] = $child->nodeValue;
                    $oembedTag->removeChild($child);
                }
            }
            return $obj;
        }
    }

    protected static function processHcard($uri)
    {
        $attributes = array('fn', 'industry', 'title', 'locality', 'photo');
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(self::fetch($uri));
        $xPath = new DOMXPath($dom);
        $basicQuery = '//*[contains(@class,\'vcard\')]';
        $ret = array();

        foreach ($attributes as $attribute) {
            $query = $basicQuery . '//*[contains(@class, \'' . $attribute . '\')]';
            $relevantElements = $xPath->query($query);
            $element = $relevantElements->item(0);
            if ($element instanceof DOMElement) {
                $ret[$attribute] = trim($element->nodeValue);
                if ($attribute === 'photo') {
                    $ret[$attribute] = $element->getAttribute('src');
                }
            }
        }

        if (count($ret)) {
            $ret['type'] = 'hcard';
            $ret['url'] = $uri;
            return $ret;
        }
    }

    protected static function processImage($uri)
    {
        if (preg_match('#(https?://[^\s]+(?=\.(jpe?g|png|gif)))(\.(jpe?g|png|gif))#i', $uri)) {
            return array(
                'type' => 'image',
                'src' => $uri,
            );
        }
    }

    protected static function processTwitterCard($uri)
    {
        $openGraphFallbacks = array('description', 'title', 'image', 'image:width', 'image:height');
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(self::fetch($uri));
        $metaTags = $dom->getElementsByTagName('meta');
        $ogTags = array();
        $ret = array();

        foreach ($metaTags as $metaTag) {
            if (!($metaTag instanceof DOMElement)) {
                continue;
            }

            $property = $metaTag->getAttribute('property');
            if (strpos($property, 'twitter:') === 0) {
                $property = self::processMetaKey(substr($property, 8));
                $content = $metaTag->getAttribute('content');
                $ret[$property] = $content;
            } else {
                if (strpos($property, 'og:') === 0) {
                    $ogTags[] = $metaTag;
                }
            }
        }

        foreach ($openGraphFallbacks as $fallback) {
            $key = self::processMetaKey($fallback);
            if (!isset($ret[$key])) {
                foreach ($ogTags as $ogTag) {
                    if (!($ogTag instanceof DOMElement)) {
                        continue;
                    }

                    $property = $ogTag->getAttribute('property');
                    $property = self::processMetaKey(substr($property, 3));
                    if ($property == $key) {
                        $ret[$key] = $ogTag->getAttribute('content');
                    }
                }
            }
        }

        if (count($ret)) {
            $ret['type'] = 'twitter';
            $ret['url'] = $uri;
            return $ret;
        }
    }

    protected static function processOpenGraph($uri)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(self::fetch($uri));
        $metaTags = $dom->getElementsByTagName('meta');
        $ret = array();
        foreach ($metaTags as $metaTag) {
            if (!($metaTag instanceof DOMElement)) {
                continue;
            }

            $property = $metaTag->getAttribute('property');
            if (strpos($property, 'og:') === 0) {
                $property = self::processMetaKey(substr($property, 3));
                $content = $metaTag->getAttribute('content');
                $ret[$property] = $content;
            }
        }
        if (count($ret) && isset($ret['type']) && isset($ret['og:title'])) {
            return $ret;
        }
    }

    protected static function processWebpage($uri)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(self::fetch($uri));
        $titleTags = $dom->getElementsByTagName('title');
        foreach ($titleTags as $titleTag) {
            if (!($titleTag instanceof DOMElement)) {
                continue;
            }

            return array(
                'type' => 'website',
                'url' => $uri,
                'title' => $titleTag->nodeValue,
            );
        }
    }

    private static function fetch($uri)
    {
        if (!isset(self::$cache[$uri])) {
            $curl = curl_init($uri);

            curl_setopt($curl, CURLOPT_FAILONERROR, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

            $ret = curl_exec($curl);
            if ($ret !== false) {
                // Curl succeeded.
                self::$cache[$uri] = $ret;
            }

            curl_close($curl);
        }

        return self::$cache[$uri];
    }

    private static function processMetaKey($key)
    {
        $parts = explode(':', $key);
        $ret = array_shift($parts);
        foreach ($parts as $part) {
            $ret .= ucfirst($part);
        }
        return $ret;
    }
}
