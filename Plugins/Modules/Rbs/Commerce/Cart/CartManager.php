<?php
namespace Rbs\Commerce\Cart;

use Zend\Form\Annotation\AbstractArrayAnnotation;

/**
 * @name \Rbs\Commerce\Cart\CartManager
 */
class CartManager implements \Zend\EventManager\EventsCapableInterface
{
	use \Change\Events\EventsCapableTrait;

	const EVENT_MANAGER_IDENTIFIER = 'CartManager';

	/**
	 * @var \Rbs\Commerce\Services\CommerceServices
	 */
	protected $commerceServices;

	/**
	 * @param \Rbs\Commerce\Services\CommerceServices $commerceServices
	 */
	public function setCommerceServices(\Rbs\Commerce\Services\CommerceServices $commerceServices)
	{
		$this->commerceServices = $commerceServices;
		if ($this->sharedEventManager === null)
		{
			$this->setSharedEventManager($commerceServices->getApplicationServices()->getApplication()->getSharedEventManager());
		}
	}

	/**
	 * @return \Rbs\Commerce\Services\CommerceServices
	 */
	public function getCommerceServices()
	{
		return $this->commerceServices;
	}

	/**
	 * @return \Change\Documents\DocumentServices
	 */
	protected function getDocumentServices()
	{
		return $this->commerceServices->getDocumentServices();
	}

	/**
	 * @return \Change\Application\ApplicationServices
	 */
	protected function getApplicationServices()
	{
		return $this->commerceServices->getApplicationServices();
	}

	/**
	 * @return string
	 */
	protected function getEventManagerIdentifier()
	{
		return static::EVENT_MANAGER_IDENTIFIER;
	}

	/**
	 * @return string[]
	 */
	protected function getListenerAggregateClassNames()
	{
		$config = $this->getApplicationServices()->getApplication()->getConfiguration();
		$classNames = $config->getEntry('Change/Events/CartManager');
		return is_array($classNames) ? $classNames : array();
	}

