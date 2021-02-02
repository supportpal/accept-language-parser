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
     * @param string[] $supportedLocales
     * @param bool $strict
     * @return Component|null
     */
    public function pick(array $supportedLocales, $strict = true)
    {
        foreach ($supportedLocales as $key => $locale) {
            $supportedLocales[$key] = Component::parse($locale);
        }

        foreach ($this->parse() as $component) {
            foreach ($supportedLocales as $supportedComponent) {
                if ($supportedComponent->isEqualTo($component, $strict)) {
                    return $supportedComponent;
                }
            }
        }

        return null;
    }
}
