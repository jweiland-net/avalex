<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex;

use JWeiland\Avalex\Client\Request\Exception\InvalidAvalexEndpointException;
use JWeiland\Avalex\Client\Request\RequestFactory;
use JWeiland\Avalex\Domain\Repository\Exception\DatabaseQueryException;
use JWeiland\Avalex\Domain\Repository\Exception\NoAvalexConfigurationException;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Traits\SiteTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This is the main class that will be called via TypoScript.
 */
readonly class AvalexPlugin
{
    use SiteTrait;

    public function __construct(
        private ApiService $apiService,
        private RequestFactory $requestFactory,
    ) {}

    /**
     * Main method. This will be called by TypoScript "userFunc"
     */
    public function render(string $content, array $conf, ServerRequestInterface $request): string
    {
        try {
            $endpointRequest = $this->requestFactory->create($conf['endpoint'], $request);
        } catch (NoAvalexConfigurationException) {
            return LocalizationUtility::translate('error.noAvalexConfiguration', 'avalex');
        } catch (DatabaseQueryException $databaseQueryException) {
            return LocalizationUtility::translate('error.dbError', 'avalex') . $databaseQueryException->getMessage();
        } catch (InvalidAvalexEndpointException) {
            return LocalizationUtility::translate('error.invalidAvalexRequest', 'avalex');
        }

        return $this->apiService->getHtmlContentFromEndpoint($endpointRequest, $request);
    }
}
