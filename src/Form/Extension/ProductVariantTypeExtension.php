<?php

declare(strict_types=1);

namespace Aropixel\SyliusAdminMediaPlugin\Form\Extension;

use App\Form\Type\ProductVariantContainerType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductVariantTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productVariantContainers', CollectionType::class, [
                'entry_type' => ProductVariantContainerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductVariantType::class];
    }
}
