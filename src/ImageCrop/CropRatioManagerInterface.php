<?php

declare(strict_types=1);

namespace Aropixel\SyliusAdminMediaPlugin\ImageCrop;


interface CropRatioManagerInterface
{
    public function getRatio(string $type): ?float;

    public function getEntityCrops($imageClass): array;
}
