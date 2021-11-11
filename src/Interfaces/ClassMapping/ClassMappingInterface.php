<?php

namespace OxidSolutionCatalysts\Unzer\Interfaces\ClassMapping;

use OxidSolutionCatalysts\Unzer\Model\Payments\InvoiceUnsecured;

/**
 * Interface ConstantInterface
 */
interface ClassMappingInterface
{
    const UNZERCLASSNAMEMAPPING = [
        'oscunzer_invoice' => InvoiceUnsecured::class
    ];
}