<?php

namespace luya\components;

use Yii;
use luya\helpers\Url;

class Composition extends \yii\base\Component
{
    /**
     * Enable or disable the $composition->getFull() prefix. If disabled the response of getFull() would be empty, otherwhise it
     * returns the full prefix composition pattern based url.
     *
     * @var bool
     */
    public $hidden = false;

    /**
     * Url matching prefix, which is used for all the modules (e.g. an e-store requireds a language
     * as the cms needs this informations too). After proccessing this informations, they will be removed
     * from the url for further proccessing.
     *
     * The fullqualified composer key will be stored in $request->get('urlPrefixCompositionKey').
     *
     * Examples of how to use urlPrefixComposition
     * ```
     * $urlPrefixComposition = '<langShortCode:[a-z]{2}>/<countryShortCode:[a-z]{2}>'; // de/ch; fr/ch
     * $urlPrefixComposition = '<countryShortCode:[a-z]{2}>-<langShortCode:[a-z]{2}>'; // ch-de; ch-fr
     * ```
     *
     * @var string
     */
    public $pattern = '<langShortCode:[a-z]{2}>';

    /**
     * Default value if there is no composition provided in the url. The default value must match the $pattern url.
     *
     * @var array
     */
    public $default = ['langShortCode' => 'de'];

    /**
     * contains all composition key value pairings.
     *
     * @var array
     */
    private $_composition = [];

    /**
     * @todo should be rewritten soon.
     * @param \yii\web\Request $request
     */
    public function getResolvedPathInfo(\yii\web\Request $request)
    {
        $parts = explode('/', $request->getPathInfo());
        preg_match_all('/<(\w+):?([^>]+)?>/', $this->pattern, $matches, PREG_SET_ORDER);
        $compositionKeys = [];
        foreach ($matches as $index => $match) {
            if (isset($parts[$index])) {
                $urlValue = $parts[$index];
                $rgx = $match[2];
                $param = $match[1];
                preg_match("/^$rgx$/", $urlValue, $res);
        
                if (count($res) == 1) {
                    $compositionKeys[$param] = $urlValue;
                    $this->_resolvedValues[] = $parts[$index];
                    unset($parts[$index]);
                }
            }
        }
        
        if (count($compositionKeys) == 0) {
            $compositionKeys = $this->default;
        }
        
        $this->set($compositionKeys);
        
        return implode('/', $parts);
    }
    
    private $_resolvedValues = [];
    
    public function getResolvedValues()
    {
        return $this->_resolvedValues;
    }
    
    public function setKey($key, $value)
    {
        $this->_composition[$key] = $value;
    }

    public function getKey($key, $defaultValue = false)
    {
        return (isset($this->_composition[$key])) ? $this->_composition[$key] : $defaultValue;
    }

    public function get()
    {
        return $this->_composition;
    }

    public function set($array)
    {
        $this->_composition = $array;
    }

    public function getFull()
    {
        if ($this->hidden) {
            return;
        }

        return Url::trailing(implode('/', $this->_composition)); // Remove trailing slash
    }

    public function getLocale()
    {
        switch ($this->_composition['langShortCode']) {
            case 'de':
                return 'de_DE';
                break;
            case 'en':
                return 'en_EN';
                break;
            case 'fr':
                return 'fr_FR';
                break;
            case 'it':
                return 'it_IT';
                break;
        }
    }

    public function getLanguage()
    {
        return $this->_composition['langShortCode'];
    }
}
