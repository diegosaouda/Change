<?php
namespace Rbs\Commerce\Interfaces;

/**
* @name \Rbs\Commerce\Interfaces\CartTax
*/
interface CartTax extends \Serializable
{
	/**
	 * @param \Rbs\Commerce\Interfaces\Cart $cart
	 * @return $this
	 */
	public function setCart($cart);

	/**
	 * @return \Rbs\Commerce\Interfaces\Cart
	 */
	public function getCart();

	/**
	 * @return Tax
	 */
	public function getTax();

	/**
	 * @return string
	 */
	public function getCategory();

	/**
	 * @return string
	 */
	public function getZone();

	/**
	 * @return float
	 */
	public function getRate();

	/**
	 * @return float
	 */
	public function getValue();

	/**
	 * @param \Rbs\Commerce\Interfaces\TaxApplication $taxApplication
	 * @return $this
	 */
	public function fromTaxApplication($taxApplication);
}