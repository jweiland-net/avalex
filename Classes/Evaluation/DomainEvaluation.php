<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Evaluation;

/**
 * Add https:// before string if it does not contain "http".
 */
class DomainEvaluation
{
    /**
     * JavaScript code for client side validation/evaluation
     */
    public function returnFieldJS(): string
    {
        return 'if (!value.includes(\'http\')) {
          value = \'https://\' + value;
        }
        return value;';
    }

    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $isIn The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not.
     * @return string Evaluated field value
     */
    public function evaluateFieldValue(string $value , string $isIn, bool &$set): string
    {
        if (!str_contains($value, 'http')) {
            $value = 'https://' . $value;
        }

        return $value;
    }
}
