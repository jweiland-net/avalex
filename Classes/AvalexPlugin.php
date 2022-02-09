<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Service\LanguageService;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use JWeiland\Avalex\Service\ApiService;

/**
 * Class AvalexPlugin
 */
class AvalexPlugin
{
    /**
     * @var VariableFrontend
     */
    protected $cache;

    /**
     * @var ContentObjectRenderer
     */
    public $cObj;

    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('avalex_content');
    }

    /**
     * @param string $endpoint
     * @return string
     */
    protected function checkEndpoint($endpoint)
    {
        $endpoint = (string)$endpoint;
        if (in_array($endpoint, array('avx-datenschutzerklaerung', 'avx-impressum', 'avx-bedingungen', 'avx-widerruf'), true)) {
            return $endpoint;
        }
        throw new \InvalidArgumentException(sprintf('The endpoint "%s" is invalid!', $endpoint), 1582029646660);
    }

    /**
     * Render plugin
     *
     * @param string $_ empty string
     * @param array $conf TypoScript configuration
     * @return string
     */
    public function render($_, $conf)
    {
        $endpoint = $this->checkEndpoint($conf['endpoint']);
        $rootPage = AvalexUtility::getRootForPage();
        $cacheIdentifier = sprintf(
            'avalex_%s_%d_%d_%s',
            $endpoint,
            $rootPage,
            $GLOBALS['TSFE']->id,
            AvalexUtility::getFrontendLocale()
        );
        if ($this->cache->has($cacheIdentifier)) {
            $content = (string)$this->cache->get($cacheIdentifier);
        } else {
            $avalexConfigurationRepository = GeneralUtility::makeInstance(AvalexConfigurationRepository::class);
            $configuration = $avalexConfigurationRepository->findByWebsiteRoot($rootPage, 'uid, api_key, domain');
            $language = GeneralUtility::makeInstance(LanguageService::class, $configuration)->getLanguageForEndpoint($endpoint);
            $apiService = GeneralUtility::makeInstance(ApiService::class);
            $content = $apiService->getHtmlForCurrentRootPage($endpoint, $language, $configuration);
            $curlInfo = $apiService->getCurlService()->getCurlInfo();
            if ($curlInfo['http_code'] === 200) {
                // set cache for successful calls only
                $extensionConfiguration = AvalexUtility::getExtensionConfiguration();
                $content = $this->processLinks($content);
                $this->cache->set(
                    $cacheIdentifier,
                    $content,
                    [],
                    $extensionConfiguration['cacheLifetime'] ?: 3600
                );
            }
        }
        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    protected function processLinks($content)
    {
        $cObj = $this->cObj;
        $requestUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        return preg_replace_callback(
            '@<a href="(?P<href>(?P<type>mailto:|#)[^"\']+)">(?P<text>[^<]+)<\/a>@',
            function ($match) use ($cObj, $requestUrl) {
                if ($match['type'] === 'mailto:') {
                    $encrypted = $cObj->getMailTo(substr($match['href'], 7), $match['text']);
                    return '<a href="' . $encrypted[0] . '">' . $encrypted[1] . '</a>';
                }
                return (string)str_replace($match['href'], $requestUrl . $match['href'], $match[0]);
            },
            $content
        );
    }
}
