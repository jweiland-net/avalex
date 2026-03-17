<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Upgrade;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * With TYPO3 13 all plugins have to be declared as content elements (CType) insteadof "list_type"
 */
#[UpgradeWizard('avalex_migratePluginsToContentElementsUpdate')]
class MigratePluginToContentElementUpgrade extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'avalex_avalex' => 'avalex_avalex',
            'avalex_imprint' => 'avalex_imprint',
            'avalex_bedingungen' => 'avalex_bedingungen',
            'avalex_widerruf' => 'avalex_widerruf',
        ];
    }

    public function getTitle(): string
    {
        return '[avalex] Migrate plugins to Content Elements';
    }

    public function getDescription(): string
    {
        return 'The modern way to register plugins for TYPO3 is to register them as content element types. '
            . 'Running this wizard will migrate all events2 plugins to content element (CType)';
    }
}
