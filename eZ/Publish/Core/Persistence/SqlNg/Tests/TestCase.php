<?php
/**
 * File contains: eZ\Publish\Core\Persistence\SqlNg\Tests\TestCase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\Persistence\SqlNg;
use eZ\Publish\SPI\Persistence;

/**
 * Base test case for database related tests
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected static $dsn;

    protected static $db;

    protected static $persistenceHandler;

    protected static $user;

    protected static $language;

    protected static $section;

    protected static $contentTypeGroup;

    protected static $contentType;

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     * Get persistence handler
     *
     * @return Handler
     */
    protected function getPersistenceHandler()
    {
        if ( !self::$persistenceHandler )
        {
            self::$persistenceHandler = new SqlNg\Handler(
                $this->getDatabaseHandler(),
                new SqlNg\Content\StorageRegistry( array() )
            );

            $this->applyStatements(
                $this->getSchemaStatements()
            );
        }

        return self::$persistenceHandler;
    }

    /**
     * Applies the given SQL $statements to the database in use
     *
     * @param array $statements
     *
     * @return void
     */
    protected function applyStatements( array $statements )
    {
        $dbHandler = $this->getDatabaseHandler();
        $dbHandler->beginTransaction();
        foreach ( $statements as $statement )
        {
            $dbHandler->exec( $statement );
        }
        $dbHandler->commit();
    }

    /**
     * Returns the database schema as an array of SQL statements
     *
     * @return string[]
     */
    protected function getSchemaStatements()
    {

        return array_filter(
            preg_split(
                '(;\\s*$)m',
                file_get_contents(
                    __DIR__ . '/../schema/schema.' . self::$db . '.sql'
                )
            )
        );
    }

    /**
     * Get database handler
     *
     * @return EzcDbHandler
     */
    protected function getDatabaseHandler()
    {
        self::$dsn = getenv( "DATABASE" ) ?: "sqlite://:memory:";
        self::$db = preg_replace( '(^([a-z]+).*)', '\\1', self::$dsn );

        return new EzcDbHandler(
            \ezcDbFactory::create( self::$dsn )
        );
    }

    /**
     * Get a real user in the database
     *
     * @return Persistence\User
     */
    protected function getUser()
    {
        if ( !self::$user )
        {
            $userHandler = $this->getPersistenceHandler()->userHandler();
            self::$user = $userHandler->create( new Persistence\User( array(
                'id' => 14,
                'login' => 'admin',
                'email' => 'admin@example.com',
                'hashAlgorithm' => 0,
                'passwordHash' => '*',
            ) ) );
        }

        return self::$user;
    }

    /**
     * Get a real language in the database
     *
     * @return Persistence\Language
     */
    protected function getLanguage()
    {
        if ( !self::$language )
        {
            $languageHandler = $this->getPersistenceHandler()->contentLanguageHandler();
            self::$language = $languageHandler->create(
                new Persistence\Content\Language\CreateStruct( array(
                    'languageCode' => 'de_DE',
                    'name' => 'German',
                    'isEnabled' => true,
                ) )
            );
        }

        return self::$language;
    }

    /**
     * Get a real section in the database
     *
     * @return Persistence\Content\Section
     */
    protected function getSection()
    {
        if ( !self::$section )
        {
            $sectionHandler = $this->getPersistenceHandler()->sectionHandler();
            self::$section = $sectionHandler->create(
                "Test Section",
                "testsection"
            );
        }

        return self::$section;
    }

    /**
     * Get a functional content type group
     *
     * @return Persistence\Content\Type\Group
     */
    protected function getContentTypeGroup()
    {
        if ( !self::$contentTypeGroup )
        {
            $contentTypeHandler = $this->getPersistenceHandler()->contentTypeHandler();
            self::$contentTypeGroup = $contentTypeHandler->createGroup(
                new Persistence\Content\Type\Group\CreateStruct( $values = array(
                    'identifier' => 'testgroup',
                    'created' => time(),
                    'creatorId' => $this->getUser()->id,
                    'modified' => time(),
                    'modifierId' => $this->getUser()->id,
                ) )
            );
        }

        return self::$contentTypeGroup;
    }

    /**
     * Get a functional content type
     *
     * @return Persistence\Content\Type
     */
    protected function getContentType()
    {
        if ( !self::$contentType )
        {
            $contentTypeHandler = $this->getPersistenceHandler()->contentTypeHandler();
            self::$contentType = $contentTypeHandler->create(
                new Persistence\Content\Type\CreateStruct( array(
                    'identifier' => 'testtype',
                    'status' => 1,
                    'groupIds' => array( $this->getContentTypeGroup()->id ),
                    'created' => time(),
                    'creatorId' => $this->getUser()->id,
                    'modified' => time(),
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
                            'isSearchable' => true,
                        ) ),
                        new Persistence\Content\Type\FieldDefinition( array(
                            'identifier' => 'text',
                            'fieldGroup' => '1',
                            'position' => 2,
                            'fieldType' => 'eztext',
                            'isTranslatable' => true,
                            'isRequired' => true,
                            'isInfoCollector' => true,
                            'isSearchable' => true,
                        ) ),
                    ),
                ) )
            );
        }

        return self::$contentType;
    }

    /**
     * Asserts correct property values on $object.
     *
     * Asserts that for all keys in $properties a corresponding property
     * exists in $object with the *same* value as in $properties.
     *
     * @param array $properties
     * @param object $object
     *
     * @return void
     */
    public static function assertPropertiesCorrect( array $properties, $object )
    {
        if ( !is_object( $object ) )
        {
            throw new \InvalidArgumentException(
                'Expected object as second parameter, received ' . gettype( $object )
            );
        }
        foreach ( $properties as $propName => $propVal )
        {
            self::assertEquals(
                $propVal,
                $object->$propName,
                "Incorrect value for \${$propName}"
            );
        }
    }
}
