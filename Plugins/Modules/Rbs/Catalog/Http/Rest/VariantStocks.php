<?php
namespace Rbs\Catalog\Http\Rest;

use Rbs\Stock\Services\StockManager;
use Zend\Http\Response;

/**
 * @name \Rbs\Catalog\Http\Rest\GetVariantStocks
 */
class VariantStocks
{

	public function getVariantStocks(\Change\Http\Event $event)
	{

		$request = $event->getRequest();
		$variantGroupId = $request->getQuery('variantGroupId');

		$variantGroup = $event->getApplicationServices()->getDocumentManager()->getDocumentInstance($variantGroupId);

		if ($variantGroup instanceof \Rbs\Catalog\Documents\VariantGroup)
		{
			$result = new \Change\Http\Rest\Result\ArrayResult();

			$query = $event->getApplicationServices()->getDocumentManager()->getNewQuery('Rbs_Catalog_Product');
			$query->andPredicates($query->eq('variant', true), $query->eq('variantGroup', $variantGroup), $query->isNotNull('sku'));

			$resultArray = array();

			$warehousesArray = array();
			$productsArray = array();

			$defaultWarehouseCode = $event->getApplicationServices()->getI18nManager()->trans('m.rbs.stock.admin.warehouse_default_label', ['ucf']);

			$collection = $query->getDocuments();
			foreach ($collection as $document)
			{
				/* @var $document \Rbs\Catalog\Documents\Product */
				$sku = $document->getSku();

				$productsArray[$document->getId()] = ['label' => $document->getLabel()];
				$productsArray[$document->getId()]['sku'] = ['id' => $sku->getId(), 'label' => $sku->getLabel()];

				$inventoryQuery = $event->getApplicationServices()->getDocumentManager()->getNewQuery('Rbs_Stock_InventoryEntry');
				$inventoryQuery->andPredicates($inventoryQuery->eq('sku', $sku));

				$inventoryCollection = $inventoryQuery->getDocuments();

				$event->getApplicationServices()->getLogging()->fatal(var_export($inventoryCollection->count(), true));

				if ($inventoryCollection->count() > 0)
				{
					foreach ($inventoryCollection as $inventory)
					{
						/* @var $inventory \Rbs\Stock\Documents\InventoryEntry */
						$warehouse = $inventory->getWarehouse();

						$warehouseId = -1;
						$warehouseCode = $defaultWarehouseCode;
						if ($warehouse instanceof \Rbs\Stock\Documents\AbstractWarehouse)
						{
							$warehouseId = $warehouse->getId();
							$warehouseCode = $warehouse->getCode();
						}

						if (!array_key_exists($warehouseId, $warehousesArray))
						{
							$warehousesArray[$warehouseId] = ['code' => $warehouseCode, 'skus' => array()];
						}
						$warehousesArray[$warehouseId]['skus'][$sku->getId()] = $inventory->getLevel();
					}
				}
				else
				{
					if (!array_key_exists(-1, $warehousesArray))
					{
						$warehousesArray[-1] = ['code' => $defaultWarehouseCode, 'skus' => array()];
					}
				}

			}

			$resultArray['warehouses'] = $warehousesArray;
			$resultArray['products'] = $productsArray;

			$result->setArray($resultArray);

			$result->setHttpStatusCode(Response::STATUS_CODE_200);

		}
		else
		{
			$result = new \Change\Http\Rest\Result\ErrorResult(999999, 'Bad variant group id', \Zend\Http\Response::STATUS_CODE_409);
		}

		$event->setResult($result);
	}

	public function saveVariantStocks(\Change\Http\Event $event)
	{
		$request = $event->getRequest();
		$stocks = $request->getPost('stocks');

		/* @var $commerceServices \Rbs\Commerce\CommerceServices */
		$commerceServices = $event->getServices('commerceServices');

		/* @var $stockManager \Rbs\Stock\Services\StockManager */
		$stockManager = $commerceServices->getStockManager();
		/* @var $documentManger \Change\Documents\DocumentManager */
		$documentManger = $event->getApplicationServices()->getDocumentManager();
		$warehouseIds = array_keys($stocks);

		foreach ($warehouseIds as $warehouseId)
		{

			if ($warehouseId === -1)
			{
				$warehouse = null;
			}
			else
			{
				/* @var $warehouse \Rbs\Stock\Documents\AbstractWarehouse */
				$warehouse = $documentManger->getDocumentInstance($warehouseId);
			}

			foreach($stocks[$warehouseId]['skus'] as $skuId => $stock)
			{
				/* @var $sku \Rbs\Stock\Documents\Sku */
				$sku = $documentManger->getDocumentInstance($skuId, 'Rbs_Stock_Sku');
				$stockManager->setInventory($stock, $sku, $warehouse);
			}

		}

		$result = new \Change\Http\Result();
		$result->setHttpStatusCode(Response::STATUS_CODE_200);

		$event->setResult($result);
	}
}