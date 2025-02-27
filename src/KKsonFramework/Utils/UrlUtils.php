<?php

namespace KKsonFramework\Utils;

use Stringy\Stringy;

class UrlUtils
{
    public static function baseUrl($fromRoot = false, bool $prefixSlash = true) : string {
        $requestURI = new Stringy($_SERVER["REQUEST_URI"]);
        $scriptName = Stringy::create($_SERVER["SCRIPT_NAME"]);
        $scriptUrl = Stringy::create($_SERVER["SCRIPT_NAME"]);

        $containsScriptName = $requestURI->contains($_SERVER["SCRIPT_NAME"]);
        $isScriptNameIndex = $scriptName->endsWith("/index.php");
        
        if($isScriptNameIndex) {
            $scriptUrl = $scriptName->replaceFirst("/index.php", "");
        } else {
            $scriptUrl = $scriptName->replaceFirst(".php", "");
        }

        $baseRelativeURI = $requestURI->beforeFirst($scriptUrl);
        
        if(!$containsScriptName && !$isScriptNameIndex) {
            $baseRelativeURI = $baseRelativeURI->append("/".pathinfo($_SERVER["SCRIPT_NAME"], PATHINFO_FILENAME));
        }

        if($fromRoot) {
            if($containsScriptName) {
                $baseRelativeURI = $baseRelativeURI->prepend($_SERVER["SCRIPT_NAME"]);
            } else {
                $scriptDir = dirname($_SERVER["SCRIPT_NAME"]);
                if($scriptDir !== "/") {
                    $baseRelativeURI = $baseRelativeURI->prepend($scriptDir);
                }
            }
        }

        if(!$prefixSlash) {
            $baseRelativeURI = $baseRelativeURI->replaceFirst("/", "");
        }

        return $baseRelativeURI->__toString();
    }

    public static function url(?string $relativePath, $fromRoot = false, $prefixSlash = true) : string
    {
        if($relativePath == null) {
            $relativePath = "";
        }
        // Remove the first slash
        $relativePath = ltrim($relativePath, '/');
        $baseUrl = self::baseUrl($fromRoot, $prefixSlash);
        
        if($relativePath == "") {
            $url = $baseUrl;
        } else {
            $url = $baseUrl . "/" . $relativePath;
        }

        if(!$prefixSlash) {
            $url = Stringy::create($url)->replaceFirst("/", "")->__toString();
        }

        return $url;
    }

    public static function burl(?string $relativePath, $prefixSlash = true) {
        return self::url($relativePath, true, $prefixSlash);
    }

    /**
     * Protocol "function" from http://stackoverflow.com/questions/4503135/php-get-site-url-protocol-http-vs-https
     * @param $relativePath
     * @return string
     */
    public static function fullURL($relativePath, $useBaseUrl = false) {

        // Remove the first slash
        $relativePath = ltrim($relativePath, '/');

        if (self::isSSL()) {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }

        return $protocol . $_SERVER["SERVER_NAME"] . (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" ? ":" . $_SERVER["SERVER_PORT"] : "") . self::url($relativePath, $useBaseUrl);
    }

    /**
     * Relative Path for resources (jpg, png etc)
     * @param $relativePath
     * @return string
     */
    public static function res($relativePath)
    {
        $segments = explode("/", $_SERVER["SCRIPT_NAME"]);
        $phpFile = $segments[count($segments) - 1];

        return str_replace($phpFile, "", $_SERVER["SCRIPT_NAME"]) . $relativePath;
    }
    
    
    public static function fullRes($relativePath) {

        // Remove the first slash
        $relativePath = ltrim($relativePath, '/');

        if (self::isSSL()) {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }
        
        return $protocol . $_SERVER["SERVER_NAME"] . (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443" ? ":" . $_SERVER["SERVER_PORT"] : "") . self::res($relativePath);
    }
    

    public static function isSSL() {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            return true;
        } else if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") {
            return true;
        }
        return false;
    }
}