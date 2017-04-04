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
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Help extends Base
{
    public function handle($arguments, $params)
    {
        $command = $arguments[0];
        if (isset($arguments[1])) {
            $action = $arguments[1];
        } else {
            $action = '';
        }
        $modular = $this->_dependencies->getModular();
        $element = $modular->getProvider()->getModule($command);

        // Generate main help text.
        $title = "COMMAND \"" . $command . "\"";
        $sub = str_repeat('-', strlen($title));
        $help = "\n" . $title . "\n" . $sub . "\n\n";
        $help .= Horde_String::wordwrap(
            $element->getHelp($action), 75, "\n", true
        );

        // @todo figure this out when needing contextual options :)
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
        $this->_dependencies->getCli()->writeln($help);
    }

    /**
     * Get the usage description for this module.
     *
     * @return string The description.
     */
    public function getUsage()
    {
        return "\n  help COMMAND  - Provide information about the specified COMMAND.";
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
