<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Dependency\Facade;

interface ManualOrderEntryGuiToStoreFacadeInterface
{
    /**
     * @return array<string>
     */
    public function getCountries();
}
