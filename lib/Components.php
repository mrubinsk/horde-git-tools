<?php
/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Components
 */

namespace Horde\GitTools;

use Horde_Argv_Parser;
use Horde_Cli_Modular;
/**
 * The Components:: class is the entry point for the various component actions
 * provided by the package.
 *
 * @author    Gunnar Wrobel <wrobel@pardus.de>
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Components
 * @category  Horde
 */
class Components extends \Components
{

    /**
     * The main entry point for the application.
     *
     * @param array $parameters A list of named configuration parameters.
     * <pre>
     * 'cli'        - (array)  CLI configuration parameters.
     *   'parser'   - (array)  Parser configuration parameters.
     *     'class'  - (string) The class name of the parser to use.
     * </pre>
     */
    public static function main(array $parameters = array())
    {
        $dependencies = new Components\Dependencies\Injector();
        $modular = self::_prepareModular($dependencies, $parameters);
        $parser = $modular->createParser();
        $dependencies->setParser($parser);
        $config = self::_prepareConfig($parser);
        $dependencies->initConfig($config);

        // Shift off the initial 'components' argument so we can pass this
        // directly to Components.
        $config->getArguments();
        $config->shiftArgument();

        try {
            self::_identifyComponent(
                $config, self::_getActionArguments($modular), $dependencies
            );
        } catch (\Components_Exception $e) {
            $parser->parserError($e->getMessage());
            return;
        }

        try {
            $ran = false;
            foreach (clone $modular->getModules() as $module) {
                $ran |= $modular->getProvider()->getModule($module)->handle($config);
            }
        } catch (\Components_Exception $e) {
            $dependencies->getOutput()->fail($e);
            return;
        }

        if (!$ran) {
            $parser->parserError(self::ERROR_NO_ACTION);
        }
    }

}
