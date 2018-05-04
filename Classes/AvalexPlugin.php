<?php
namespace JWeiland\Avalex;

/*
 * This file is part of the avalex project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Avalex\Domain\Repository\LegalTextRepository;
use JWeiland\Avalex\Exception\InvalidUidException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class AvalexPlugin
 */
class AvalexPlugin
{
    /**
     * @var ContentObjectRenderer
     */
    public $cObj;

    /**
     * Render plugin
     *
     * @return string
     */
    public function render()
    {
        $legalText = $this->getLegalText($this->getRootForCurrentPage());
        if ($legalText) {
            $content = $legalText['content'];
        } else {
            $content = LocalizationUtility::translate('errors.missing_data', 'avalex');
        }
        return $content;
    }

    /**
     * Returns the uid of the site root of current page
     *
     * @return int
     * @throws InvalidUidException
     */
    protected function getRootForCurrentPage()
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $currentPageUid = $this->getTypoScriptFrontendController()->id;
        $rootLine = $pageRepository->getRootLine($currentPageUid);
        $rootPageUid = array_pop($rootLine);
        $rootPageUid = $rootPageUid['uid'];
        if (!MathUtility::canBeInterpretedAsInteger($rootPageUid)) {
            throw new InvalidUidException('Could not determine root page uid of current page id!', 1525270267);
        }
        return (int)$rootPageUid;
    }

    /**
     * Get legal text by rootPageUid (website_root)
     *
     * @param $rootPageUid
     * @return array|false|null
     */
    protected function getLegalText($rootPageUid)
    {
        /** @var LegalTextRepository $legalTextRepository */
        $legalTextRepository = GeneralUtility::makeInstance(
            'JWeiland\\Avalex\\Domain\\Repository\\LegalTextRepository',
            $this->cObj
        );
        return $legalTextRepository->findByWebsiteRoot($rootPageUid);
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
