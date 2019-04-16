<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/13/2019
 * Time: 5:53 PM
 */


namespace business\itsm;


use business\Operator;
use controllers\CurrentUserController;
use database\itsm\AssetDatabaseHandler;
use database\itsm\PurchaseOrderDatabaseHandler;
use database\itsm\ReturnOrderDatabaseHandler;
use database\itsm\WarehouseDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use models\itsm\Warehouse;

class WarehouseOperator extends Operator
{
    /**
     * @param int $id
     * @return Warehouse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getWarehouse(int $id): Warehouse
    {
        return WarehouseDatabaseHandler::selectById($id);
    }

    /**
     * @param string $code
     * @param string $name
     * @param array $closed
     * @return Warehouse[]
     * @throws \exceptions\DatabaseException
     */
    public static function search($code = '%', $name = '%', $closed = array()): array
    {
        return WarehouseDatabaseHandler::select($code, $name, $closed);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createWarehouse(?string $code, ?string $name): array
    {
        $errors = self::validateSubmission($code, $name);

        if(!empty($errors))
            return array('errors' => $errors);


        $user = CurrentUserController::currentUser();

        return array('id' => WarehouseDatabaseHandler::insert($code, $name, 0, $user->getId(), date('Y-m-d'))->getId());
    }

    /**
     * @param Warehouse $warehouse
     * @param string $code
     * @param string $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateWarehouse(Warehouse $warehouse, string $code, string $name): array
    {
        $errors = self::validateSubmission($code, $name, $warehouse);

        if(!empty($errors))
            return array('errors' => $errors);

        $user = CurrentUserController::currentUser();

        return array('id' => WarehouseDatabaseHandler::update($warehouse->getId(), $code, $name, $user->getId(), date('Y-m-d'))->getId());
    }

    /**
     * @param Warehouse $warehouse
     * @return bool
     * @throws EntryInUseException
     * @throws \exceptions\DatabaseException
     */
    public static function deleteWarehouse(Warehouse $warehouse): bool
    {
        if(AssetDatabaseHandler::areAssetsInWarehouse($warehouse->getId())
            OR PurchaseOrderDatabaseHandler::doPurchaseOrdersReferenceWarehouse($warehouse->getId())
            OR ReturnOrderDatabaseHandler::doReturnOrdersReferenceWarehouse($warehouse->getId()))
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);
        }

        return WarehouseDatabaseHandler::delete($warehouse->getId());
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeInUse(string $code): bool
    {
        return WarehouseDatabaseHandler::selectIdFromCode($code) !== NULL;
    }

    /**
     * @param int $id
     * @return string
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(?int $id): ?string
    {
        if($id === NULL)
            return NULL;

        return WarehouseDatabaseHandler::selectCodeFromId($id);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param Warehouse|null $warehouse
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function validateSubmission(?string $code, ?string $name, ?Warehouse $warehouse = NULL): array
    {
        $errors = array();

        // code
        if($warehouse === NULL OR $warehouse->getCode() != $code)
        {
            try{Warehouse::validateCode($code);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // name
        try{Warehouse::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}