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

namespace Horde\GitTools\Module;

use Horde_String;
use Horde_Argv_IndentedHelpFormatter;

/**
 * Class for handling help.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Help extends Base
{
    public function handle(\Components_Configs $config)
    {
        // Arguments will be an array:
        // 0 => help 1 => COMMAND [2 => ACTION]
        $arguments = $config->getArguments();
        if (!isset($arguments[0]) ||
            (isset($arguments[0]) && $arguments[0] == 'help')) {
            if (isset($arguments[1])) {
                $command = $arguments[1];
            } else {
                $this->_dependencies->getParser()->printHelp();
                return true;
            }
            if (isset($arguments[2])) {
                $action = $arguments[2];
            } else {
                $action = '';
            }

            $modules = $this->_dependencies->getModules();
            $element= $modules->getProvider()->getModule($command);

            // Generate main help text.
            $title = "COMMAND \"" . $command . "\"";
            $sub = str_repeat('-', strlen($title));
            $help = "\n" . $title . "\n" . $sub . "\n\n";
            $help .= Horde_String::wordwrap(
                $element->getHelp($action), 75, "\n", true
            );
            $options = $element->getContextOptionHelp($action);
            if (!empty($options)) {
                $formatter = new Horde_Argv_IndentedHelpFormatter();
                $parser = $this->_dependencies->getParser();
                $title = "OPTIONS for \"" . $action . "\"";
                $sub = str_repeat('-', strlen($title));
                $help .= "\n\n\n" . $title . "\n" . $sub . "";
                foreach ($options as $option => $help_text) {
                    $argv_option = $parser->getOption($option);
                    $help .= "\n\n    " . $formatter->formatOptionStrings($argv_option) . "\n\n      ";
                    if (empty($help_text)) {
                        $help .= Horde_String::wordwrap(
                            $argv_option->help, 75, "\n      ", true
                        );
                    } else {
                        $help .= Horde_String::wordwrap(
                            $help_text, 75, "\n      ", true
                        );
                    }
                }
            }
            $help .= "\n";
            $this->_dependencies->getOutput()->help(
                $help
            );
        }

        return true;
    }

    /**
     * Returns additional usage title for this module.
     *
     * @return string  The usage title.
     */
    public function getTitle()
    {
        return 'help COMMAND';
    }

    /**
     * Returns additional usage description for this module.
     *
     * @return string The description.
     */
    public function getUsage()
    {
        return 'Provide information about the specified COMMAND.';
    }

    /**
     * Return the action arguments supported by this module.
     *
     * @return array A list of supported action arguments.
     */
    public function getActions()
    {
        return array('help');
    }

}
