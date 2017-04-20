<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools;

use Horde_Cli_Modular;
use Horde_Argv_Parser;

/**
 * Main class
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Cli
{
    const ERROR_NO_ACTION = 'You did not specify an action!';

    /**
     * Entry point
     */
    public static function main()
    {
        // Setup the modular cli.
        $dependencies = new \Components_Dependencies_Injector();
        $modular = self::_prepareModular($dependencies);
        $parser = $modular->createParser();
        $dependencies->setParser($parser);
        $config = self::_prepareConfig($parser);
        $dependencies->initConfig($config);

        // Run the action.
        try {
            $ran = false;
            foreach (clone $modular->getModules() as $module) {
                $ran |= $modular->getProvider()->getModule($module)->handle($config);
            }
        } catch (\Components_Exception $e) {
            $dependencies->getOutput()->fail($e);
            return;
        }

        // Something didn't work as expected.
        if (!$ran) {
            $parser->parserError(self::ERROR_NO_ACTION);
        }
    }

    /**
     * Prepare the Configuration object
     *
     * @param  Horde_Argv_Parser $parser  The parser.
     *
     * @return \Component_Configs  The configuration helper.
     */
    protected static function _prepareConfig(Horde_Argv_Parser $parser)
    {
        $config = new \Components_Configs();
        $config->addConfigurationType(
            new Config\Cli(
                $parser
            )
        );
        $config->unshiftConfigurationType(
            new \Components_Config_File(
                $config->getOption('config')
            )
        );
        return $config;
    }

    /**
     * Prepare the modular CLI instance.
     *
     * @param  \Components_Dependencies $dependencies  The dependency container.
     *
     * @return \Horde_Cli_Modular  The modular CLI object.
     */
    protected static function _prepareModular($dependencies)
    {
        // The modular CLI helper.
        $modular = new Horde_Cli_Modular(array(
            'parser' => array('usage' => '[OPTIONS] COMMAND [ARGUMENTS]

COMMAND

Selects the command to perform. This is a list of possible commands '
            ),
            'modules' => array(
                'directory' => __DIR__ . '/Module',
                'exclude' => 'Base'
            ),
            'provider' => array(
                'prefix' => '\Horde\GitTools\Module\\',
                'dependencies' => $dependencies
            ),
            'cli' => $dependencies->getInstance('Horde_Cli'),
        ));
        $dependencies->setModules($modular);

        return $modular;
    }

}
