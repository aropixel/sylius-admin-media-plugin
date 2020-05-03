<?php

declare(strict_types=1);

namespace Aropixel\SyliusAdminMediaPlugin\Form\Type;


final class TaxonImageType extends ImageType
{

    public function getBlockPrefix(): string
    {
        return 'sylius_taxon_image';
    }
}
