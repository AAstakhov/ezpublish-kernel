<?php
/**
 * File contains: eZ\Publish\Core\Persistence\SqlNg\Tests\Content\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Tests\Content;

use eZ\Publish\Core\Persistence\SqlNg\Tests\TestCase;
use eZ\Publish\SPI\Persistence;

/**
 * Test case for LocationHandlerTest
 */
class LocationHandlerTest extends TestCase
{
    /**
     * Returns the handler to test
     *
     * @return \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Handler
     */
    protected function getLocationHandler()
    {
        return $this->getPersistenceHandler()->locationHandler();
    }

    public function testCreateRootLocation()
    {
        $handler = $this->getLocationHandler();

        $content = $this->getContent();
        $location = $handler->create(
            new Persistence\Content\Location\CreateStruct( array(
                'remoteId' => 'test-location-root',
                'contentId' => $content->versionInfo->contentInfo->id,
                'contentVersion' => $content->versionInfo->versionNo,
                'mainLocationId' => true,
                'parentId' => null,
            ) )
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Location',
            $location
        );
        $this->assertEquals( 1, $location->depth );

        return $location;
    }

    /**
     * @depends testCreateRootLocation
     */
    public function testCreateChildLocation( $root )
    {
        $handler = $this->getLocationHandler();

        $content = $this->getContent();
        $location = $handler->create(
            new Persistence\Content\Location\CreateStruct( array(
                'remoteId' => 'test-location-child',
                'contentId' => $content->versionInfo->contentInfo->id,
                'contentVersion' => $content->versionInfo->versionNo,
                'mainLocationId' => $root->id,
                'parentId' => $root->id,
            ) )
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Location',
            $location
        );
        $this->assertEquals( 2, $location->depth );

        return $location;
    }

    /**
     * @depends testCreateRootLocation
     */
    public function testUpdateLocation( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->update(
            new Persistence\Content\Location\UpdateStruct( array(
                'priority' => 1,
                'remoteId' => 'test-location-root-updated',
            ) ),
            $location->id
        );
    }

    /**
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testUpdateNotFound()
    {
        $handler = $this->getLocationHandler();

        $handler->update(
            new Persistence\Content\Location\UpdateStruct( array(
                'priority' => 1,
                'remoteId' => 'test-location-root-updated',
            ) ),
            1337
        );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testLoadLocation( $location )
    {
        $handler = $this->getLocationHandler();

        $loaded = $handler->load( $location->id );

        $this->assertEquals(
            $location,
            $loaded
        );
    }

    /**
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadLocationNotFound()
    {
        $handler = $this->getLocationHandler();

        $handler->load( 1337 );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testLoadLocationByRemoteId( $location )
    {
        $handler = $this->getLocationHandler();

        $loaded = $handler->loadByRemoteId( $location->remoteId );

        $this->assertEquals(
            $location,
            $loaded
        );
    }

    /**
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadLocationByRemoteIdNotFound()
    {
        $handler = $this->getLocationHandler();

        $handler->loadByRemoteId( 'not_existing' );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testLoadLocationsByContent( $location )
    {
        $handler = $this->getLocationHandler();

        $loaded = $handler->loadLocationsByContent( $location->contentId );

        $this->assertEquals( 2, count( $loaded ) );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testLoadLocationsByContentSubtree( $location )
    {
        $handler = $this->getLocationHandler();

        $loaded = $handler->loadLocationsByContent( $location->contentId, $location->id );

        $this->assertEquals(
            array( $location ),
            $loaded
        );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testHideUpdateHidden( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->hide( $location->parentId );

        $this->assertTrue(  $handler->load( $location->parentId )->hidden );
        $this->assertTrue(  $handler->load( $location->parentId )->invisible );
        $this->assertFalse( $handler->load( $location->id )->hidden );
        $this->assertTrue(  $handler->load( $location->id )->invisible );

        return $location;
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->unhide( $location->parentId );

        $this->assertFalse( $handler->load( $location->parentId )->hidden );
        $this->assertFalse( $handler->load( $location->parentId )->invisible );
        $this->assertFalse( $handler->load( $location->id )->hidden );
        $this->assertFalse( $handler->load( $location->id )->invisible );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testHideChildAndParent( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->hide( $location->id );
        $handler->hide( $location->parentId );

        $this->assertTrue( $handler->load( $location->parentId )->hidden );
        $this->assertTrue( $handler->load( $location->parentId )->invisible );
        $this->assertTrue( $handler->load( $location->id )->hidden );
        $this->assertTrue( $handler->load( $location->id )->invisible );

        return $location;
    }

    /**
     * @depends testHideChildAndParent
     */
    public function testUnhideChildHiddenParent( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->unhide( $location->id );

        $this->assertTrue(  $handler->load( $location->parentId )->hidden );
        $this->assertTrue(  $handler->load( $location->parentId )->invisible );
        $this->assertFalse( $handler->load( $location->id )->hidden );
        $this->assertTrue(  $handler->load( $location->id )->invisible );
    }

    /**
     * @depends testHideChildAndParent
     */
    public function testUnhideParentHiddenChild( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->hide( $location->id );
        $handler->unhide( $location->parentId );

        $this->assertFalse( $handler->load( $location->parentId )->hidden );
        $this->assertFalse( $handler->load( $location->parentId )->invisible );
        $this->assertTrue(  $handler->load( $location->id )->hidden );
        $this->assertTrue(  $handler->load( $location->id )->invisible );
    }

    /**
     * @depends testHideChildAndParent
     */
    public function testUnhideAll( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->unhide( $location->id );
        $handler->unhide( $location->parentId );

        $this->assertFalse( $handler->load( $location->parentId )->hidden );
        $this->assertFalse( $handler->load( $location->parentId )->invisible );
        $this->assertFalse( $handler->load( $location->id )->hidden );
        $this->assertFalse( $handler->load( $location->id )->invisible );
    }

    /**
     * @depends testCreateChildLocation
     */
    public function testSetSectionForSubtree( $location )
    {
        $handler = $this->getLocationHandler();

        $handler->setSectionForSubtree(
            $location->parentId,
            $sectionId = $this->getSection()->id
        );

        // No sensible assertions here…
    }

    public function testChangeMainLocation()
    {
        $handler = $this->getLocationHandler();

        $handler->changeMainLocation( 12, 34 );
    }

    public function testSwapLocations()
    {
        $handler = $this->getLocationHandler();

        $handler->swap( 70, 78 );
    }

    public function testMoveSubtree()
    {
        $handler = $this->getLocationHandler();

        $handler->move( 69, 77 );
    }

    public function testCopySubtree()
    {
        $handler = $this->getLocationHandler();

        $handler->copySubtree(
            $subtreeContentRows[0]["node_id"],
            $destinationData["node_id"]
        );
    }

    public function testRemoveSubtree()
    {
        $handler = $this->getLocationHandler();

        $handler->removeSubtree( 42 );
    }
}
