<?php

declare(strict_types=1);

namespace JWeiland\Avalex\Update;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('jweilandAvalexCTypeMigration')]
final class JWeilandAvalexCTypeMigration extends AbstractListTypeToCTypeUpdate
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
            . 'Running this wizard will migrate all avalex plugins to content element (CType)';
    }
}
