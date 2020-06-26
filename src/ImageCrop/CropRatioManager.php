<?php

declare(strict_types=1);

namespace Aropixel\SyliusAdminMediaPlugin\ImageCrop;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CropRatioManager implements CropRatioManagerInterface
{

    private const PARAM_ENTITIES_CROPS = "aropixel_sylius_admin_media.entities_crops";
    private const PARAM_DEFAULT_CROPS = "aropixel_sylius_admin_media.default_crops";
    private const PARAM_LIIP_FILTERS = "liip_imagine.filter_sets";

    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }



    public function getEntityCrops($imageClass): array
    {
        $entitiesCrops = $this->params->get(self::PARAM_ENTITIES_CROPS);

        $entityCrops = [];

        if (array_key_exists($imageClass, $entitiesCrops)) {
            $entityCrops = array_flip($entitiesCrops[$imageClass]);
        }

        $defaultCrops = array_flip($this->params->get(self::PARAM_DEFAULT_CROPS));

        // ajoute les crops définis par défaut aux crops de l'entité
        $entityCrops = array_merge($entityCrops, $defaultCrops);

        return $entityCrops;
    }

    public function getRatio($type): ?float
    {
        // Récupérer tous les crops dans la config sylius admin
        //$entityCrops = $this->getEntityCrops($entity);

        // Récupère les filtres liip
        $liipFilters = $this->params->get(self::PARAM_LIIP_FILTERS);


        // définit une valeur de ratio par défaut
        $ratio = null;

        // si le type crop existe dans les filters liip
        if (array_key_exists($type, $liipFilters)) {

            $liipFilter = $liipFilters[$type];
            // on calcule son ratio
            $ratio = $liipFilter['filters']['thumbnail']['size'][0] / $liipFilter['filters']['thumbnail']['size'][1];
        }

        return $ratio;
    }
}
