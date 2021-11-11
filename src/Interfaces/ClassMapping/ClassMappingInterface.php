<?php

namespace OxidSolutionCatalysts\Unzer\Interfaces\ClassMapping;


use OxidSolutionCatalysts\Unzer\Model\Payments\Invoice_unsecured;

/**
 * Interface ConstantInterface
 */
interface ClassMappingInterface
{
    const UNZERCLASSNAMEMAPPING = [
        'oscunzer_invoice' => Invoice_unsecured::class
    ];
}
