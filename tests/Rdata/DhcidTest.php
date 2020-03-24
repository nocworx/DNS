<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\DHCID;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class DhcidTest extends TestCase
{
    public function getDataProvider(): array
    {
        return [
            //[Text,                                             IDType, Identifier,                                  FQDN]
            ['AAIBY2/AuCccgoJbsaxcQc9TUapptP69lOjxfNuVAA2kjEA=', 2,      '00:01:00:06:41:2d:f1:66:01:02:03:04:05:06', 'chi6.example.com.'],
            ['AAEBOSD+XR3Os/0LozeXVqcNc7FwCfQdWL3b/NaiUDlW2No=', 1,      '01:07:08:09:0a:0b:0c',                      'chi.example.com.'],
            ['AAABxLmlskllE0MVjd57zHcWmEH3pCQ6VytcKD//7es/deY=', 0,      '01:02:03:04:05:06',                         'client.example.com.'],
        ];
    }

    public function testGetType(): void
    {
        $dhcid = new DHCID();
        $this->assertEquals('DHCID', $dhcid->getType());
    }

    public function testGetTypeCode(): void
    {
        $dhcid = new DHCID();
        $this->assertEquals(49, $dhcid->getTypeCode());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string $text
     * @param int    $identifierType
     * @param string $identifier
     * @param string $fqdn
     */
    public function testToText(string $text, int $identifierType, string $identifier, string $fqdn): void
    {
        $dhcid = new DHCID();
        $dhcid->setIdentifier($identifierType, $identifier);
        $dhcid->setFqdn($fqdn);

        $this->assertEquals($text, $dhcid->toText());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string $text
     * @param int    $identifierType
     * @param string $identifier
     * @param string $fqdn
     */
    public function testToFromWire(string $text, int $identifierType, string $identifier, string $fqdn): void
    {
        $expectation = new DHCID();
        $expectation->setIdentifier($identifierType, $identifier);
        $expectation->setFqdn($fqdn);

        $dhcid = new DHCID();
        $dhcid->fromWire($expectation->toWire());

        $this->assertEquals($expectation->getIdentifierType(), $dhcid->getIdentifierType());
        $this->assertEquals($expectation->getDigestType(), $dhcid->getDigestType());
        $this->assertEquals($expectation->getDigest(), $dhcid->getDigest());
        $this->assertEquals($expectation->toText(), $dhcid->toText());
        $this->assertEquals($text, $dhcid->toText());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string $text
     * @param int    $identifierType
     * @param string $identifier
     * @param string $fqdn
     *
     * @throws \Exception
     */
    public function testFromText(string $text, int $identifierType, string $identifier, string $fqdn): void
    {
        $expectation = new DHCID();
        $expectation->setIdentifier($identifierType, $identifier);
        $expectation->setFqdn($fqdn);
        $expectation->calculateDigest();

        $dhcid = new DHCID();
        $dhcid->fromText($text);

        $this->assertEquals($expectation->getIdentifierType(), $dhcid->getIdentifierType());
        $this->assertEquals($expectation->getDigestType(), $dhcid->getDigestType());
        $this->assertEquals($expectation->getDigest(), $dhcid->getDigest());
        $this->assertEquals($expectation->toText(), $dhcid->toText());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string $text
     * @param int    $identifierType
     * @param string $identifier
     * @param string $fqdn
     */
    public function testFactory(string $text, int $identifierType, string $identifier, string $fqdn): void
    {
        $dhcid = Factory::DHCID(null, $identifierType, $identifier, $fqdn);
        $this->assertEquals($text, $dhcid->toText());

        $digest = $dhcid->getDigest();
        $_dhcid = Factory::DHCID($digest, $identifierType);

        $this->assertEquals($dhcid->toText(), $_dhcid->toText());
    }

    public function testFactoryThrowsExceptionIfIdAndFqdnAreNull(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier and FQDN cannot be null if digest is null.');
        $dhcid = Factory::DHCID(null, 2, null);
    }
}
