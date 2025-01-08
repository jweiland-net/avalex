<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Service\LanguageService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Factory to create a new request object based on a given endpoint configuration
 */
class RequestFactory
{
    /**
     * @var RequestInterface[]
     */
    private iterable $registeredAvalexRequests;

    public function __construct(
        private readonly AvalexConfigurationRepository $avalexConfigurationRepository,
        private LanguageService $languageService,
        private readonly LoggerInterface $logger,
        iterable $registeredAvalexRequests,
    ) {
        $this->registeredAvalexRequests = $registeredAvalexRequests;
    }

    public function create(string $endpoint, ServerRequestInterface $request): ?RequestInterface
    {
        $avalexConfiguration = $this->getAvalexConfiguration($request);

        // Early return, if no avalex configuration could be found.
        if (!$avalexConfiguration instanceof AvalexConfiguration) {
            return null;
        }

        $endpointRequest = $this->getRequestForEndpoint($endpoint, $avalexConfiguration);

        if ($endpointRequest instanceof LocalizeableRequestInterface) {
            $this->languageService->addLanguageToEndpoint($endpointRequest, $avalexConfiguration, $request);
        }

        return $endpointRequest;
    }

    private function getAvalexConfiguration(ServerRequestInterface $request): ?AvalexConfiguration
    {
        return $this->avalexConfigurationRepository->findByRootPageUid(
            $this->detectRootPageUid($request),
        );
    }

    private function detectRootPageUid(ServerRequestInterface $request): int
    {
        $site = $request->getAttribute('site');

        return $site instanceof Site ? $site->getRootPageId() : 0;
    }

    private function getRequestForEndpoint(
        string $endpoint,
        AvalexConfiguration $avalexConfiguration,
    ): ?RequestInterface {
        foreach ($this->registeredAvalexRequests as $avalexRequest) {
            if ($avalexRequest->getEndpoint() === $endpoint) {
                $avalexRequest->setAvalexConfiguration($avalexConfiguration);
                return $avalexRequest;
            }
        }

        $this->logger->error('There is no registered avalex request with specified endpoint: ' . $endpoint);

        return null;
    }
}
