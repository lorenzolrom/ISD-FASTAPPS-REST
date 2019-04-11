<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:06 PM
 */


namespace controllers;


use business\SecretOperator;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\RouteException;
use exceptions\SecurityException;
use factories\ControllerFactory;
use models\HTTPRequest;
use models\HTTPResponse;
use models\Secret;

class FrontController
{
    /**
     * @return Secret
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    public static function currentSecret(): Secret
    {
        // Check for secret
        if(!isset($_SERVER['HTTP_SECRET']))
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::KEY_NOT_SUPPLIED], SecurityException::KEY_NOT_SUPPLIED);

        try
        {
            $key = SecretOperator::getSecret($_SERVER['HTTP_SECRET']);
            return $key;
        }
        catch(EntryNotFoundException $e)
        {
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::KEY_NOT_FOUND], SecurityException::KEY_NOT_FOUND, $e);
        }
    }

    public static function processRequest()
    {
        // Final response object
        $response = null;

        try
        {

            // Receive request
            switch($_SERVER['REQUEST_METHOD'])
            {
                case 'POST':
                    $method = HTTPRequest::POST;
                    break;
                case 'PUT':
                    $method = HTTPRequest::PUT;
                    break;
                case 'DELETE':
                    $method = HTTPRequest::DELETE;
                    break;
                default:
                    $method = HTTPRequest::GET;
            }

            // Get secret
            $key = self::currentSecret();

            // Determine URI
            if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on')
                $url = "https";
            else
                $url = "http";

            $url .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $urlParts = explode(\Config::OPTIONS['baseURL'] . \Config::OPTIONS['baseURI'], $url);

            if(isset($urlParts[1]))
                $uriParts = explode('/', $urlParts[1]);
            else
                $uriParts = array();

            // Create request
            $request = new HTTPRequest($key, $method, $uriParts, self::getRequestBodyAsArray());

            $response = ControllerFactory::getController($request)->getResponse();

            if($response == NULL)
                throw new RouteException(RouteException::MESSAGES[RouteException::REQUEST_INVALID], RouteException::REQUEST_INVALID);

        }
        catch(RouteException $e)
        {
            $response = new HTTPResponse(HTTPResponse::BAD_REQUEST, array('errors' => array($e->getMessage())));
        }
        catch(EntryNotFoundException $e)
        {
            $response = new HTTPResponse(HTTPResponse::NOT_FOUND, array('errors' => array($e->getMessage())));
        }
        catch(EntryInUseException $e)
        {
            $response = new HTTPResponse(HTTPResponse::CONFLICT, array('errors' => array($e->getMessage())));
        }
        catch(SecurityException $e)
        {
            if($e->getCode() == SecurityException::USER_NO_PERMISSION OR $e->getCode() == SecurityException::KEY_NO_PERMISSION)
                $response = new HTTPResponse(HTTPResponse::FORBIDDEN, array('errors' => array($e->getMessage())));
            else
                $response = new HTTPResponse(HTTPResponse::UNAUTHORIZED, array('errors' => array($e->getMessage())));
        }
        catch(\Exception $e)
        {
            $response = new HTTPResponse(HTTPResponse::INTERNAL_SERVER_ERROR, array('errors' => array($e->getMessage())));
        }

        // Reply to the request
        header('Content-type: application/vnd.api+json');

        // Request has been set
        if($response !== NULL)
        {
            http_response_code($response->getResponseCode());
            echo json_encode($response->getBody());
            exit;
        }

    }

    /**
     * Converts data sent in a request document to an array
     * @return array
     */
    public static function getRequestBodyAsArray(): array
    {
        $array = json_decode(file_get_contents('php://input'), TRUE);

        if(is_array($array))
            return $array;

        return [];
    }
}