<?php

namespace SupportPal\AcceptLanguageParser;

/**
 * PHP port of https://github.com/opentable/accept-language-parser/blob/master/index.js
 */
class Parser
{
    /** @var string */
    private $value;

    /**
     * Parser constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return array<Component>
     */
    public function parse()
    {
        $regex = '/((([a-zA-Z]+(-[a-zA-Z0-9]+){0,2})|\*)(;q=[0-1](\.[0-9]+)?)?)*/';

        preg_match_all($regex, $this->value, $matches, PREG_SET_ORDER, 0);

        $locales = array();
        foreach ($matches as $match) {
            if (empty($match[0])) {
                continue;
            }

            $locales[] = Component::parse($match[0]);
        }

        usort($locales, function ($a, $b) {
            /** @var $a Component */
            return $a->compareQuality($b);
        });

        return $locales;
    }

    /**
     * @param string[] $supportedLocales BCP-47 locales.
     * @param bool $strict
     * @return Component|null
     */
    public function pick(array $supportedLocales, $strict = true)
    {
        /** @var Component[] $locales */
        $locales = [];
        foreach ($supportedLocales as $locale) {
            $locales[] = Component::parse($locale);
        }

        foreach ($this->parse() as $component) {
            $match = $this->findClosestMatch($locales, $component, $strict);
            if ($match) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @param string[] $iso15897Locales ISO 15897 locales.
     * @param bool $strict
     * @return Component|null
     */
    public function pickIso15897(array $iso15897Locales, $strict = true)
    {
        // convert to bcp-47
        $bcp47Locales = array_map(function ($locale) {
            return str_replace(Component::ISO15897_SEPARATOR, Component::BCP47_SEPARATOR, $locale);
        }, $iso15897Locales);

        return $this->pick($bcp47Locales, $strict);
    }

    /**
     * @param Component[] $components
     * @param Component $component
     * @param bool $strict
     * @return Component|null
     */
    private function findClosestMatch(array $components, Component $component, $strict)
    {
        $results = [];
        foreach ($components as $supportedComponent) {
            if ($supportedComponent->isEqualTo($component, $strict)) {
                $results[] = $supportedComponent;
            }
        }

        /** @var Component $result */
        foreach ($results as $result) {
            if ($result->isEqualTo($component, true)) {
                return $result;
            }
        }

        return isset($results[0]) ? $results[0] : null;
    }
}
