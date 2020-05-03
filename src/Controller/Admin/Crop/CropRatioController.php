<?php

namespace Aropixel\SyliusAdminMediaPlugin\Controller\Admin\Crop;

use Aropixel\SyliusAdminMediaPlugin\ImageCrop\CropRatioManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CropRatioController extends AbstractController
{

    /**
     * @var CropRatioManagerInterface
     */
    private $cropRatioManager;

    public function __construct(CropRatioManagerInterface $cropRatioManager)
    {
        $this->cropRatioManager = $cropRatioManager;
    }

    public function getCropRatio(Request $request)
    {
        $type = $request->query->get('type');

        $cropRatio = $this->cropRatioManager->getRatio($type);

        $response = new JsonResponse(['ratio' => $cropRatio]);

        return $response;
    }

}
