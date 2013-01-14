<?php
/**
 * File containing the Section Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Content\Section;

use eZ\Publish\SPI\Persistence\Content\Section\Handler as BaseSectionHandler;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use RuntimeException;

/**
 * Section Handler
 */
class Handler implements BaseSectionHandler
{
    /**
     * Section Gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Section\Gateway
     */
    protected $sectionGateway;

    /**
     * Creates a new Section Handler
     *
     * @param \eZ\Publish\Core\Persistence\SqlNg\Content\Section\Gateway $sectionGateway
     */
    public function __construct( Gateway $sectionGateway  )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Create a new section
     *
     * @param string $name
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function create( $name, $identifier )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Update name and identifier of a section
     *
     * @param mixed $id
     * @param string $name
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function update( $id, $name, $identifier )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Get section data
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function load( $id )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Get all section data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section[]
     */
    public function loadAll()
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Get section data by identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If section is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Section
     */
    public function loadByIdentifier( $identifier )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Delete a section
     *
     * Might throw an exception if the section is still associated with some
     * content objects. Make sure that no content objects are associated with
     * the section any more *before* calling this method.
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Assigns section to single content object
     *
     * @param mixed $sectionId
     * @param mixed $contentId
     */
    public function assign( $sectionId, $contentId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Number of content assignments a Section has
     *
     * @param mixed $sectionId
     *
     * @return int
     */
    public function assignmentsCount( $sectionId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }
}
