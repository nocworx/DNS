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

namespace Badcow\DNS\Rdata;

use Badcow\DNS\Parser\Tokens;

/**
 * {@link https://tools.ietf.org/html/rfc7477}.
 */
class CSYNC implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CSYNC';
    const TYPE_CODE = 62;

    /**
     * @var int
     */
    private $soaSerial;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var array
     */
    private $types = [];

    /**
     * @param string $type
     */
    public function addType(string $type): void
    {
        $this->types[] = $type;
    }

    /**
     * Clears the types from the RDATA.
     */
    public function clearTypes(): void
    {
        $this->types = [];
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return int
     */
    public function getSoaSerial(): int
    {
        return $this->soaSerial;
    }

    /**
     * @param int $soaSerial
     */
    public function setSoaSerial(int $soaSerial): void
    {
        $this->soaSerial = $soaSerial;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %d %s', $this->soaSerial, $this->flags, implode(Tokens::SPACE, $this->types));
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        return pack('Nn', $this->soaSerial, $this->flags).NSEC::renderBitmap($this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setSoaSerial((int) array_shift($rdata));
        $this->setFlags((int) array_shift($rdata));
        array_map([$this, 'addType'], $rdata);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnsupportedTypeException|DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $integers = unpack('Nserial/nflags', $rdata, $offset);
        $offset += 6;
        $types = NSEC::parseBitmap($rdata, $offset);

        $this->setSoaSerial((int) $integers['serial']);
        $this->setFlags((int) $integers['flags']);
        array_map([$this, 'addType'], $types);
    }
}
