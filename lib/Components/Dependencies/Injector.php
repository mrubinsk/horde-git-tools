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

namespace Horde\GitTools\Components\Dependencies;

/**
 * The Components_Dependencies_Injector:: class provides the
 * Components dependencies based on the Horde injector.
 *
 * @author    Gunnar Wrobel <wrobel@pardus.de>
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Components
 * @category  Horde
 */
class Injector
extends \Components_Dependencies_Injector
implements \Components_Dependencies
{

    /**
     * Returns a component instance factory.
     *
     * @return Components_Component_Factory The component factory.
     */
    public function getComponentFactory()
    {
        return $this->getInstance('\Horde\GitTools\Components\Component\Factory');
    }


}
