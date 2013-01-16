<?php
/**
 * File contains: eZ\Publish\Core\Persistence\SqlNg\Tests\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Tests\Content\Type;

use eZ\Publish\Core\Persistence\SqlNg\Tests\TestCase;
use eZ\Publish\SPI\Persistence;
use eZ\Publish\Core\Persistence\SqlNg;

/**
 * Test case for Content Type Handler.
 */
class ContentTypeHandlerTest extends TestCase
{
    /**
     * Returns a handler to test, based on mock objects
     *
     * @return \eZ\Publish\Core\Persistence\SqlNg\Content\Type\Handler
     */
    protected function getHandler()
    {
        return $this->getPersistenceHandler()->contentTypeHandler();
    }

    public function testCtor()
    {
        $handler = $this->getHandler();
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\SqlNg\\Content\\Type\\Handler',
            $handler
        );
    }

    public function testCreateGroup()
    {
        $handler = $this->getHandler();
        $group = $handler->createGroup(
            new Persistence\Content\Type\Group\CreateStruct( $values = array(
                'identifier' => 'testgroup',
                'created' => 123456789,
                'creatorId' => $this->getUser()->id,
                'modified' => 123456789,
                'modifierId' => $this->getUser()->id,
                'name' => array(
                    'de' => 'Test-Gruppe',
                ),
                'description' => array(
                    'de' => 'Test-Gruppe',
                ),
            ) )
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group',
            $group
        );

        $this->assertPropertiesCorrect( $values, $group );

        return $group;
    }

    /**
     * @depends testCreateGroup
     */
    public function testLoadGroup( $group )
    {
        $handler = $this->getHandler();
        $loaded = $handler->loadGroup( $group->id );

        $this->assertEquals( $group, $loaded );
    }

    /**
     * @depends testCreateGroup
     */
    public function testLoadGroupByIdentifier( $group )
    {
        $handler = $this->getHandler();
        $loaded = $handler->loadGroupByIdentifier( $group->identifier );

        $this->assertEquals( $group, $loaded );
    }

    /**
     * @depends testCreateGroup
     */
    public function testLoadAllGroups( $group )
    {
        $handler = $this->getHandler();
        $loaded = $handler->loadAllGroups();

        $this->assertEquals( array( $group ), $loaded );
    }

    /**
     * @depends testCreateGroup
     */
    public function testUpdateGroup( $group )
    {

        $handler = $this->getHandler();
        $group = $handler->updateGroup(
            new Persistence\Content\Type\Group\UpdateStruct( $values = array(
                'id' => $group->id,
                'identifier' => 'updated',
                'modified' => 123456789,
                'modifierId' => $this->getUser()->id,
            ) )
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group',
            $group
        );

        $this->assertPropertiesCorrect( $values, $group );
    }

    /**
     * @depends testCreateGroup
     */
    public function testDeleteGroupSuccess( $group )
    {
        $handler = $this->getHandler();
        $handler->deleteGroup( $group->id );

        return $group;
    }

    /**
     * @depends testDeleteGroupSuccess
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testDeleteNotExistingGroup( $group )
    {
        $handler = $this->getHandler();
        $handler->deleteGroup( $group->id );
    }

    /**
     * @depends testDeleteGroupSuccess
     */
    public function testDeleteNonEmptyGroup( $group )
    {
        $this->markTestIncomplete( "Requires creation of a type first." );

        $handler = $this->getHandler();
        $handler->deleteGroup( $group->id );
    }

    /**
     * @depends testCreateGroup
     */
    public function testCreate()
    {
        $handler = $this->getHandler();

        $group = $handler->createGroup(
            new Persistence\Content\Type\Group\CreateStruct( $values = array(
                'identifier' => 'testgroup',
                'created' => 123456789,
                'creatorId' => $this->getUser()->id,
                'modified' => 123456789,
                'modifierId' => $this->getUser()->id,
            ) )
        );

        $type = $handler->create(
            new Persistence\Content\Type\CreateStruct( array(
                'identifier' => 'testtype',
                'status' => 0,
                'groupIds' => array( $group->id ),
                'created' => 123456789,
                'creatorId' => $this->getUser()->id,
                'modified' => 123456789,
                'modifierId' => $this->getUser()->id,
                'remoteId' => 'testtype',
                'initialLanguageId' => $this->getLanguage()->id,
                'fieldDefinitions' => array(
                    new Persistence\Content\Type\FieldDefinition( array(
                        'identifier' => 'title',
                        'fieldGroup' => '1',
                        'position' => 1,
                        'fieldType' => 'ezstring',
                        'isTranslatable' => true,
                        'isRequired' => true,
                        'isInfoCollector' => true,
                        'fieldTypeConstraints' => array(
                            'minLength' => 5,
                            'maxLength' => 20,
                        ),
                        'defaultValue' => 'Hello World!',
                        'isSearchable' => true,
                        'name' => array(
                            'de' => 'Test-Feld',
                        ),
                        'description' => array(
                            'de' => 'Test-Feld',
                        ),
                    ) )
                ),
                'name' => array(
                    'de' => 'Test-Typ',
                ),
                'description' => array(
                    'de' => 'Test-Typ',
                ),
            ) )
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $type
        );
        $this->assertNotNull( $type->id );
        return $type;
    }

    /**
     * @depends testCreate
     */
    public function testLoadContentTypes( $type )
    {
        $handler = $this->getHandler();
        $loaded = $handler->loadContentTypes( $type->groupIds[0], $type->status );

        $this->assertEquals( array( $type ), $loaded );
    }

    /**
     * @depends testCreate
     */
    public function testLoad( $type )
    {
        $handler = $this->getHandler();
        $loaded = $handler->load( $type->id, $type->status );

        $this->assertEquals( $type, $loaded );
    }

    /**
     * @return void
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadNotFound()
    {
        $handler = $this->getHandler();
        $type = $handler->load( 1337 );
    }

    /**
     * @depends testCreate
     */
    public function testLoadDefaultVersion( $type )
    {
        $handler = $this->getHandler();
        $loaded = $handler->load( $type->id );

        $this->assertEquals( $type, $loaded );
    }

    /**
     * @depends testCreate
     */
    public function testLoadByIdentifier( $type )
    {
        $handler = $this->getHandler();
        $loaded = $handler->loadByIdentifier( $type->identifier );

        $this->assertEquals( $type, $loaded );
    }

    /**
     * @depends testCreate
     */
    public function testLoadByRemoteId( $type )
    {
        $handler = $this->getHandler();
        $loaded = $handler->loadByRemoteId( $type->remoteId );

        $this->assertEquals( $type, $loaded );
    }


    /**
     * @depends testCreate
     */
    public function testUpdate( $type )
    {
        $handler = $this->getHandler();
        $updated = $handler->update(
            $type->id,
            $type->status,
            new Persistence\Content\Type\UpdateStruct( array(
            ) )
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $updated
        );
    }

    /**
     *
     * @return void
     */
    public function testDeleteSuccess()
    {
        $handler = $this->getHandler();
        $res = $handler->delete( 23, 0 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testDeleteThrowsNotFoundException()
    {
        $handler = $this->getHandler();
        $res = $handler->delete( 23, 0 );
    }

    /**
     * @return void
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testDeleteThrowsBadStateException()
    {
        $handler = $this->getHandler();
        $res = $handler->delete( 23, 0 );
    }

    /**
     *
     * @return void
     */
    public function testCreateVersion()
    {
        $res = $handlerMock->createDraft(
            42, 23
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     *
     * @return void
     */
    public function testCopy()
    {
        $res = $handlerMock->copy(
            42, 23, 0
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     *
     * @return void
     */
    public function testLink()
    {
        $handler = $this->getHandler();
        $res = $handler->link( 3, 23, 1 );

        $this->assertTrue( $res );
    }

    /**
     *
     * @return void
     */
    public function testUnlinkSuccess()
    {
        $handler = $this->getHandler();
        $res = $handler->unlink( 3, 23, 1 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @expectedException eZ\Publish\Core\Persistence\SqlNg\Exception\RemoveLastGroupFromType
     * @expectedExceptionMessage Type with ID "23" in status "1" cannot be unlinked from its last group.
     */
    public function testUnlinkFailure()
    {
        $handler = $this->getHandler();
        $res = $handler->unlink( 3, 23, 1 );
    }

    /**
     *
     * @return void
     */
    public function testGetFieldDefinition()
    {
        $handler = $this->getHandler();
        $fieldDefinition = $handler->getFieldDefinition( 42, Type::STATUS_DEFINED );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition',
            $fieldDefinition
        );
    }

    /**
     *
     * @return void
     */
    public function testAddFieldDefinition()
    {
        $fieldDef = new FieldDefinition();

        $handler = $this->getHandler();
        $handler->addFieldDefinition( 23, 1, $fieldDef );

        $this->assertEquals(
            42,
            $fieldDef->id
        );
    }

    /**
     *
     * @return void
     */
    public function testRemoveFieldDefinition()
    {
        $handler = $this->getHandler();
        $res = $handler->removeFieldDefinition( 23, 1, 42 );

        $this->assertTrue( $res );
    }

    /**
     *
     * @return void
     */
    public function testUpdateFieldDefinition()
    {
        $fieldDef = new FieldDefinition();

        $handler = $this->getHandler();
        $res = $handler->updateFieldDefinition( 23, 1, $fieldDef );

        $this->assertNull( $res );
    }

    /**
     *
     * @return void
     */
    public function testPublish()
    {
        $handler = $this->getHandler();
        $handler->publish( 23 );
    }

    /**
     *
     * @return void
     */
    public function testPublishNoOldType()
    {
        $handler = $this->getHandler();
        $handler->publish( 23 );
    }
}
