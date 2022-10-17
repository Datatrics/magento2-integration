<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Pixel;

/**
 * Class TemplatePreparator
 */
class TemplatePreparator
{

    /**
     * @param string $html
     * @param array $variables
     * @return string
     */
    public function execute($html = '', $variables = [])
    {
        $variablesSeparated = [
            'recursive' => [],
            'simple' => []
        ];
        foreach ($variables as $key => $value) {
            $variablesSeparated[
                is_array($value) ? 'recursive' : 'simple'
            ][$key] = $value;
        }
        $html = $this->applySimpleParameters($html, $variablesSeparated['simple']);
        $html = $this->applyRecursiveParameters($html, $variablesSeparated['recursive']);
        return $html;
    }

    /**
     * @param string $html
     * @param array $simple
     * @return string
     */
    private function applySimpleParameters(string $html, array $simple)
    {
        foreach ($simple as $key => $value) {
            $html = str_replace(
                sprintf('{{%s}}', $key),
                (string)$value,
                $html
            );
        }
        return $html;
    }

    /**
     * @param string $html
     * @param array $recursive
     * @return string
     */
    private function applyRecursiveParameters(string $html, array $recursive)
    {
        $count = 1;
        foreach ($recursive as $key => $values) {
            $positionStart = strpos(
                $html,
                sprintf('{{%s start}}', $key)
            );
            $positionEnd = strpos(
                $html,
                sprintf('{{%s end}}', $key)
            ) - $positionStart + strlen(sprintf('{{%s end}}', $key));
            $toReplace = substr($html, $positionStart, $positionEnd);
            $replace = '';
            foreach ($values as $subValue) {
                $replace .= $this->applySimpleParameters($toReplace, $subValue);
            }
            $replace = str_replace(sprintf('{{%s start}}', $key), '', $replace);
            $replace = str_replace(sprintf('{{%s end}}', $key), '', $replace);
            $html = str_replace($toReplace, $replace, $html, $count);
        }
        return $html;
    }
}
