<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Backend\Preview;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Domain\Repository\Exception\DatabaseQueryException;
use JWeiland\Avalex\Domain\Repository\Exception\NoAvalexConfigurationException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\SiteFinder;

final class ContentPreviewRenderer extends StandardContentPreviewRenderer
{
    public function __construct(
        private readonly AvalexConfigurationRepository $avalexConfigurationRepository,
        private readonly UriBuilder $uriBuilder,
        private readonly SiteFinder $siteFinder,
    ) {
        parent::__construct();
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $itemContent = parent::renderPageModulePreviewContent($item);
        $row = $item->getRecord();
        $rootPage = $this->detectRootPageUid($row->getPid());
        $request = $item->getContext()->getCurrentRequest();
        $normalizedParams = $this->getNormalizedParams($request);

        $itemContent .= sprintf(
            '<p><b>Avalex: %s</b></p>',
            $this->getTranslation(sprintf('tx_%s.name', $row->getRecordType())),
        );

        try {
            $avalexConfiguration = $this->avalexConfigurationRepository->findByRootPageUid($rootPage, $request);

            $itemContent .= sprintf(
                '<p>%s</p>',
                sprintf(
                    $this->getTranslation('preview_renderer.found_config'),
                    $avalexConfiguration->getDescription(),
                ),
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-default">%s</a>',
                $this->getLinkToEditConfigurationRecord($avalexConfiguration->getUid(), $normalizedParams),
                $this->getTranslation('preview_renderer.button.edit'),
            );
        } catch (NoAvalexConfigurationException) {
            $itemContent .= sprintf(
                '<p>%s</p>',
                $this->getTranslation('preview_renderer.no_config'),
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-default">%s</a>',
                $this->getLinkToCreateConfigurationRecord($normalizedParams),
                $this->getTranslation('preview_renderer.button.add'),
            );
        } catch (DatabaseQueryException $databaseQueryException) {
            $itemContent .= sprintf(
                '<p>%s</p>',
                $databaseQueryException->getMessage(),
            );
        }

        return $itemContent;
    }

    private function getLinkToEditConfigurationRecord(int $uid, NormalizedParams $normalizedParams): string
    {
        $params = [
            'edit' => ['tx_avalex_configuration' => [$uid => 'edit']],
            'returnUrl' => $normalizedParams->getRequestUri(),
        ];

        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    private function getLinkToCreateConfigurationRecord(NormalizedParams $normalizedParams): string
    {
        $params = [
            'edit' => ['tx_avalex_configuration' => [0 => 'new']],
            'returnUrl' => $normalizedParams->getRequestUri(),
        ];

        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    private function detectRootPageUid(int $pageUid): int
    {
        try {
            return $this->siteFinder->getSiteByPageId($pageUid)->getRootPageId();
        } catch (SiteNotFoundException) {
        }

        return 0;
    }

    private function getTranslation(string $key): string
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:' . $key);
    }

    private function getNormalizedParams(ServerRequestInterface $request): NormalizedParams
    {
        return $request->getAttribute('normalizedParams');
    }
}
