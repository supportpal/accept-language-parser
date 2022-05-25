<?php

namespace SupportPal\AcceptLanguageParser;

class Component
{
    const BCP47_SEPARATOR = '-';
    const ISO15897_SEPARATOR = '_';

    /** @var string */
    private $code;

    /** @var string|null */
    private $script;

    /** @var string|null */
    private $region;

    /** @var float */
    private $quality;

    /**
     * Component constructor.
     *
     * @param string $code
     * @param string|null $script
     * @param string|null $region
     * @param float $quality
     */
    public function __construct($code, $script, $region, $quality)
    {
        $this->code = $code;
        $this->script = $script;
        $this->region = $region;
        $this->quality = $quality;
    }

    /**
     * @return string
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function script()
    {
        return $this->script;
    }

    /**
     * @return string|null
     */
    public function region()
    {
        return $this->region;
    }

    /**
     * @return float
     */
    public function quality()
    {
        return $this->quality;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return array(
            'code'    => $this->code(),
            'script'  => $this->script(),
            'region'  => $this->region(),
            'quality' => $this->quality(),
        );
    }

    /**
     * @return string
     */
    public function toIso15897()
    {
        return str_replace(self::BCP47_SEPARATOR, self::ISO15897_SEPARATOR, $this->__toString());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = $this->code();
        if ($this->script() !== null) {
            $string .= self::BCP47_SEPARATOR . $this->script();
        }

        if ($this->region() !== null) {
            $string .= self::BCP47_SEPARATOR . $this->region();
        }

        return $string;
    }

    /**
     * @param Component $component
     * @return int
     */
    public function compareQuality(Component $component)
    {
        if ($this->quality() === $component->quality()) {
            return 0;
        }

        return $this->quality() > $component->quality() ? -1 : 1;
    }

    /**
     * @param Component $component
     * @param bool $strict
     * @return bool
     */
    public function isEqualTo(Component $component, $strict)
    {
        return strcasecmp($this->code(), $component->code()) === 0
            && (! $strict || $component->script() === null || strcasecmp($this->script(), $component->script()) === 0)
            && (! $strict || $component->region() === null || strcasecmp($this->region(), $component->region()) === 0);
    }

    /**
     * @param string $value
     * @return static
     */
    public static function parse($value)
    {
        $bits = explode(';', $value);
        $ietf = explode(self::BCP47_SEPARATOR, $bits[0]);
        $hasScript = count($ietf) === 3;

        $code    = $ietf[0];
        $script  = static::parseScript($hasScript, $ietf);
        $region  = static::parseRegion($hasScript, $ietf);
        $quality = static::parseQuality($bits);

        return new static($code, $script, $region, $quality);
    }

    /**
     * @param bool $hasScript
     * @param string[] $ietf
     * @return string|null
     */
    private static function parseScript($hasScript, $ietf)
    {
        if ($hasScript && isset($ietf[1])) {
            return $ietf[1];
        }

        return null;
    }

    /**
     * @param bool $hasScript
     * @param string[] $ietf
     * @return string|null
     */
    private static function parseRegion($hasScript, $ietf)
    {
        if ($hasScript) {
            return isset($ietf[2]) ? $ietf[2] : null;
        }

        return isset($ietf[1]) ? $ietf[1] : null;
    }

    /**
     * @param string[] $bits
     * @return float
     */
    private static function parseQuality($bits)
    {
        $default = 1.0;

        if (isset($bits[1])) {
            $components = explode('=', $bits[1]);

            return isset($components[1]) ? @ floatval($components[1]) : $default;
        }

        return $default;
    }
}
