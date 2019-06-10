<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 5:32 PM
 */


namespace database\tickets;


use database\DatabaseConnection;
use exceptions\EntryNotFoundException;
use models\tickets\Update;

class UpdateDatabaseHandler
{
    /**
     * @param int $id
     * @return Update
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Update
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `ticket`, `user`, `time`, `description` FROM `Tickets_Update` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\tickets\Update');
    }

    /**
     * @param int $ticket
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByTicket(int $ticket): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Update` WHERE `ticket` = ?');
        $select->bindParam(1, $ticket, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $updates = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$updates[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $updates;
    }

    /**
     * @param int $ticket
     * @param int $user
     * @param string $description
     * @return Update
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $ticket, int $user, string $description): Update
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Update` (`ticket`, `user`, `time`, `description`) VALUES (:ticket, :user, NOW(), :description)');
        $insert->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }
}