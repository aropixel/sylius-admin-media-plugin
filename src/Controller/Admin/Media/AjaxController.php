<?php

namespace Aropixel\SyliusAdminMediaPlugin\Controller\Admin\Media;

use Artgris\Bundle\MediaBundle\Controller\AjaxController as BaseAjaxController;
use Gregwar\Image\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{

    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }


    public function ajaxCrop(Request $request)
    {
        $post = $request->request;
        $src = $post->get('src');
        $src = strtok($src, '?');
        $x = $post->getInt('x');
        $y = $post->getInt('y');
        $width = $post->getInt('width');
        $height = $post->getInt('height');
        $scaleX = $post->getInt('scaleX', 1);
        $scaleY = $post->getInt('scaleY', 1);
        $rotate = $post->getInt('rotate');
        $conf = $post->get('conf');

        $fileManager = $this->params->get('artgris_file_manager');

        $destinationFolder = null;
        if ($conf !== null) {
            $artgrisConf = $this->get('artgris_bundle_file_manager.service.filemanager_service')->getBasePath(['conf' => $conf]);
            $destinationFolder = $artgrisConf['dir'];
        }

        dump($destinationFolder);


        $flipX = $scaleX !== 1;
        $flipY = $scaleY !== 1;

        if ($flipX) {
            $rotate = -$rotate;
        }
        if ($flipY) {
            $rotate = -$rotate;
        }

        $pathinfo = pathinfo(parse_url($src, PHP_URL_PATH));
        $extension = $pathinfo['extension'];

        if ($src[0] === '/') {
            $src = urldecode($this->params->get('kernel.project_dir').'/'.$fileManager['web_dir'].$src);
        }

        if (!file_exists($src)) {
            return new JsonResponse('');
        }

        $image = Image::open($src)
            ->rotate(-$rotate)
            ->flip($flipY, $flipX)
            ->crop($x, $y, $width, $height);

        $savedPath = '/';

        if ($destinationFolder !== null) {

            if (substr($destinationFolder, -1) !== DIRECTORY_SEPARATOR) {
                $destinationFolder .= DIRECTORY_SEPARATOR;
            }

            $rootdir = $this->params->get('kernel.project_dir');

            //$baseUrl = $rootdir.$fileManager['conf']['default']['dir'];

            $baseUrl = $fileManager['conf']['default']['dir'];

            $cropStrAdd = '_crop_';
            $filename = $pathinfo['filename'];
            $cropPos = mb_strpos($filename, $cropStrAdd);
            if ($cropPos !== false) {
                $filename = mb_substr($filename, 0, $cropPos);
            }
            $croppedPath = $this->params->get('artgris_media')['cropped_path'];

            $uniqueFileName = urldecode($filename).$cropStrAdd.uniqid().'.'.$extension;

            $savedPath = $image->save($baseUrl.DIRECTORY_SEPARATOR.$croppedPath.$uniqueFileName, 'guess', 85);

            $savedPath = mb_substr($savedPath, mb_strlen($baseUrl));

            if ($savedPath[0] !== '/') {
                $savedPath = $src;
            }
        } else {
            if ($extension === 'png') {
                $savedPath .= $image->png();
            } else {
                $savedPath .= $image->jpeg();
            }
        }

        return new JsonResponse($savedPath);
    }

}
