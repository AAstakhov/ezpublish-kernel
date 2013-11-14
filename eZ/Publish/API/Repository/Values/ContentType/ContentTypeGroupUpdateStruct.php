<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\MultiLanguageUpdateStructBase;

/**
 * This class is used for updating a content type group
 */
class ContentTypeGroupUpdateStruct extends MultiLanguageUpdateStructBase
{

    /**
     * If set this value overrides the current user as modifier
     *
     * @var mixed
     */
    public $modifierId = null;

    /**
     * If set this value overrides the current time for modified
     *
     * @var \DateTime
     */
    public $modificationDate = null;
}
