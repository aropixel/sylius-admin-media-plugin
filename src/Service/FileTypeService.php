<?php

namespace Aropixel\SyliusAdminMediaPlugin\Service;

use Artgris\Bundle\FileManagerBundle\Service\FileTypeService as BaseFileTypeService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

class FileTypeService extends BaseFileTypeService
{

    /**
     * @var RouterInterface
     */
    private $router;

    private $params;


    /**
     * FileTypeService constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router, ParameterBagInterface $params)
    {
        parent::__construct($router);
        $this->params = $params;
    }


    public function fileIcon($filePath, $extension = null, $size = 75, $lazy = false)
    {
        if (null === $extension) {
            $filePathTmp = strtok($filePath, '?');
            $extension = pathinfo($filePathTmp, PATHINFO_EXTENSION);
        }
        switch (true) {
            case $this->isYoutubeVideo($filePath):
            case preg_match('/(mp4|ogg|webm|avi|wmv|mov)$/i', $extension):
                $fa = 'far fa-file-video';
                break;
            case preg_match('/(mp3|wav)$/i', $extension):
                $fa = 'far fa-file-audio';
                break;
            case preg_match('/(gif|png|jpe?g|svg)$/i', $extension):
                $query = parse_url($filePath, PHP_URL_QUERY);
                $time = 'time='.time();

                // get the public dir
                $publicDir = $this->params->get('kernel.project_dir').'/public';

                $fileManagerParams = $this->params->get('artgris_file_manager');
                $baseUrl = $fileManagerParams['conf']['default']['dir'];

                // remove the public dir from the absolute path
                $relativeBaseUrl = str_replace($publicDir,"", $baseUrl);

                $filePath = $relativeBaseUrl.'/'.$filePath;

                $fileName = $query ? $filePath.'&'.$time : $filePath.'?'.$time;

                if ($lazy) {
                    $html = "<img class=\"lazy\" data-src=\"{$fileName}\" height='{$size}'>";
                } else {
                    $html = "<img src=\"{$fileName}\" height='{$size}'>";
                }

                return [
                    'path' => $filePath,
                    'html' => $html,
                    'image' => true,
                ];
            case preg_match('/(pdf)$/i', $extension):
                $fa = 'far fa-file-pdf';
                break;
            case preg_match('/(docx?)$/i', $extension):
                $fa = 'far fa-file-word';
                break;
            case preg_match('/(xlsx?|csv)$/i', $extension):
                $fa = 'far fa-file-excel';
                break;
            case preg_match('/(pptx?)$/i', $extension):
                $fa = 'far fa-file-powerpoint';
                break;
            case preg_match('/(zip|rar|gz)$/i', $extension):
                $fa = 'far fa-file-archive';
                break;
            case filter_var($filePath, FILTER_VALIDATE_URL):
                $fa = 'fab fa-internet-explorer';
                break;
            default:
                $fa = 'far fa-file';
        }

        return [
            'path' => $filePath,
            'html' => "<i class='{$fa}' aria-hidden='true'></i>",
        ];
    }

}
