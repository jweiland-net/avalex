<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Traits;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Trait with helpful method to retrieve various attributes from TYPO3 request object.
 */
trait SiteTrait
{
    private function getContentObjectRendererFromRequest(ServerRequestInterface $request): ?ContentObjectRenderer
    {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');

        return $contentObjectRenderer instanceof ContentObjectRenderer ? $contentObjectRenderer : null;
    }

    private function detectRootPageUid(ServerRequestInterface $request): int
    {
        $site = $request->getAttribute('site');

        return $site instanceof Site ? $site->getRootPageId() : 0;
    }

    private function detectCurrentPageUid(ServerRequestInterface $request): int
    {
        $pageArguments = $request->getAttribute('routing');

        return $pageArguments instanceof PageArguments ? $pageArguments->getPageId() : 0;
    }
}
