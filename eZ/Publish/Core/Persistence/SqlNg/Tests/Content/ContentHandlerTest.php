<?php
/**
 * File contains: eZ\Publish\Core\Persistence\SqlNg\Tests\Content\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Tests\Content;

use eZ\Publish\Core\Persistence\SqlNg\Tests\TestCase;

use eZ\Publish\SPI\Persistence;
use eZ\Publish\Core\Persistence\SqlNg;

/**
 * Test case for Content Handler
 */
class ContentHandlerTest extends TestCase
{
    /**
     * Returns the handler to test
     *
     * @return \eZ\Publish\Core\Persistence\SqlNg\Content\Handler
     */
    protected function getContentHandler()
    {
        return $this->getPersistenceHandler()->contentHandler();
    }

    public function testCtor()
    {
        $handler = $this->getContentHandler();
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\SqlNg\\Content\\Handler',
            $handler
        );
    }

    public function testCreateRoot()
    {
        $handler = $this->getContentHandler();

        $contentType = $this->getContentType();

        $createStruct = new Persistence\Content\CreateStruct( array(
            'typeId' => $contentType->id,
            'sectionId' => $this->getSection()->id,
            'ownerId' => $this->getUser()->id,
            'alwaysAvailable' => true,
            'remoteId' => 'testobject',
            'initialLanguageId' => $this->getLanguage()->id,
            'modified' => 123456789,
            'locations' => array(
                new Persistence\Content\Location\CreateStruct( array(
                    'remoteId' => 'testobject-location',
                    'parentId' => null,
                ) )
            ),
            'fields' => array(),
            'name' => array(
                $this->getLanguage()->languageCode => "Test-Objekt",
            ),
        ) );

        foreach ( $contentType->fieldDefinitions as $fieldDefinition )
        {
            $createStruct->fields[] = new Persistence\Content\Field( array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'value' => 'Hello World!',
                'languageCode' => $this->getLanguage()->languageCode,
            ) );
        }

        $content = $handler->create( $createStruct );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content',
            $content,
            'Content not created'
        );
        $this->assertInstanceOf(
            '\\eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo',
            $content->versionInfo,
            'Version infos not created'
        );
        $this->assertNotNull( $content->versionInfo->id );
        $this->assertNotNull( $content->versionInfo->contentInfo->id );
        $this->assertEquals(
            2,
            count( $content->fields ),
            'Fields not set correctly in version'
        );

        return $content;
    }

    /**
     * @depends testCreateRoot
     */
    public function testLoadRoot( $content )
    {
        $handler = $this->getContentHandler();

        $loaded = $handler->load(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo
        );

        $this->assertEquals(
            $content,
            $loaded
        );
    }

    /**
     * @depends testCreateRoot
     */
    public function testPublishRoot( $content )
    {
        $handler = $this->getContentHandler();

        $content = $handler->publish(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct( array(
                'ownerId' => $this->getUser()->id,
                'publicationDate' => 123456,
                'modificationDate' => 123456,
                'mainLanguageId' => $this->getLanguage()->id,
                'alwaysAvailable' => true,
                'remoteId' => 'updated',
            ) )
        );

        return $content;
    }

    /**
     * @depends testPublishRoot
     */
    public function testLoadPublishedVersion( $content )
    {
        $handler = $this->getContentHandler();

        $loaded = $handler->load(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo
        );

        $this->assertEquals(
            $content,
            $loaded
        );
    }

    // @TODO:
    // - Test create multilang content object
    // - Test loading modified content types object

    /**
     * @depends testPublishRoot
     */
    public function testCreateChildContent( $parent )
    {
        $handler = $this->getContentHandler();

        $contentType = $this->getContentType();

        $createStruct = new Persistence\Content\CreateStruct( array(
            'typeId' => $contentType->id,
            'sectionId' => $this->getSection()->id,
            'ownerId' => $this->getUser()->id,
            'alwaysAvailable' => true,
            'remoteId' => 'testobject-child',
            'initialLanguageId' => $this->getLanguage()->id,
            'modified' => 123456789,
            'locations' => array(
                new Persistence\Content\Location\CreateStruct( array(
                    'remoteId' => 'testobject-child-location',
                    'parentId' => $parent->versionInfo->contentInfo->id,
                ) )
            ),
            'fields' => array(),
            'name' => array(
                $this->getLanguage()->languageCode => "Kind-Objekt",
            ),
        ) );

        foreach ( $contentType->fieldDefinitions as $fieldDefinition )
        {
            $createStruct->fields[] = new Persistence\Content\Field( array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'value' => 'Hello World!',
                'languageCode' => $this->getLanguage()->languageCode,
            ) );
        }

        $child = $handler->create( $createStruct );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content',
            $child,
            'Content not created'
        );

        $child = $handler->publish(
            $child->versionInfo->contentInfo->id,
            $child->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct( array(
                'ownerId' => $this->getUser()->id,
                'publicationDate' => 123456,
                'modificationDate' => 123456,
                'mainLanguageId' => $this->getLanguage()->id,
                'alwaysAvailable' => true,
                'remoteId' => 'updated-child',
            ) )
        );

        return $child;
    }

    /**
     * @depends testPublishRoot
     */
    public function testCreateDraftFromVersion( $content )
    {
        $handler = $this->getContentHandler();

        $draft = $handler->createDraftFromVersion(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo,
            $this->getUser()->id
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content',
            $draft
        );
        $this->assertEquals(
            2,
            $draft->versionInfo->versionNo
        );


    }

    /**
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadErrorNotFound()
    {
        $handler = $this->getContentHandler();

        $handler->load( 1337, 2 );
    }

    /**
     * @depends testPublishRoot
     */
    public function testUpdateContent( $content )
    {
        $handler = $this->getContentHandler();

        $updateStruct = new Persistence\Content\UpdateStruct( array(
            'creatorId' => $this->getUser()->id,
            'modificationDate' => 12345467890,
            'initialLanguageId' => $this->getLanguage()->id,
            'fields' => array(),
        ) );

        $contentType = $this->getContentType();
        foreach ( $contentType->fieldDefinitions as $fieldDefinition )
        {
            $updateStruct->fields[] = new Persistence\Content\Field( array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'value' => 'Updated!',
                'languageCode' => $this->getLanguage()->languageCode,
                'versionNo' => $content->versionInfo->versionNo,
            ) );
        }

        $updatedContent = $handler->updateContent(
            $content->versionInfo->contentInfo->id,
            $content->versionInfo->versionNo,
            $updateStruct
        );
    }

    /**
     * @depends testPublishRoot
     */
    public function testUpdateMetadata( $content )
    {
        $handler = $this->getContentHandler();

        $updatedContentInfo = $handler->updateMetadata(
            14, // ContentId
            new MetadataUpdateStruct( array(
                'ownerId' => 14,
                'name' => 'Some name',
                'modificationDate' => time(),
                'alwaysAvailable' => true
            ) )
        );

        self::assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo',
            $updatedContentInfo
        );
    }

    /**
     * @depends testPublishRoot
     */
    public function testAddRelation( $content )
    {
        $handler = $this->getContentHandler();

        $relation = $handler->addRelation(
            new Relation\CreateStruct( array(
                'destinationContentId' => 66,
                'sourceContentId' => $content->versionInfo->contentInfo->id,
                'sourceContentVersionNo' => 1,
                'type' => RelationValue::COMMON,
            ) )
        );

        $this->assertEquals(
            array(),
            $relation
        );

        return $content;
    }

    /**
     * @depends testAddRelation
     */
    public function testLoadRelations( $content )
    {
        $handler = $this->getContentHandler();

        $relations = $handler->loadRelations( $content->versionInfo->contentInfo->id );

        $this->assertEquals(
            array(),
            $relations
        );
    }

    /**
     * @depends testAddRelation
     */
    public function testLoadReverseRelations( $content )
    {
        $handler = $this->getContentHandler();

        $relations = $handler->loadReverseRelations( $content->versionInfo->contentInfo->id );

        $this->assertEquals(
            array(),
            $relations
        );
    }

    /**
     * @depends testAddRelation
     */
    public function testRemoveRelation( $content )
    {
        $handler = $this->getContentHandler();

        $this->getContentHandler()->removeRelation( 1 );
    }

    /**
     * @depends testPublishRoot
     */
    public function testLoadDraftsForUser( $content )
    {
        $handler = $this->getContentHandler();

        $draft = $handler->loadDraftsForUser( $content->versionInfo->contentInfo->id );

        $this->assertEquals(
            array( new VersionInfo() ),
            $draft
        );
    }

    /**
     * @depends testPublishRoot
     */
    public function testListVersions( $content )
    {
        $handler = $this->getContentHandler();

        $versions = $handler->listVersions( $content->versionInfo->contentInfo->id );

        $this->assertEquals(
            array( new VersionInfo() ),
            $versions
        );
    }

    /**
     * @depends testPublishRoot
     */
    public function testRemoveRawContent( $content )
    {
        $handler = $this->getContentHandler();

        $handler->removeRawContent( $content->versionInfo->contentInfo->id );
    }

    /**
     * @depends testPublishRoot
     */
    public function testDeleteContentWithLocations( $content )
    {
        $handler = $this->getContentHandler();

        $handlerMock->deleteContent( $content->versionInfo->contentInfo->id );
    }

    /**
     * @depends testPublishRoot
     */
    public function testDeleteContentWithoutLocations( $content )
    {
        $handler = $this->getContentHandler();

        $handlerMock->deleteContent( $content->versionInfo->contentInfo->id );
    }

    /**
     * @depends testPublishRoot
     */
    public function testDeleteVersion( $content )
    {
        $handler = $this->getContentHandler();

        $handler->deleteVersion( 225, 2 );
    }

    /**
     * @depends testPublishRoot
     */
    public function testCopySingleVersion( $content )
    {
        $handler = $this->getContentHandler();

        $content = $handler->copy( $content->versionInfo->contentInfo->id, 32 );

        $this->assertInstanceOf(
            "eZ\\Publish\\SPI\\Persistence\\Content",
            $content
        );
    }

    /**
     * @depends testPublishRoot
     */
    public function testCopyAllVersions( $content )
    {
        $handler = $this->getContentHandler();

        $content = $handler->copy( $content->versionInfo->contentInfo->id );

        $this->assertInstanceOf(
            "eZ\\Publish\\SPI\\Persistence\\Content",
            $content
        );
    }

    /**
     * @depends testPublishRoot
     */
    public function testCopyThrowsNotFoundExceptionContentNotFound( $content )
    {
        $handler = $this->getContentHandler();

        $result = $handler->copy( $content->versionInfo->contentInfo->id );
    }

    /**
     * @depends testPublishRoot
     */
    public function testCopyThrowsNotFoundExceptionVersionNotFound( $content )
    {
        $handler = $this->getContentHandler();

        $result = $handler->copy( $content->versionInfo->contentInfo->id, 32 );
    }

    /**
     * @depends testPublishRoot
     */
    public function testSetStatus( $content )
    {
        $handler = $this->getContentHandler();

        $this->assertTrue(
            $handler->setStatus( $content->versionInfo->contentInfo->id, 2, 5 )
        );
    }
}
