<?php
/**
 * File containing the IdManager class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\IdManager;

use eZ\Publish\API\Repository\Tests\IdManager;

/**
 * ID manager that provides a mapping between legacy identifiers and sqlng
 * identifiers.
 */
class SqlNg extends IdManager
{
    private $mapping = array(
        'group' => array(
            4 => 1,
            13 => 7,
        )
    );

    /**
     * Generates a repository specific ID.
     *
     * Generates a repository specific ID for an object of $type from the
     * database ID $rawId.
     *
     * @param string $type
     * @param mixed $rawId
     *
     * @return mixed
     */
    public function generateId( $type, $rawId )
    {
        if ( isset( $this->mapping[$type][$rawId] ) )
        {
            return $this->mapping[$type][$rawId];
        }
        // TODO Throw an exception?
        return $rawId;
    }

    /**
     * Parses the given $id for $type into its raw form.
     *
     * Takes a repository specific $id of $type and returns the raw database ID
     * for the object.
     *
     * @param string $type
     * @param mixed $id
     *
     * @return mixed
     */
    public function parseId( $type, $id )
    {
        if ( isset( $this->mapping[$type] ) && is_int( $rawId = array_search( $id, $this->mapping[$type] ) ) )
        {
            return $rawId;
        }
        // TODO Throw an exception?
        return $id;
    }
}
