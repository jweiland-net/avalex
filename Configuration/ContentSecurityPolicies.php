<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\HashValue;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Type\Map;

return call_user_func(static function (): Map {
    $hashScriptTagBody = new HashValue('AKZnKXnqtOlXD47qmsZludKD3AgJk/jI1TZo38yYEsQ=');
    $hashButtonEvent = new HashValue('+nZ4HfY28YjDrh2jtJEmjM5e2bqcGJhqd7el43OvUzQ=');

    return Map::fromEntries(
        [
            Scope::frontend(),
            new MutationCollection(
                new Mutation(
                    MutationMode::Extend,
                    Directive::ScriptSrc,
                    $hashScriptTagBody,
                    $hashButtonEvent,
                    SourceKeyword::unsafeHashes
                ),
            ),
        ]
    );
});
