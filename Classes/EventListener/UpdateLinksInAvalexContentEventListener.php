<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\EventListener;

use JWeiland\Avalex\Event\PostProcessApiResponseContentEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The retrieved HTML content from avalex API endpoint may contain visible email addresses in <a>-tags.
 * This listener will search for these tags and encrypts the contained email addresses like configured in TYPO3
 * @see config.spamProtectEmailAddresses
 */
#[AsEventListener('avalexUpdateLinksInAvalexContent')]
readonly class UpdateLinksInAvalexContentEventListener
{
    public function __invoke(PostProcessApiResponseContentEvent $postProcessApiResponseContentEvent): void
    {
        $requestUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        $content = $postProcessApiResponseContentEvent->getContent();
        $contentObjectRenderer = $postProcessApiResponseContentEvent->getContentObjectRenderer();

        if ($content === '') {
            return;
        }

        $content = preg_replace_callback(
            '@<a href="(?P<href>(?P<type>mailto:|#)[^"\']+)">(?P<text>[^<]+)</a>@',
            static function ($match) use ($requestUrl, $contentObjectRenderer) {
                if ($match['type'] === 'mailto:') {
                    return $contentObjectRenderer->typoLink($match['text'], [
                        'parameter' => $match['href'],
                    ]);
                }

                return (string)str_replace($match['href'], $requestUrl . $match['href'], $match[0]);
            },
            $content,
        );

        $postProcessApiResponseContentEvent->setContent($content);
    }
}
