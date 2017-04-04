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

use Horde_Cli;
use Horde_Cli_Modular;
use Horde_Cli_Modular_Exception;
Use Horde_Argv_Option;

/**
 * Main class
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Cli
{
    /**
     * The Horde_Cli instance.
     *
     * @var Hord_Cli
     */
    public static $cli;

    /**
     * Entry point
     */
    public static function main()
    {
        // Init the CLI, ensure we aren't running throught the web.
        // Make sure no one runs this from the web.
        if (!Horde_Cli::runningFromCLI()) {
            exit;
        }
        self::$cli = Horde_Cli::init();

        // Dependency container.
        $dependencies = new Dependencies();

        // The modular CLI helper.
        $modular = new Horde_Cli_Modular(array(
            'parser' => array('usage' => '[OPTIONS] COMMAND [ARGUMENTS]

COMMAND

Selects the command to perform. This is a list of possible commands '),
            'modules' => array(
                'directory' => __DIR__ . '/Module',
                'exclude' => 'Base'
                ),
            'provider' => array(
                'prefix' => '\Horde\GitTools\Module\\',
                'dependencies' => $dependencies)
        ));

        // Generate the Horde_Argv_Parser.
        $parser = $modular->createParser();
        $parser->addOptions(
            array(
                new Horde_Argv_Option(
                    '-c',
                    '--config',
                    array(
                        'action' => 'store',
                        'help'   => 'Path to configuration file.',
                    )
                ),
                new Horde_Argv_Option(
                    '-a',
                    '--apps',
                    array(
                        'action' => 'store',
                        'help'   => 'Comma delimted list of applications to link.',
                    )
                ),
                new Horde_Argv_Option(
                    '',
                    '--git_base',
                    array(
                        'action' => 'store',
                        'help'   => 'Path the base directory containing git checkouts.',
                    )
                ),
                new Horde_Argv_Option(
                    '-d',
                    '--debug',
                    array(
                        'action' => 'store_true',
                        'help'   => 'Enable debug output.',
                    )
                )
            )
        );

        // Set dependencies.
        $dependencies->setParser($parser);
        $dependencies->setCli(self::$cli);
        $dependencies->setModular($modular);

        // Parse options and args.
        list($options, $arguments) = $parser->parseArgs();

        // Load config file and combine/replace with options provided on
        // command line.
        $params = array();
        if (empty($options['config'])) {
             require dirname(__FILE__) . '/../bin/conf.php';
        } else {
            require $options['config'];
        }
        // $options is not a true array so we can't array_merge.
        foreach ($options as $key => $value) {
            if (!empty($value)) {
                $params[$key] = $value;
            }
        }

        // Required parameters.
        if (empty($params['git_base'])) {
            $parser->printHelp();
            exit;
        }

        // Parse the COMMAND we want and hand off...
        $module = array_shift($arguments);

        // We always have an ACTION after COMMAND, except for 'help',
        // which prints, well, help anyway....
        if (empty($arguments)) {
            $parser->printHelp();
            return;
        }

        try {
            $module = $modular->getProvider()->getModule($module);
        } catch (\Horde_Cli_Modular_Exception $e) {
            self::$cli->message($e->getMessage(), 'cli.error');
            self::$cli->writeln();
            $parser->printHelp();
            return;
        }
        $module->handle($arguments, $params);
    }

}
