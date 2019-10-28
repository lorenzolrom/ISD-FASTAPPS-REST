<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/25/2019
 * Time: 10:16 AM
 */


namespace business\ie442;


use business\Operator;
use business\UserOperator;
use database\ie442\HeartbeatDatabaseHandler;
use database\PermissionDatabaseHandler;
use exceptions\EntryNotFoundException;
use models\User;

class HeartbeatOperator extends Operator
{
    /**
     * @param User $user
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function updateHeartbeat(User $user): bool
    {
        if(!HeartbeatDatabaseHandler::update($user->getId())) // If no heartbeat exists...
            HeartbeatDatabaseHandler::insert($user->getId()); // ...create it

        // The above statement will have a heartbeat inserted/updated with current time

        return TRUE;
    }

    /**
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function getActiveUsers(): array
    {
        $userIds = HeartbeatDatabaseHandler::selectActive(10);

        $users = array();

        foreach($userIds as $userId)
        {
            try{$users[] = UserOperator::getUser($userId);}
            catch(EntryNotFoundException $e){}
        }

        return $users;
    }

    /**
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function getChatUsers(): array
    {
        return PermissionDatabaseHandler::selectUsersByPermission('chat');
    }
}