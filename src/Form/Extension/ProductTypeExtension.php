<?php

namespace Aropixel\SyliusAdminMediaPlugin\Form\Extension;

use Aropixel\SyliusAdminMediaPlugin\Form\Type\AdminMediaCollectionType;
use Aropixel\SyliusAdminMediaPlugin\Form\Type\ProductImageType;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\CoreBundle\Form\Type\Taxon\ProductTaxonAutocompleteChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Sylius\Component\Core\Model\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductTypeExtension extends AbstractTypeExtension
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * surcharge de la totalité du product extension pour modifier l'entry type
     * des images (pour avoir le media type) car avec une autre extension
     * impossible de supprimer et de re-ajouter un champs déjà défini dans une extension du coeur
     * (même avec la priorité)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('channels', ChannelChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => 'sylius.form.product.channels',
            ])
            ->add('mainTaxon', TaxonAutocompleteChoiceType::class, [
                'label' => 'sylius.form.product.main_taxon',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $product = $event->getData();
                $form = $event->getForm();

                $form->add('productTaxons', ProductTaxonAutocompleteChoiceType::class, [
                    'label' => 'sylius.form.product.taxons',
                    'product' => $product,
                    'multiple' => true,
                ]);
            })
            ->add('variantSelectionMethod', ChoiceType::class, [
                'choices' => array_flip(Product::getVariantSelectionMethodLabels()),
                'label' => 'sylius.form.product.variant_selection_method',
            ])
            ->add('images', AdminMediaCollectionType::class, [
                'entry_type' => ProductImageType::class,
                'entry_options' => ['product' => $options['data']],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'sylius.form.product.images',
                'block_name' => 'entry',
            ])

        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }

}
