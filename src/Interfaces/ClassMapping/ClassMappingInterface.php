<?php

namespace OxidSolutionCatalysts\Unzer\Interfaces\ClassMapping;

use OxidSolutionCatalysts\Unzer\Model\Payment\Invoice_unsecured;

/**
 * Interface ConstantInterface
 */
interface ClassMappingInterface
{
    const UNZERCLASSNAMEMAPPING = [
        'oscunzer_invoice' => Invoice_unsecured::class
    ];
}
