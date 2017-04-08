<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see https://www.horde.org/licenses/bsd.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools\Components\Component;

use Components_Component_Factory;
use Horde_Pear_Package_Contents_List;
use Horde_Pear_Package_Type_HordeSplit;

/**
 * Extend Components_Component_Factory to deal with new split repository
 * structure.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class Factory extends Components_Component_Factory
{

    /**
     * Create and return a new Helper Root object.
     *
     * @return \Horde\GitTools\Components\Helper\Root
     */
    public function createGitRoot()
    {
        return new \Horde\GitTools\Components\Helper\Root(
            $this->_config->getOptions()
        );
    }

    /**
     * Create a new PEAR Package representation.
     *
     * @param string $package_xml_dir Path to the parent directory of the
     *                                new package.xml file.
     *
     * @return PEAR_PackageFile
     */
    public function createPackageFile($package_xml_dir)
    {
        $type = new Horde_Pear_Package_Type_HordeSplit($package_xml_dir);
        $type->writePackageXmlDraft();
    }

    /**
     * Create a new content listing.
     *
     * @param string $package_xml_dir Path to the parent directory of the
     *                                new package.xml file.
     *
     * @return Horde_Pear_Package_Contents_List
     */
    public function createContentList($package_xml_dir)
    {
        return new Horde_Pear_Package_Contents_List(
            new Horde_Pear_Package_Type_HordeSplit(
                $package_xml_dir,
                $this->getGitRoot()->getRoot()
            )
        );
    }

}
