<?php

namespace Aropixel\SyliusAdminMediaPlugin\Controller\Admin\FileManager;

use Aropixel\SyliusAdminMediaPlugin\FileManager\Helpers\UploadHandler;
use Artgris\Bundle\FileManagerBundle\Controller\ManagerController as BaseManagerController;
use Artgris\Bundle\FileManagerBundle\Event\FileManagerEvents;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ManagerController extends BaseManagerController
{

    // surcharge du controleur original car la classe UploadHandler (surchargée) est instanciée
    // à la main ici
    public function uploadFileAction(Request $request)
    {
        $fileManager = $this->newFileManager($request->query->all());

        $options = [
            'upload_dir' => $fileManager->getCurrentPath().DIRECTORY_SEPARATOR,
            'upload_url' => $fileManager->getImagePath(),
            'accept_file_types' => $fileManager->getRegex(),
            'print_response' => false,
        ];
        if (isset($fileManager->getConfiguration()['upload'])) {
            $options += $fileManager->getConfiguration()['upload'];
        }

        $this->dispatch(FileManagerEvents::PRE_UPDATE, ['options' => &$options]);

        $uploadHandler = new UploadHandler($options);
        $response = $uploadHandler->response;

        foreach ($response['files'] as $file) {
            if (isset($file->error)) {
                $file->error = $this->get('translator')->trans($file->error);
            } else {
                //if (!$fileManager->getImagePath()) {
                //    $file->url = $this->generateUrl('file_manager_file', array_merge($fileManager->getQueryParameters(), ['fileName' => $file->url]));
                //}

                $file->url = $file->name;
            }


        }

        $this->dispatch(FileManagerEvents::POST_UPDATE, ['response' => &$response]);

        return new JsonResponse($response);
    }

    // méthode surchargée car en privé dans le controleur de base
    private function newFileManager($queryParameters)
    {
        if (!isset($queryParameters['conf'])) {
            throw new \RuntimeException('Please define a conf parameter in your route');
        }
        $webDir = $this->getParameter('artgris_file_manager')['web_dir'];

        $this->fileManager = new FileManager($queryParameters, $this->get('artgris_bundle_file_manager.service.filemanager_service')->getBasePath($queryParameters), $this->getKernelRoute(), $this->get('router'), $webDir);

        return $this->fileManager;
    }

    // méthode surchargée car en privé dans le controleur de base
    private function getKernelRoute()
    {
        return $this->getParameter('kernel.root_dir');
    }
}
