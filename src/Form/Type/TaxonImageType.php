<?php

declare(strict_types=1);

namespace Aropixel\SyliusAdminMediaPlugin\Form\Type;

use App\Entity\Taxonomy\TaxonImage;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaxonImageType extends ImageType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => TaxonImage::class,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_taxon_image';
    }
}
