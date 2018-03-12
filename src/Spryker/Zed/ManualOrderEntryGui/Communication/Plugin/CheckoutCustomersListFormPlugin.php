<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Plugin;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @method \Spryker\Zed\ManualOrderEntryGui\Communication\ManualOrderEntryGuiCommunicationFactory getFactory()
 */
class CheckoutCustomersListFormPlugin extends AbstractPlugin implements CheckoutFormPluginInterface
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return void
     *
     * @throws \Spryker\Zed\Kernel\Exception\Container\ContainerKeyNotFoundException
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $formType = $this->getFactory()
            ->createCustomersListType();

        $dataProvider = $this->getFactory()
            ->createCustomersListDataProvider();

        $quoteTransfer = $builder->getData();
        $dataProvider->getData($quoteTransfer);

        $formType->buildForm(
            $builder,
            $dataProvider->getOptions()
        );
    }

}
