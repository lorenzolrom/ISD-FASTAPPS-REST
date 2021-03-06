<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 7:17 AM
 */


namespace extensions\facilities\controllers;


use extensions\facilities\business\BuildingOperator;
use extensions\facilities\business\LocationOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class LocationController extends Controller
{
    private const FIELDS = array('code', 'name');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-r', 'itsm_inventory-assets-w'));

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            return $this->getLocation($param);
        }
        else if($this->request->method() == HTTPRequest::DELETE)
        {
            return $this->deleteLocation($param);
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            return $this->updateLocation($param);
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            return $this->createLocation($param); // Param here is Building ID
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getLocation(?string $param): HTTPResponse
    {
        $location = LocationOperator::getLocation((int)$param);
        $building = BuildingOperator::getBuilding($location->getBuilding());

        $data = array(
            'id' => $location->getId(),
            'buildingId' => $building->getId(),
            'buildingCode' => $building->getCode(),
            'buildingName' => $building->getName(),
            'code' => $location->getCode(),
            'name' => $location->getName()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    private function deleteLocation(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-w'));

        LocationOperator::deleteLocation(LocationOperator::getLocation((int) $param));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createLocation(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-w'));

        $building = BuildingOperator::getBuilding((int) $param);

        $fields = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = LocationOperator::createLocation($building, $fields['code'], $fields['name']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateLocation(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-w'));

        $location = LocationOperator::getLocation((int) $param);

        $fields = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = LocationOperator::updateLocation($location, $fields['code'], $fields['name']);

        if(!isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::NO_CONTENT);

        return new HTTPResponse(HTTPResponse::CONFLICT, $errors);
    }
}
