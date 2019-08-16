<?php
namespace Neos\RedirectHandler;

/*
 * This file is part of the Neos.RedirectHandler package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DateTime;
use DateTimeInterface;

/**
 * A Redirect DTO
 */
class Redirect implements RedirectInterface
{
    /**
     * Relative URI path for which this redirect should be triggered
     *
     * @var string
     */
    protected $sourceUriPath;

    /**
     * Target URI path to which a redirect should be pointed
     *
     * @var string
     */
    protected $targetUriPath;

    /**
     * Status code to be send with the redirect header
     *
     * @var integer
     */
    protected $statusCode;

    /**
     * Full qualified host name
     *
     * @var string
     */
    protected $host;

    /**
     * The human readable name of the creator of the redirect
     *
     * @var string
     */
    protected $creator;

    /**
     * A textual comment describing the redirect
     *
     * @var string
     */
    protected $comment;

    /**
     * The type of the redirect to be able to differentiate between system generated and manual redirects.
     *
     * @var string
     */
    protected $type;

    /**
     * The date and time the redirect should start being active
     *
     * @var DateTime
     */
    protected $startDateTime;

    /**
     * The date and time the redirect should stop being active
     *
     * @var DateTime
     */
    protected $endDateTime;

    /**
     * The number of times the redirect was executed
     *
     * @var int
     */
    protected $hitCounter;

    /**
     * The last date and time the redirect was executed
     *
     * @var DateTime
     */
    protected $lastHit;

    /**
     * The date and time the redirect was created
     *
     * @var DateTime
     */
    protected $creationDateTime;

    /**
     * The date and time the redirect was modified
     *
     * @var DateTime
     */
    protected $lastModificationDateTime;

    /**
     * @param string $sourceUriPath relative URI path for which a redirect should be triggered
     * @param string $targetUriPath target URI path to which a redirect should be pointed
     * @param integer $statusCode status code to be send with the redirect header
     * @param string $host Full qualified host name to match the redirect
     * @param null $creator name of the person who created the redirect
     * @param null $comment textual description of the redirect
     * @param null $type
     * @param DateTimeInterface|null $startDateTime
     * @param DateTimeInterface|null $endDateTime
     * @param DateTimeInterface|null $creationDateTime
     * @param DateTimeInterface|null $lastModificationDateTime
     * @param int $hitCounter
     * @param DateTimeInterface|null $lastHit
     */
    public function __construct(
        $sourceUriPath,
        $targetUriPath,
        $statusCode,
        $host = null,
        $creator = null,
        $comment = null,
        $type = null,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        DateTimeInterface $creationDateTime = null,
        DateTimeInterface $lastModificationDateTime = null,
        $hitCounter = 0,
        DateTimeInterface $lastHit = null
    ) {
        $this->sourceUriPath = ltrim($sourceUriPath, '/');
        $this->targetUriPath = ltrim($targetUriPath, '/');
        $this->statusCode = (integer)$statusCode;
        $this->host = trim($host);
        $this->creator = $creator;
        $this->comment = $comment;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->creationDateTime = $creationDateTime;
        $this->lastModificationDateTime = $lastModificationDateTime;
        $this->hitCounter = $hitCounter;
        $this->lastHit = $lastHit;

        $this->type = in_array($type,
            [self::REDIRECT_TYPE_GENERATED, self::REDIRECT_TYPE_MANUAL]) ? $type : self::REDIRECT_TYPE_GENERATED;
    }

    /**
     * @param RedirectInterface $redirect
     * @return Redirect
     */
    public static function create(RedirectInterface $redirect): RedirectInterface
    {
        return new self($redirect->getSourceUriPath(), $redirect->getTargetUriPath(), $redirect->getStatusCode(),
            $redirect->getHost(), $redirect->getCreator(), $redirect->getComment(), $redirect->getType(),
            $redirect->getStartDateTime(), $redirect->getEndDateTime(), $redirect->getCreationDateTime(),
            $redirect->getLastModificationDateTime(), $redirect->getHitCounter(), $redirect->getLastHit());
    }

    /**
     * @return string
     */
    public function getSourceUriPath(): string
    {
        return $this->sourceUriPath;
    }

    /**
     * @return string
     */
    public function getTargetUriPath(): string
    {
        return $this->targetUriPath;
    }

    /**
     * @return integer
     */
    public function getStatusCode(): int
    {
        return (integer)$this->statusCode;
    }

    /**
     * @return string
     */
    public function getHost(): ?string
    {
        return trim($this->host) === '' ? null : $this->host;
    }

    /**
     * @return string
     */
    public function getCreator(): ?string
    {
        return $this->creator;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getStartDateTime(): ?DateTimeInterface
    {
        return $this->startDateTime;
    }

    /**
     * @return DateTime
     */
    public function getEndDateTime(): ?DateTimeInterface
    {
        return $this->endDateTime;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getCreationDateTime(): ?DateTimeInterface
    {
        return $this->creationDateTime;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getLastModificationDateTime(): ?DateTimeInterface
    {
        return $this->lastModificationDateTime;
    }

    /**
     * @return integer
     */
    public function getHitCounter(): int
    {
        return $this->hitCounter;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getLastHit(): ?DateTimeInterface
    {
        return $this->lastHit;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'host' => $this->getHost(),
            'sourceUriPath' => $this->getSourceUriPath(),
            'targetUriPath' => $this->getTargetUriPath(),
            'statusCode' => $this->getStatusCode(),
            'startDateTime' => $this->getStartDateTime() ? $this->getStartDateTime()->format('Y-m-d H:i:s') : '',
            'endDateTime' => $this->getEndDateTime() ? $this->getEndDateTime()->format('Y-m-d H:i:s') : '',
            'comment' => $this->getComment(),
            'creator' => $this->getCreator(),
            'type' => $this->getType(),
            'hitCounter' => $this->getHitCounter(),
            'lastHit' => $this->getLastHit() ? $this->getLastHit()->format('Y-m-d H:i:s') : '',
            'creationDateTime' => $this->getCreationDateTime() ? $this->getCreationDateTime()->format('Y-m-d H:i:s') : '',
            'lastModificationDateTime' => $this->getLastModificationDateTime() ? $this->getLastModificationDateTime()->format('Y-m-d H:i:s') : '',
        ];
    }
}
