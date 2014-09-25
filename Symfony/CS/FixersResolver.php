<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 */
class FixersResolver
{
    public function __construct(array $allFixers)
    {
        $this->allFixers = $allFixers;
    }

    public function resolveByLevel($levelOption)
    {
        $fixers = array();

        if (is_string($levelOption)) {
            switch ($levelOption) {
                case 'psr0':
                    $level = FixerInterface::PSR0_LEVEL;
                    break;
                case 'psr1':
                    $level = FixerInterface::PSR1_LEVEL;
                    break;
                case 'psr2':
                    $level = FixerInterface::PSR2_LEVEL;
                    break;
                case 'symfony':
                    $level = FixerInterface::SYMFONY_LEVEL;
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('The level "%s" is not defined.', $levelOption));
            }
        } else {
            $level = $levelOption;
        }

        // select base fixers for the given level
        if (is_array($level)) {
            foreach ($this->allFixers as $fixer) {
                if (in_array($fixer->getName(), $level, true) || in_array($fixer, $level, true)) {
                    $fixers[] = $fixer;
                }
            }
        } else {
            foreach ($this->allFixers as $fixer) {
                if ($fixer->getLevel() === ($fixer->getLevel() & $level)) {
                    $fixers[] = $fixer;
                }
            }
        }

        return $fixers;
    }

    public function resolveByNames(array $fixers, $names)
    {
        if (!is_array($names)) {
            $names = array_map('trim', explode(',', $names));
        }

        $addNames = array();
        $removeNames = array();
        foreach ($names as $name) {
            if (0 === strpos($name, '-')) {
                $removeNames[] = ltrim($name, '-');
            } else {
                $addNames[] = $name;
            }
        }

        foreach ($fixers as $key => $fixer) {
            if (in_array($fixer->getName(), $removeNames, true)) {
                unset($fixers[$key]);
            }
        }

        foreach ($this->allFixers as $fixer) {
            if (in_array($fixer->getName(), $addNames, true) && !in_array($fixer, $fixers, true)) {
                $fixers[] = $fixer;
            }
        }

        return $fixers;
    }
}
