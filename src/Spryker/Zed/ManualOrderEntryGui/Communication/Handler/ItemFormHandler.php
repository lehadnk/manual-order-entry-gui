<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Handler;

use ArrayObject;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCartFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMessengerFacadeInterface;
use Symfony\Component\HttpFoundation\Request;

class ItemFormHandler implements FormHandlerInterface
{
    /**
     * @var \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCartFacadeInterface
     */
    protected $cartFacade;

    /**
     * @var \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMessengerFacadeInterface
     */
    protected $messengerFacade;

    /**
     * @param \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCartFacadeInterface $cartFacade
     * @param \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMessengerFacadeInterface $messengerFacade
     */
    public function __construct(
        ManualOrderEntryGuiToCartFacadeInterface $cartFacade,
        ManualOrderEntryGuiToMessengerFacadeInterface $messengerFacade
    ) {
        $this->cartFacade = $cartFacade;
        $this->messengerFacade = $messengerFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function handle(QuoteTransfer $quoteTransfer, &$form, Request $request): QuoteTransfer
    {
        $items = new ArrayObject();
        $addedSkus = [];

        $this->appendItemsFromManualOrderEntryItems($quoteTransfer, $addedSkus, $items);

        $this->appendItemsFromQuoteItems($quoteTransfer, $items);

        $quoteTransfer->setItems($items);

        if (count($items)) {
            $quoteTransfer = $this->cartFacade->reloadItems($quoteTransfer);
        }

        $this->updateItems($quoteTransfer);

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    protected function updateItems(QuoteTransfer $quoteTransfer)
    {
        $quoteTransfer->getManualOrder()->setItems(new ArrayObject());

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $newItemTransfer = new ItemTransfer();
            $newItemTransfer->setSku($itemTransfer->getSku())
                ->setQuantity($itemTransfer->getQuantity())
                ->setUnitGrossPrice($itemTransfer->getUnitGrossPrice());

            $quoteTransfer->getManualOrder()->addItems($newItemTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param array $addedSkus
     * @param \ArrayObject $items
     *
     * @return void
     */
    protected function appendItemsFromManualOrderEntryItems(QuoteTransfer $quoteTransfer, $addedSkus, $items): void
    {
        foreach ($quoteTransfer->getManualOrder()->getItems() as $newItemTransfer) {
            if ($newItemTransfer->getQuantity() <= 0
                || in_array($newItemTransfer->getSku(), $addedSkus)
            ) {
                continue;
            }

            $addedSkus[] = $newItemTransfer->getSku();
            $itemTransfer = new ItemTransfer();
            $itemTransfer->fromArray($newItemTransfer->toArray());

            $items->append($itemTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \ArrayObject $items
     *
     * @return void
     */
    protected function appendItemsFromQuoteItems(QuoteTransfer $quoteTransfer, $items): void
    {
        foreach ($quoteTransfer->getItems() as $quoteItemTransfer) {
            $skuAdded = false;
            foreach ($items as $itemTransfer) {
                if ($itemTransfer->getSku() === $quoteItemTransfer->getSku()) {
                    $skuAdded = true;

                    break;
                }
            }

            if (!$skuAdded) {
                $items->append($quoteItemTransfer);
            }
        }
    }
}