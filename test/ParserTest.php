<?php

namespace SupportPal\AcceptLanguageParser\Tests;

use PHPUnit\Framework\TestCase;
use SupportPal\AcceptLanguageParser\Component;
use SupportPal\AcceptLanguageParser\Parser;

class ParserTest extends TestCase
{
    /**
     * @dataProvider parseProvider
     *
     * @param string $header
     * @param Component[] $expected
     */
    public function testParse($header, array $expected)
    {
        $parser = new Parser($header);
        $result = $parser->parse();
        foreach ($expected as $key => $component) {
            $this->assertSame($component->code(), $result[$key]->code());
            $this->assertSame($component->script(), $result[$key]->script());
            $this->assertSame($component->region(), $result[$key]->region());
            $this->assertSame($component->quality(), $result[$key]->quality());
        }
    }

    public function parseProvider()
    {
        return array(
            array('en-GB;q=0.8', array(new Component('en', null, 'GB', 0.8))),
            array('en-GB', array(new Component('en', null, 'GB', 1.0))),
            array('en;q=0.8', array(new Component('en', null, null, 0.8))),
            array('az-AZ', array(new Component('az', null, 'AZ', 1.0))),
            array(
                'fr-CA,fr;q=0.8',
                array(
                    new Component('fr', null, 'CA', 1.0),
                    new Component('fr', null, null, 0.8),
                ),
            ),
            array(
                'fr-CA,*;q=0.8',
                array(
                    new Component('fr', null, 'CA', 1.0),
                    new Component('*', null, null, 0.8),
                ),
            ),
            array('fr-150', array(new Component('fr', null, '150', 1.0))),
            array(
                'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4,*;q=0.1',
                array(
                    new Component('fr', null, 'CA', 1.0),
                    new Component('fr', null, null, 0.8),
                    new Component('en', null, 'US', 0.6),
                    new Component('en', null, null, 0.4),
                    new Component('*', null, null, 0.1),
                ),
            ),
            array(
                'fr-CA, fr;q=0.8,  en-US;q=0.6,en;q=0.4,    *;q=0.1',
                array(
                    new Component('fr', null, 'CA', 1.0),
                    new Component('fr', null, null, 0.8),
                    new Component('en', null, 'US', 0.6),
                    new Component('en', null, null, 0.4),
                    new Component('*', null, null, 0.1),
                )
            ),
            array(
                'fr-CA,fr;q=0.2,en-US;q=0.6,en;q=0.4,*;q=0.5',
                array(
                    new Component('fr', null, 'CA', 1.0),
                    new Component('en', null, 'US', 0.6),
                    new Component('*', null, null, 0.5),
                    new Component('en', null, null, 0.4),
                    new Component('fr', null, null, 0.2),
                ),
            ),
            array('zh-Hant-cn', array(new Component('zh', 'Hant', 'cn', 1.0))),
            array(
                'zh-Hant-cn;q=1, zh-cn;q=0.6, zh;q=0.4',
                array(
                    new Component('zh', 'Hant', 'cn', 1.0),
                    new Component('zh', null, 'cn', 0.6),
                    new Component('zh', null, null, 0.4),
                )
            )
        );
    }

    /**
     * @dataProvider pickProvider
     *
     * @param string[] $supported
     * @param bool $strict
     * @param string $header
     * @param string $expected
     */
    public function testPick($supported, $strict, $header, $expected)
    {
        $parser = new Parser($header);
        $result = $parser->pick($supported, $strict);

        $this->assertSame($expected, (string) $result);
    }

    public function pickProvider()
    {
        return array(
            array(
                array('en-US', 'fr-CA'),
                true,
                'fr-CA,fr;q=0.2,en-US;q=0.6,en;q=0.4,*;q=0.5',
                'fr-CA'
            ),
            array(
                array('zh-Hant-cn', 'zh-cn'),
                true,
                'zh-Hant-cn,zh-cn;q=0.6,zh;q=0.4',
                'zh-Hant-cn',
            ),
            array(
                array('eN-Us', 'Fr-cA'),
                true,
                'fR-Ca,fr;q=0.2,en-US;q=0.6,en;q=0.4,*;q=0.5',
                'Fr-cA',
            ),
            array(
                array('en', 'fr-CA'), true, 'ja-JP,ja;1=0.5,en;q=0.2', 'en',
            ),
            array(
                array('en-us', 'it-IT'), true, 'pl-PL,en', 'en-us',
            ),
            array(
                array('ko-KR'), true, 'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4,*;q=0.1', ''
            ),
            array(
                array(), true, 'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4,*;q=0.1', ''
            ),
            array(
                array('en'), true, '', ''
            ),
            array(
                array('en', 'pl'), true, 'en-US;q=0.6', '',
            ),
            // loose mode
            array(
                array('en', 'pl'), false, 'en-US;q=0.6', 'en',
            ),
            array(
                array('en-US', 'en', 'pl'), false, 'en-US;q=0.6', 'en-US',
            ),
            array(
                array('en', 'en-US', 'pl'), false, 'en-US;q=0.6', 'en-US',
            )
        );
    }

    /**
     * @dataProvider pickIso897Provider
     *
     * @param string[] $supported
     * @param bool $strict
     * @param string $header
     * @param string $expected
     */
    public function testPickIso15897($supported, $strict, $header, $expected)
    {
        $parser = new Parser($header);
        $result = $parser->pickIso15897($supported, $strict);

        $this->assertSame($expected, $result ? $result->toIso15897() : '');
    }

    public function pickIso897Provider()
    {
        return array(
            array(
                array('en_US', 'fr_CA'),
                true,
                'fr-CA,fr;q=0.2,en-US;q=0.6,en;q=0.4,*;q=0.5',
                'fr_CA'
            ),
            array(
                array('zh_Hant_cn', 'zh_cn'),
                true,
                'zh-Hant-cn,zh-cn;q=0.6,zh;q=0.4',
                'zh_Hant_cn',
            ),
            array(
                array('en_us', 'it_IT'), true, 'pl-PL,en', 'en_us',
            ),
            array(
                array('ko_KR'), true, 'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4,*;q=0.1', ''
            ),
            array(
                array(), true, 'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4,*;q=0.1', ''
            ),
            array(
                array('en'), true, '', ''
            ),
            array(
                array('en', 'pl'), true, 'en-US;q=0.6', '',
            ),
            array(
                array('en', 'pl'), false, 'en-US;q=0.6', 'en', // loose mode
            ),
            array(
                array('en_US', 'en', 'pl'), false, 'en-US;q=0.6', 'en_US', // loose mode
            )
        );
    }
}
