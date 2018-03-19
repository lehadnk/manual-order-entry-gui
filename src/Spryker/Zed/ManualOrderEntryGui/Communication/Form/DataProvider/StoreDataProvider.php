<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Store\StoreType;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCurrencyFacadeInterface;
use Symfony\Component\HttpFoundation\Request;

class StoreDataProvider
{

    /**
     * @var \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCurrencyFacadeInterface
     */
    protected $currencyFacade;

    /**
     * @param \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCurrencyFacadeInterface $currencyFacade
     */
    public function __construct(
        ManualOrderEntryGuiToCurrencyFacadeInterface $currencyFacade
    ) {
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return array
     *
     * @throws \Spryker\Zed\Currency\Business\Model\Exception\CurrencyNotFoundException
     */
    public function getOptions()
    {
        return [
            'data_class' => QuoteTransfer::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            StoreType::OPTION_STORES_ARRAY => $this->getStoreList(),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getData(QuoteTransfer $quoteTransfer)
    {
        return $quoteTransfer;
    }

    /**
     * @return array
     *
     * @throws \Spryker\Zed\Currency\Business\Model\Exception\CurrencyNotFoundException
     */
    protected function getStoreList()
    {
        $storeWithCurrencyTransfers = $this->currencyFacade->getAllStoresWithCurrencies();
        $storeList = [];

        foreach ($storeWithCurrencyTransfers as $storeWithCurrencyTransfer) {
            $storeTransfer = $storeWithCurrencyTransfer->getStore();

            foreach ($storeWithCurrencyTransfer->getCurrencies() as $currencyTransfer) {
                $row = $storeTransfer->getName()
                    . ' - '
                    . $currencyTransfer->getName()
                    . ' [' . $currencyTransfer->getCode() . ']';

                $storeList[$storeTransfer->getIdStore() . ';' . $currencyTransfer->getIdCurrency()] = $row;
            }
        }

        return $storeList;
    }

}