<?php
/**
 * File containing the EzcDatabase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Content\Type\Gateway;

use eZ\Publish\Core\Persistence\SqlNg\Content\Type\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use ezcQuery;
use ezcQuerySelect;

/**
 * Zeta Component Database based content type gateway.
 */
class EzcDatabase extends Gateway
{
    /**
     * Zeta Components database handler.
     *
     * @var \ezcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new gateway based on $dbHandler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     */
    public function __construct( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Inserts the given $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group $group
     *
     * @return mixed Group ID
     */
    public function insertGroup( Group $group )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( 'ezcontenttype_group' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcontenttype_group', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $group->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'created' ),
            $query->bindValue( $group->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $query->bindValue( $group->creatorId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $query->bindValue( $group->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $query->bindValue( $group->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $query->bindValue( json_encode( $group->name ) )
        )->set(
            $this->dbHandler->quoteColumn( 'description' ),
            $query->bindValue( json_encode( $group->description ) )
        );
        $query->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcontenttype_group', 'id' )
        );
    }

    /**
     * Updates a group with data in $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $group
     *
     * @return void
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteColumn( 'ezcontenttype_group' )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $group->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $query->bindValue( $group->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $query->bindValue( $group->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $query->bindValue( json_encode( $group->name ) )
        )->set(
            $this->dbHandler->quoteColumn( 'description' ),
            $query->bindValue( json_encode( $group->description ) )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $group->id, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Returns the number of Groups the type is assigned to.
     *
     * @param int $typeId
     * @param int $status
     *
     * @return int
     */
    public function countGroupsForType( $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Deletes the Group with the given $groupId.
     *
     * @param int $groupId
     *
     * @return void
     */
    public function deleteGroup( $groupId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( $this->dbHandler->quoteTable( 'ezcontenttype_group' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $groupId, null, \PDO::PARAM_INT )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $statement->rowCount() < 1 )
        {
            throw new NotFound( 'group', $groupId );
        }
    }

    /**
     * Inserts a new content type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     * @param mixed|null $typeId
     *
     * @return mixed Type ID
     */
    public function insertType( Type $type, $typeId = null )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto( $this->dbHandler->quoteTable( 'ezcontenttype' ) );
        $query->set(
            $this->dbHandler->quoteColumn( 'id' ),
            isset( $typeId ) ?
                $query->bindValue( $typeId, null, \PDO::PARAM_INT ) :
                $this->dbHandler->getAutoIncrementValue( 'ezcontenttype', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $query->bindValue( $type->status, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'created' ),
            $query->bindValue( $type->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $query->bindValue( $type->creatorId, null, \PDO::PARAM_INT )
        );
        $this->setCommonTypeColumns( $query, $type );

        $query->prepare()->execute();

        if ( empty( $typeId ) )
        {
            $typeId = $this->dbHandler->lastInsertId(
                $this->dbHandler->getSequenceName( 'ezcontenttype', 'id' )
            );
        }

        return $typeId;
    }

    /**
     * Set common columns for insert/update of a Type.
     *
     * @param \ezcQuery $query
     * @param mixed $type
     *
     * @return void
     */
    protected function setCommonTypeColumns( ezcQuery $query, $type )
    {
        $query->set(
            $this->dbHandler->quoteColumn( 'name_list' ),
            $query->bindValue( json_encode( $type->name ) )
        )->set(
            $this->dbHandler->quoteColumn( 'description_list' ),
            $query->bindValue( json_encode( $type->description ) )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $type->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $query->bindValue( $type->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $query->bindValue( $type->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'remote_id' ),
            $query->bindValue( $type->remoteId )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_name' ),
            $query->bindValue( $type->nameSchema )
        )->set(
            $this->dbHandler->quoteColumn( 'is_container' ),
            $query->bindValue( $type->isContainer ? 1 : 0, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $query->bindValue( $type->initialLanguageId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_field' ),
            $query->bindValue( $type->sortField, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_order' ),
            $query->bindValue( $type->sortOrder, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'always_available' ),
            $query->bindValue( (int)$type->defaultAlwaysAvailable, null, \PDO::PARAM_INT )
        );
    }

    /**
     * Insert assignment of $typeId to $groupId.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function insertGroupAssignment( $groupId, $typeId, $status )
    {
        $groups = $this->loadGroupData( $groupId );
        $group = $groups[0];

        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( 'ezcontenttype_group_rel' )
        )->set(
            $this->dbHandler->quoteColumn( 'contenttype_id' ),
            $query->bindValue( $typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'group_id' ),
            $query->bindValue( $groupId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $query->bindValue( $status, null, \PDO::PARAM_INT )
        );

        $query->prepare()->execute();
    }

    /**
     * Deletes a group assignments for a Type.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteGroupAssignment( $groupId, $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Loads data about Group with $groupId.
     *
     * @param mixed $groupId
     *
     * @return string[][]
     */
    public function loadGroupData( $groupId )
    {
        $query = $this->createGroupLoadQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );
        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads data about Group with $identifier.
     *
     * @param mixed $identifier
     *
     * @return string[][]
     */
    public function loadGroupDataByIdentifier( $identifier )
    {
        $query = $this->createGroupLoadQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'identifier' ),
                $query->bindValue( $identifier, null, \PDO::PARAM_STR )
            )
        );
        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Returns an array with data about all Group objects.
     *
     * @return string[][]
     */
    public function loadAllGroupsData()
    {
        $query = $this->createGroupLoadQuery();

        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads data for all Types in $status in $groupId.
     *
     * @param mixed $groupId
     * @param int $status
     *
     * @return string[][]
     */
    public function loadTypesDataForGroup( $groupId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return mixed Field definition ID
     */
    public function insertFieldDefinition( $typeId, FieldDefinition $fieldDefinition )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto( $this->dbHandler->quoteTable( 'ezcontenttype_field' ) );
        $query->set(
            $this->dbHandler->quoteColumn( 'id' ),
            isset( $fieldDefinition->id ) ?
                $query->bindValue( $fieldDefinition->id, null, \PDO::PARAM_INT ) :
                $this->dbHandler->getAutoIncrementValue( 'ezcontenttype_field', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'contenttype_id' ),
            $query->bindValue( $typeId, null, \PDO::PARAM_INT )
        );
        $this->setCommonFieldColumns( $query, $fieldDefinition );

        $query->prepare()->execute();

        if ( !isset( $fieldDefinition->id ) )
        {
            return $this->dbHandler->lastInsertId(
                $this->dbHandler->getSequenceName( 'ezcontentclass_attribute', 'id' )
            );
        }

        return $fieldDefinition->id;
    }

    /**
     * Set common columns for insert/update of FieldDefinition.
     *
     * @param \ezcQuery $query
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return void
     */
    protected function setCommonFieldColumns( ezcQuery $query, FieldDefinition $fieldDefinition )
    {
        $query->set(
            $this->dbHandler->quoteColumn( 'name_list' ),
            $query->bindValue( json_encode( $fieldDefinition->name ) )
        )->set(
            $this->dbHandler->quoteColumn( 'description_list' ),
            $query->bindValue( json_encode( $fieldDefinition->description ) )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $fieldDefinition->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'field_group' ),
            $query->bindValue( $fieldDefinition->fieldGroup, null, \PDO::PARAM_STR )
        )->set(
            $this->dbHandler->quoteColumn( 'placement' ),
            $query->bindValue( $fieldDefinition->position, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'type_string' ),
            $query->bindValue( $fieldDefinition->fieldType )
        )->set(
            $this->dbHandler->quoteColumn( 'can_translate' ),
            $query->bindValue( ( $fieldDefinition->isTranslatable ? 1 : 0 ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'is_required' ),
            $query->bindValue( ( $fieldDefinition->isRequired ? 1 : 0 ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'is_information_collector' ),
            $query->bindValue( ( $fieldDefinition->isInfoCollector ? 1 : 0 ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'constraints' ),
            $query->bindValue( json_encode( $fieldDefinition->fieldTypeConstraints ) )
        )->set(
            $this->dbHandler->quoteColumn( 'default_value' ),
            $query->bindValue( json_encode( $fieldDefinition->defaultValue ) )
        )->set(
            $this->dbHandler->quoteColumn( 'is_searchable' ),
            $query->bindValue( ( $fieldDefinition->isSearchable ? 1 : 0 ), null, \PDO::PARAM_INT )
        );
    }

    /**
     * Loads an array with data about field definition referred $id and $status.
     *
     * @param mixed $id field definition id
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return array Data rows.
     */
    public function loadFieldDefinition( $id )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param mixed $fieldDefinitionId
     *
     * @return void
     */
    public function deleteFieldDefinition( $typeId, $fieldDefinitionId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Updates a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return void
     */
    public function updateFieldDefinition( $typeId, FieldDefinition $fieldDefinition )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Update a type with $updateStruct.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $updateStruct
     *
     * @return void
     */
    public function updateType( $typeId, $status, UpdateStruct $updateStruct )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Loads an array with data about $typeId in $status.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return array Data rows.
     */
    public function loadTypeData( $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Loads an array with data about the type referred to by $identifier in
     * $status.
     *
     * @param string $identifier
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeDataByIdentifier( $identifier, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Loads an array with data about the type referred to by $remoteId in
     * $status.
     *
     * @param mixed $remoteId
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeDataByRemoteId( $remoteId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Counts the number of instances that exists of the identified type.
     *
     * @param int $typeId
     *
     * @return int
     */
    public function countInstancesOfType( $typeId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteFieldDefinitionsForType( $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Deletes a Type completely.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function delete( $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteType( $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Deletes all group assignments for a Type.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteGroupAssignmentsForType( $typeId, $status )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Publishes the Type with $typeId from $sourceVersion to $targetVersion,
     * including its fields
     *
     * @param int $typeId
     * @param int $sourceVersion
     * @param int $targetVersion
     *
     * @return void
     */
    public function publishTypeAndFields( $typeId, $sourceVersion, $targetVersion )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Creates the basic query to load Group data.
     *
     * @return ezcQuerySelect
     */
    protected function createGroupLoadQuery()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->quoteColumn( 'identifier' ),
            $this->dbHandler->quoteColumn( 'created' ),
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $this->dbHandler->quoteColumn( 'modified' ),
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $this->dbHandler->quoteColumn( 'name' ),
            $this->dbHandler->quoteColumn( 'description' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontenttype_group' )
        );
        return $query;
    }
}
