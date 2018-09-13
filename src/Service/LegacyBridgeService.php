<?php
namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Class LegacyBridgeService
 *
 * A dedicated service to expose internal services to legacy code.
 *
 * @package App
 */
class LegacyBridgeService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExHentaiBrowserService
     */
    private $browser;

    public function __construct(
        LoggerInterface $logger,
        ExHentaiBrowserService $browserService
    ) {
        $this->logger  = $logger;
        $this->browser = $browserService;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return ExHentaiBrowserService
     */
    public function getBrowser(): ExHentaiBrowserService
    {
        return $this->browser;
    }
}
