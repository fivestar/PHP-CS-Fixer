<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Console;

use Symfony\CS\ConfigInterface;
use Symfony\CS\Fixer;
use Symfony\CS\FixerInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 */
class FixersResolver
{
    public function __construct(Fixer $fixer, ConfigInterface $config)
    {
        $this->fixer = $fixer;
        $this->config = $config;
    }

    public function resolve($levelOption, $fixersOption)
    {
        $allFixers = $this->fixer->getFixers();

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
            case null:
                if (empty($fixerOption) || preg_match('{(^|,)-}', $fixerOption)) {
                    $level = $this->config->getFixers();
                } else {
                    $level = null;
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('The level "%s" is not defined.', $levelOption));
        }

        // select base fixers for the given level
        $fixers = array();
        if (is_array($level)) {
            foreach ($allFixers as $fixer) {
                if (in_array($fixer->getName(), $level, true) || in_array($fixer, $level, true)) {
                    $fixers[] = $fixer;
                }
            }
        } else {
            foreach ($allFixers as $fixer) {
                if ($fixer->getLevel() === ($fixer->getLevel() & $level)) {
                    $fixers[] = $fixer;
                }
            }
        }

        // remove/add fixers based on the fixers option
        $names = array_map('trim', explode(',', $fixersOption));
        $addNames = [];
        $removeNames = [];
        foreach ($names as $name) {
            if (strpos($name, '-') === 0) {
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

        foreach ($allFixers as $fixer) {
            if (in_array($fixer->getName(), $names, true) && !in_array($fixer, $fixers, true)) {
                $fixers[] = $fixer;
            }
        }

        return $fixers;
    }
}
