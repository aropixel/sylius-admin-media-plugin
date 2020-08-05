<?php

declare(strict_types=1);

namespace Aropixel\SyliusAdminMediaPlugin\Form\Type;

use Aropixel\SyliusAdminMediaPlugin\ImageCrop\CropRatioManager;
use Aropixel\SyliusAdminMediaPlugin\ImageCrop\CropRatioManagerInterface;
use Artgris\Bundle\MediaBundle\Form\Type\MediaType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

abstract class ImageType extends AbstractResourceType
{
    /** @var CropRatioManagerInterface $cropRatioManager */
    private $cropRatioManager;

    public function __construct(CropRatioManagerInterface $cropRatioManager, string $dataClass, array $validationGroups = [])
    {
        parent::__construct( $dataClass, $validationGroups );
        $this->cropRatioManager = $cropRatioManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // en fonction du type de crop choisi, on défini le ratio du crop de l'image
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder){

                $form = $event->getForm();
                $entityCrops = $this->getEntityCrops($form);

                // si des crops sont définis en config,
                // on affiche le champs type avec les crops possibles
                if (!empty($entityCrops)) {

                    $form->add('type', ChoiceType::class, [
                        'label' => 'Format',
                        'required' => false,
                        'choices'  => $entityCrops
                    ]);

                }

                $form->add('path', MediaType::class, [
                    'conf' => 'default',
                    'crop_options' => [
                        'display_crop_data' => false,
                        'allow_flip' => true,
                        'allow_rotation' => true,
                        'ratio' => $this->getRatio($event)
                    ]
                ]);

            }
        );
    }

    private function getEntityCrops(FormInterface $form): array
    {
        // la class reliée au form
        $imageClass = $form->getConfig()->getDataClass();

        $entityCrops = $this->cropRatioManager->getEntityCrops($imageClass);

        return $entityCrops;
    }

    private function getRatio(FormEvent $event)
    {
        // l'image reliée au form (vide ou déjà persistée en bdd si c'est un update)
        $image = $event->getData();

        $ratio = false;

        if (!empty($image) && $image->getId()) {
            $ratio = $this->cropRatioManager->getRatio($image->getType());
        }

        return $ratio;
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'aropixel_media_image';
    }


}
