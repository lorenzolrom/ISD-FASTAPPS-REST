<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/01/2019
 * Time: 1:10 PM
 */


namespace extensions\netuserman\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\ValidationError;
use extensions\netuserman\business\NetUserOperator;
use models\HTTPRequest;
use models\HTTPResponse;
use utilities\LDAPConnection;

class NetUserController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('netuserman');

        $param = $this->request->next();
        $next = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            CurrentUserController::validatePermission('netuserman-read');

            if($param !== NULL)
            {
                if($next === 'photo')
                    return $this->getUserImage((string)$param);
                else if($next === NULL)
                    return $this->getSingleUser((string)$param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param === 'search' AND $next === NULL)
            {
                CurrentUserController::validatePermission('netuserman-read');
                return $this->searchUsers();
            }
            else if($next === 'photo')
            {
                CurrentUserController::validatePermission('netuserman-edit-details');
                return $this->updateUserImage((string)$param);
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('netuserman-edit-details');
            return $this->updateUser((string)$param);
        }

        return NULL;
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    private function getSingleUser(string $username): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, NetUserOperator::getUserDetails($username));
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    private function getUserImage(string $username): HTTPResponse
    {
        $photo = NetUserOperator::getUserDetails($username, array('thumbnailPhoto'));

        if(!isset($photo['thumbnailphoto']))
            $photo['thumbnailphoto'] = file_get_contents(dirname(__FILE__) . '/../media/no-photo-available.jpg');

        header('Content-Type: image/jpeg');
        echo $photo['thumbnailphoto'];
        exit;
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws ValidationError
     * @throws \exceptions\LDAPException
     */
    private function updateUserImage(string $username):HTTPResponse
    {
        if(!empty($_FILES['thumbnailphoto']))
        {
            $imageContents = file_get_contents($_FILES['thumbnailphoto']['tmp_name']);

            // File has content
            if(strlen($imageContents) === 0)
                throw new ValidationError(array('Photo required'));

            // File is type jpeg or jpg
            if(strtolower($_FILES['thumbnailphoto']['type']) !== 'image/jpeg')
                throw new ValidationError(array('Photo must be a JPEG'));

            // Set LDAP user thumbnailphoto
            $ldap = new LDAPConnection();
            if($ldap->updateLDAPEntry($username, array('thumbnailphoto' => $imageContents)))
                return new HTTPResponse(HTTPResponse::NO_CONTENT);
            else
                throw new ValidationError(array('Could not change photo'));
        }

        throw new ValidationError(array('Photo required'));
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws ValidationError
     * @throws \exceptions\LDAPException
     */
    private function updateUser(string $username): HTTPResponse
    {
        $details = $this->request->body();

        if(NetUserOperator::updateUser($username, $details))
            return new HTTPResponse(HTTPResponse::NO_CONTENT);

        throw new ValidationError(array('User could not be updated'));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\LDAPException
     */
    private function searchUsers(): HTTPResponse
    {
        $results = NetUserOperator::searchUsers(self::getFormattedBody(NetUserOperator::SEARCH_ATTRIBUTES, TRUE));

        $users = array();

        for($i = 0; $i < $results['count']; $i++)
        {
            $user = array();

            foreach(array_keys($results[$i]) as $attr)
            {
                if(is_numeric($attr)) // Skip integer indexes
                    continue;

                if(is_array($results[$i][$attr])) // Attribute has details
                {
                    if((int)$results[$i][$attr]['count'] == 1) // Only one detail in this attribute
                        $user[$attr] = $results[$i][$attr][0];
                    else // Many details in this attribute
                    {
                        $subData = array();
                        for($j = 0; $j < (int)$results[$i][$attr]['count']; $j++)
                        {
                            $subData[] = $results[$i][$attr][$j];
                        }

                        $user[$attr] = $subData;
                    }
                }
                else
                {
                    $user[$attr] = ''; // No attribute data, leave blank
                }
            }

            $users[] = $user;
        }

        return new HTTPResponse(HTTPResponse::OK, $users);
    }
}