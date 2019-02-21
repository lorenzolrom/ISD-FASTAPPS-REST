<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 6:35 PM
 */


namespace controllers;


use database\UserDatabaseHandler;
use exceptions\RouteException;
use exceptions\SecurityException;
use factories\UserFactory;
use messages\Messages;

class UserController extends Controller
{
    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function processURI(string $uri): array
    {
        $uriParts = explode("/", $uri);

        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            if (sizeof($uriParts) == 1 AND $uriParts[0] == "") // Get list of users
                return $this->getUsers();
            else if (sizeof($uriParts) == 1) // Get user details
                return $this->getUser(intval($uriParts[0]));
            else if (sizeof($uriParts) == 2 AND $uriParts[1] == "roles") // Get user roles
                return $this->getUserRoles(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            if (sizeof($uriParts) == 1 AND $uriParts[0] == "") // Insert new user
                return $this->createUser();
            else if (sizeof($uriParts) == 2 AND $uriParts[1] == "roles") // Add role to user
                return $this->addRole(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "PUT")
        {
            if (sizeof($uriParts) == 1) // Get user details
                return $this->updateUser(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "DELETE")
        {
            if (sizeof($uriParts) == 1) // Delete user
                return $this->deleteUser(intval($uriParts[0]));
            else if (sizeof($uriParts) == 3 AND $uriParts[1] == "roles") // Remove role from user
                return $this->removeRole(intval($uriParts[1]), intval($uriParts[2]));
        }

        throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
    }

    /**
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUsers(): array
    {
        FrontController::validatePermission('fa-users-listuserids');

        $users = array();

        foreach(UserDatabaseHandler::selectAllIDs() as $userID)
        {
            $users[] = ['type' => 'User', 'id' => $userID];
        }

        return ['data' => $users];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-showuserdetails');

        $user = UserFactory::getFromID($userID);

        return ['data' => ['type' => 'User',
                                 'id' => $user->getId(),
                                 'loginName' => $user->getLoginName(),
                                 'authType' => $user->getAuthType(),
                                 'firstName' => $user->getFirstName(),
                                 'lastName' => $user->getLastName(),
                                 'displayName' => $user->getDisplayName(),
                                 'email' => $user->getEmail(),
                                 'disabled' => $user->getDisabled()]];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUserRoles(int $userID): array
    {
        FrontController::validatePermission('fa-users-showuserroles');

        $roles = array();

        $user = UserFactory::getFromID($userID);

        foreach($user->getRoles() as $role)
        {
            $roles[] = ['type' => 'Role', 'id' => $role->getId(), 'displayName' => $role->getDisplayName()];
        }

        return ['data' => $roles];
    }

    /**
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function createUser()
    {
        FrontController::validatePermission('fa-users-create');
        http_response_code(501);
        return[];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function updateUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-update');
        http_response_code(501);
        return[];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function deleteUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-delete');
        http_response_code(501);
        return[];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function addRole(int $userID): array
    {
        FrontController::validatePermission('fa-users-modifyroles');
        http_response_code(501);
        return[];
    }

    /**
     * @param int $userID
     * @param int $roleId
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function removeRole(int $userID, int $roleId): array
    {
        FrontController::validatePermission('fa-users-modifyroles');
        http_response_code(501);
        return[];
    }
}