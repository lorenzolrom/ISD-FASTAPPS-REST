<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/11/2019
 * Time: 10:15 AM
 */


namespace controllers\itsm;


use business\AttributeOperator;
use business\facilities\LocationOperator;
use business\itsm\AssetOperator;
use business\itsm\CommodityOperator;
use business\itsm\PurchaseOrderOperator;
use business\itsm\WarehouseOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class AssetController extends Controller
{
    const SEARCH_FIELDS = array('assetTag', 'serialNumber', 'inWarehouse', 'isDiscarded', 'buildingCode',
        'locationCode', 'warehouseCode', 'purchaseOrder', 'manufacturer', 'model', 'commodityCode', 'commodityName',
        'commodityType', 'assetType', 'isVerified');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-assets-r');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResult();
                default:
                    return $this->getAsset($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
            }
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getAsset(?string $param): HTTPResponse
    {
        $asset = AssetOperator::getAsset((int)$param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'assetTag' => $asset->getAssetTag(),
            'commodity' => $asset->getCommodity(),
            'warehouse' => $asset->getWarehouse(),
            'warehouseCode' => WarehouseOperator::codeFromId($asset->getWarehouse()),
            'parent' => $asset->getParent(), // TODO: convert to asset tag
            'location' => $asset->getLocation(), // TODO: also include building code and location code
            'serialNumber' => $asset->getSerialNumber(),
            'manufactureDate' => $asset->getManufactureDate(),
            'purchaseOrder' => PurchaseOrderOperator::numberFromId($asset->getPurchaseOrder()),
            'notes' => $asset->getNotes(),
            'createDate' => $asset->getCreateDate(),
            'discarded' => $asset->getDiscarded(),
            'discardDate' => $asset->getDiscardDate(),
            'lastModifyDate' => $asset->getLastModifyDate(),
            'lastModifyUser' => UserOperator::usernameFromId($asset->getLastModifyUser()),
            'verified' => $asset->getVerified(),
            'verifyDate' => $asset->getVerifyDate(),
            'verifyUser' => ($asset->getVerifyUser() !== NULL) ? UserOperator::usernameFromId($asset->getVerifyUser()) : NULL
        ));
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    private function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $assets = AssetOperator::search($args['assetTag'], $args['serialNumber'], $args['inWarehouse'],
                $args['isDiscarded'], $args['buildingCode'], $args['locationCode'], $args['warehouseCode'],
                $args['purchaseOrder'], $args['manufacturer'], $args['model'], $args['commodityCode'],
                $args['commodityName'], $args['commodityType'], $args['assetType'], $args['isVerified']);
        }
        else
        {
            $assets = AssetOperator::search();
        }

        $data = array();

        foreach($assets as $asset)
        {
            $commodity = CommodityOperator::getCommodity($asset->getCommodity());

            $data[] = array(
                'inWorksheet' => '', // TODO: check if asset is in worksheet
                'assetTag' => $asset->getAssetTag(),
                'commodityCode' => $commodity->getCode(),
                'commodityName' => $commodity->getName(),
                'assetType' => AttributeOperator::nameFromId($commodity->getAssetType()),
                'serialNumber' => $asset->getSerialNumber(),
                'location' => LocationOperator::getFullLocationCode($asset->getLocation()),
                'warehouse' => WarehouseOperator::codeFromId($asset->getWarehouse()),
                'verified' => $asset->getVerified(),
                'returnOrderNumber' => '' // TODO: check for current return order
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}