	/**
	 * @param string $cartIdentifier
	 * @return \Rbs\Commerce\Interfaces\Cart|null
	 */
	public function getCartByIdentifier($cartIdentifier)
	{
		$em = $this->getEventManager();
		$args = $em->prepareArgs(array('cartIdentifier' => $cartIdentifier, 'commerceServices' => $this->getCommerceServices()));
		$this->getEventManager()->trigger('getCartByIdentifier', $this, $args);
		if (isset($args['cart']) && $args['cart'] instanceof \Rbs\Commerce\Interfaces\Cart)
		{
			return $args['cart'];
		}
		return null;
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\BillingArea $billingArea
	 * @param string $zone
	 * @param array $context
	 * @throws \RuntimeException
	 * @return \Rbs\Commerce\Interfaces\Cart
	 */
	public function getNewCart($billingArea = null, $zone = null, array $context = array())
	{
		$em = $this->getEventManager();
		$args = $em->prepareArgs(
			array('commerceServices' => $this->getCommerceServices(),
				'billingArea' => $billingArea, 'zone' => $zone, 'context' => $context));
		$this->getEventManager()->trigger('getNewCart', $this, $args);
		if (isset($args['cart']) && $args['cart'] instanceof \Rbs\Commerce\Interfaces\Cart)
		{
			return $args['cart'];
		}
		throw new \RuntimeException('Unable to get a new cart', 999999);
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 */
	public function saveCart(\Rbs\Commerce\Interfaces\Cart $cart)
	{
		if (!$cart->isLocked())
		{
			$em = $this->getEventManager();
			$args = $em->prepareArgs(array('cart' => $cart, 'commerceServices' => $this->getCommerceServices()));
			$this->getEventManager()->trigger('saveCart', $this, $args);
		}
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @param integer $ownerId
	 * @return bool
	 */
	public function lockCart(\Rbs\Commerce\Interfaces\Cart $cart, $ownerId)
	{
		if (!$cart->isLocked())
		{
			try
			{
				$em = $this->getEventManager();
				$args = $em->prepareArgs(array('cart' => $cart, 'ownerId' => $ownerId, 'commerceServices' => $this->getCommerceServices()));
				$this->getEventManager()->trigger('lockCart', $this, $args);
				return $cart->isLocked();
			}
			catch (\Exception $e)
			{
				$this->getApplicationServices()->getLogging()->exception($e);
			}
		}
		return false;
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @param string|\Rbs\Commerce\Interfaces\CartLineConfig|\Rbs\Commerce\Interfaces\CartLine $key
	 * @return \Rbs\Commerce\Interfaces\CartLine|null
	 */
	public function getLineByKey(\Rbs\Commerce\Interfaces\Cart $cart, $key)
	{
		if ($key instanceof \Rbs\Commerce\Interfaces\CartLine)
		{
			$lineKey = $key->getKey();
		}
		elseif ($key instanceof \Rbs\Commerce\Interfaces\CartLineConfig)
		{
			$lineKey = $key->getKey();
		}
		else
		{
			$lineKey = strval($key);
		}
		return $cart->getLineByKey($lineKey);
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @param \Rbs\Commerce\Interfaces\CartLineConfig $cartLineConfig
	 * @param float $quantity
	 * @throws \InvalidArgumentException
	 * @return \Rbs\Commerce\Interfaces\CartLine
	 */
	public function addLine(\Rbs\Commerce\Interfaces\Cart $cart, $cartLineConfig, $quantity = 1.0)
	{
		if ($cartLineConfig instanceof \Rbs\Commerce\Interfaces\CartLineConfig)
		{
			$line = $cart->getNewLine($cartLineConfig, $quantity);
			$cart->appendLine($line);
			$this->refreshCartLine($cart, $line);
			return $line;
		}
		else
		{
			throw new \InvalidArgumentException('Argument 2 should be a CartLineConfig', 999999);
		}
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @param string|\Rbs\Commerce\Interfaces\CartLineConfig|\Rbs\Commerce\Interfaces\CartLine $lineKey
	 * @param float $newQuantity
	 * @throws \RuntimeException
	 * @return \Rbs\Commerce\Interfaces\CartLine
	 */
	public function updateLineQuantityByKey(\Rbs\Commerce\Interfaces\Cart $cart, $lineKey, $newQuantity)
	{
		if ($lineKey instanceof \Rbs\Commerce\Interfaces\CartLine)
		{
			$lineKey = $lineKey->getKey();
		}
		elseif ($lineKey instanceof \Rbs\Commerce\Interfaces\CartLineConfig)
		{
			$lineKey = $lineKey->getKey();
		}
		else
		{
			$lineKey = strval($lineKey);
		}

		$line = $cart->updateLineQuantity($lineKey, $newQuantity);
		if ($line instanceof \Rbs\Commerce\Interfaces\CartLine)
		{
			$this->refreshCartLine($cart, $line);
			return $line;
		}
		else
		{
			throw new \RuntimeException('Cart line not found for key: ' . $lineKey , 999999);
		}
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @param string|\Rbs\Commerce\Interfaces\CartLineConfig|\Rbs\Commerce\Interfaces\CartLine $lineKey
	 * @param \Rbs\Commerce\Interfaces\CartLineConfig $cartLineConfig
	 * @return null|\Rbs\Commerce\Interfaces\CartLine
	 */
	public function updateLineByKey($cart, $lineKey, $cartLineConfig)
	{
		if ($lineKey instanceof \Rbs\Commerce\Interfaces\CartLine)
		{
			$lineKey = $lineKey->getKey();
		}
		elseif ($lineKey instanceof \Rbs\Commerce\Interfaces\CartLineConfig)
		{
			$lineKey = $lineKey->getKey();
		}
		else
		{
			$lineKey = strval($lineKey);
		}

		$line = $cart->getLineByKey($lineKey);
		if ($line && !$cart->getLineByKey($cartLineConfig->getKey()))
		{
			$newLine = $cart->getNewLine($cartLineConfig, $line->getQuantity());
			$cart->insertLineAt($newLine, $line->getNumber());
			$cart->removeLineByKey($lineKey);
			return $newLine;
		}
		return null;
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @param string|\Rbs\Commerce\Interfaces\CartLineConfig|\Rbs\Commerce\Interfaces\CartLine $key
	 * @throws \RuntimeException
	 * @return \Rbs\Commerce\Interfaces\CartLine
	 */
	public function removeLineByKey(\Rbs\Commerce\Interfaces\Cart $cart, $key)
	{
		if ($key instanceof \Rbs\Commerce\Interfaces\CartLine)
		{
			$lineKey = $key->getKey();
		}
		elseif ($key instanceof \Rbs\Commerce\Interfaces\CartLineConfig)
		{
			$lineKey = $key->getKey();
		}
		else
		{
			$lineKey = strval($key);
		}

		$line = $cart->removeLineByKey($lineKey);
		if ($line instanceof \Rbs\Commerce\Interfaces\CartLine)
		{
			return $line;
		}
		else
		{
			throw new \RuntimeException('Cart line not found for key: ' . $lineKey , 999999);
		}
	}

	protected function refreshCartLine(\Rbs\Commerce\Interfaces\Cart $cart, \Rbs\Commerce\Interfaces\CartLine $line)
	{
		$lineWebStoreId = $line->getOptions()->get('webStoreId', $cart->getWebStoreId());
		foreach ($line->getItems() as $item)
		{
			$sku = $this->commerceServices->getStockManager()->getSkuByCode($item->getCodeSKU());
			if ($sku)
			{
				$options = array('quantity' => $item->getReservationQuantity() * $line->getQuantity());
				$webStoreId = $item->getOptions()->get('webStoreId', $lineWebStoreId);
				$price = $this->commerceServices->getPriceManager()->getPriceBySku($sku, $webStoreId, $options, $cart->getBillingArea());
				if ($price)
				{
					$priceValue = $price->getValue();
					$cart->updateItemPrice($item, $priceValue);
					$taxApplicationArray = $this->commerceServices->getTaxManager()->getTaxByValue($priceValue, $price->getTaxCategories(), $cart->getBillingArea(), $cart->getZone());
					$cart->updateItemTaxes($item, $taxApplicationArray);
				}
			}
		}
	}

	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @return \Rbs\Commerce\Cart\CartReservation[]
	 */
	public function getReservations(\Rbs\Commerce\Interfaces\Cart $cart)
	{
		/* @var $cartReservations \Rbs\Commerce\Cart\CartReservation[] */
		$cartReservations = array();
		$cartWebStoreId = $cart->getWebStoreId();
		foreach ($cart->getLines() as $line)
		{
			$lineQuantity = $line->getQuantity();
			if ($lineQuantity)
			{
				$lineWebStoreId = $line->getOptions()->get('webStoreId', $cartWebStoreId);
				foreach ($line->getItems() as $item)
				{
					if ($item->getReservationQuantity())
					{
						$webStoreId = $item->getOptions()->get('webStoreId', $lineWebStoreId);
						$codeSKU = $item->getCodeSKU();
						$key = $codeSKU . '/' . $webStoreId;
						$resQtt = $lineQuantity * $item->getReservationQuantity();
						if (isset($cartReservations[$key]))
						{
							$reservation = $cartReservations[$key];
							$reservation->addQuantity($resQtt);
						}
						else
						{
							$reservation = new \Rbs\Commerce\Cart\CartReservation($cart->getIdentifier(), $codeSKU);
							$cartReservations[$key] = $reservation->setWebStoreId($webStoreId)->setQuantity($resQtt);
						}
					}
				}
			}
		}
		return array_values($cartReservations);
	}
}