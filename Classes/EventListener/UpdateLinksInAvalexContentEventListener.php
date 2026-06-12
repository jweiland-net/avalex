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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Http\NormalizedParams;

/**
 * The retrieved HTML content from avalex API endpoint may contain visible email addresses in <a>-tags.
 * This listener will search for these tags and encrypt the contained email addresses like configured in TYPO3
 * @see config.spamProtectEmailAddresses
 */
#[AsEventListener(
    identifier: 'avalexUpdateLinksInAvalexContent',
)]
final readonly class UpdateLinksInAvalexContentEventListener
{
    public function __invoke(PostProcessApiResponseContentEvent $event): void
    {
        $normalizedParams = $this->getNormalizedParams($event->getServerRequest());

        $requestUrl = $normalizedParams->getRequestUrl();
        $content = $event->getContent();
        $contentObjectRenderer = $event->getContentObjectRenderer();

        if ($content === '') {
            return;
        }

        $content = preg_replace_callback(
            '@<a href="(?P<href>(?P<type>mailto:|#)[^"\']+)">(?P<text>[^<]+)</a>@',
            static function (array $match) use ($requestUrl, $contentObjectRenderer) {
                if ($match['type'] === 'mailto:') {
                    return $contentObjectRenderer->typoLink($match['text'], [
                        'parameter' => $match['href'],
                    ]);
                }

                return str_replace($match['href'], $requestUrl . $match['href'], $match[0]);
            },
            $content,
        );

        $event->setContent($content);
    }

    private function getNormalizedParams(ServerRequestInterface $request): NormalizedParams
    {
        return $request->getAttribute('normalizedParams');
    }
}
