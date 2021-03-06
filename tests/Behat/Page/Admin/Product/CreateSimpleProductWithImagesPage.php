<?php

declare( strict_types=1 );

namespace Tests\Aropixel\SyliusAdminMediaPlugin\Behat\Page\Admin\Product;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Product\CreateSimpleProductPage;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

class CreateSimpleProductWithImagesPage extends CreateSimpleProductPage
{

    private const PARAM_ENTITIES_CROPS = "aropixel_sylius_admin_media.entities_crops";
    private const PARAM_DEFAULT_CROPS = "aropixel_sylius_admin_media.default_crops";
    private const PARAM_LIIP_FILTERS = "liip_imagine.filter_sets";

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(
        Session $session,
        $minkParameters,
        RouterInterface $router,
        string $routeName,
        ParameterBagInterface $params
    ) {
        parent::__construct(
            $session,
            $minkParameters,
            $router,
            $routeName
        );

        $this->params = $params;
    }

    public function isSpinnerVisible()
    {
        $spinner = $this->getElement('spinner');
        return $spinner->isVisible();
    }

    public function waitForAjaxUpload()
    {
        $this->getDocument()->waitFor(10000, function () {
            return $this->isSpinnerHidden();
        });
    }

    private function isSpinnerHidden()
    {
        $spinner = $this->getElement('spinner');
        return !$spinner->isVisible();
    }

    public function isImagePreviewVisible($path)
    {
        $this->waitForAjaxUpload();

        $imageForm = $this->getLastImageElement();

        $imageNode = $imageForm->find('css', '.crop-hover img');

        $imgUrl = $imageNode->getAttribute('src');

        $this->getDocument()->waitFor( 10000, function () use ( $imgUrl, $path ) {
            return (strpos($imgUrl, $path) !== false);
        } );

        //Assert::true(strpos($imgUrl, $path) !== false);

        return $this->isImageLinkBroken( $imgUrl );

    }

    private function getLastImageElement(): NodeElement
    {
        $images = $this->getElement('images');
        $items = $images->findAll('css', 'div[data-form-collection="item"]');

        Assert::notEmpty($items);

        return end($items);
    }


    /**
     * @param string|null $imgUrl
     *
     * @return bool
     */
    private function isImageLinkBroken( ?string $imgUrl ): bool
    {
        $baseUrl = $this->getParameter( 'base_url' );

        $this->getSession()->visit( $baseUrl . '/' . $imgUrl );

        $pageText = $this->getDocument()->getText();

        $this->getSession()->back();

        // si le 404 n'a pas été trouvé, retourne true
        return ( strpos( $pageText, '404 Not Found' ) === false );
    }

    public function addImageItem()
    {
        $this->clickTabIfItsNotActive('media');

        $filesPath = $this->getParameter('files_path');

        $this->getDocument()->clickLink('Add');

        $imageForm = $this->getLastImageElement();
    }

    private function clickTabIfItsNotActive(string $tabName): void
    {
        $attributesTab = $this->getElement('tab', ['%name%' => $tabName]);
        if (!$attributesTab->hasClass('active')) {
            $attributesTab->click();
        }
    }

    public function getDefaultCrops()
    {
        $defaultsCrops = $this->params->get(self::PARAM_DEFAULT_CROPS);

        return $defaultsCrops;
    }

    public function getProductImageCrops()
    {
        $entitiesCrops = $this->params->get(self::PARAM_ENTITIES_CROPS);

        $productImageCrops = $entitiesCrops['Sylius\Component\Core\Model\ProductImage'];

        return $productImageCrops;
    }

    public function getTypeOptions()
    {
        $typeInput = $this->getTypeInput();
        return $typeInput->getText();
    }

    private function getTypeInput()
    {
        $imageForm = $this->getLastImageElement();
        return $imageForm->find('css', '.js-admin-media-type');
    }

    public function selectImageType($type)
    {
        $this->waitForAjaxUpload();

        $typeInput = $this->getTypeInput();
        $typeInput->selectOption($type);
    }

    public function isCroppingFree()
    {
        $cropStyles = $this->cropImage();

        $widthBeforeDrag = $this->buildArrayFromStyleString( $cropStyles['before'] )['width'];
        $widthAfterDrag  = $this->buildArrayFromStyleString( $cropStyles['after'] )['width'];

        return ($widthBeforeDrag === $widthAfterDrag);
    }

    public function isCroppingSquare()
    {
        $cropStyles = $this->cropImage();

        $widthBeforeDrag = $this->buildArrayFromStyleString( $cropStyles['before'] )['width'];
        $heightBeforeDrag = $this->buildArrayFromStyleString( $cropStyles['before'] )['height'];

        // check that the crop tool before resize it is square
        Assert::true($widthBeforeDrag === $heightBeforeDrag);

        $widthAfterDrag  = $this->buildArrayFromStyleString( $cropStyles['after'] )['width'];
        $heightAfterDrag = $this->buildArrayFromStyleString( $cropStyles['after'] )['height'];

        // check that the crop tool after resize it is square
        return $widthAfterDrag === $heightAfterDrag;
    }

    private function buildArrayFromStyleString(string $style)
    {
        $dictionary = [];

        if (empty($style)) {
            return $dictionary;
        }

        foreach (explode(';', rtrim($style, ';, ')) as $css) {
            $parts = explode(':', $css);
            $dictionary[trim($parts[0])] = trim($parts[1]);
        }

        return $dictionary;
    }



    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'spinner' => '.spinner-container',
        ]);
    }

    private function getCropModal()
    {
        $imageForm = $this->getLastImageElement();

        $triggerCropModal = $imageForm->find( 'css', '.js-crop' );

        $triggerCropModal->click();

        $cropModal = $imageForm->find( 'css', '.artgris-media-crop-modal' );

        $this->getDocument()->waitFor( 10000, function () use ( $cropModal ) {
            return $cropModal->isVisible();
        } );

        return $cropModal;
    }

    /**
     * @return array
     */
    private function cropImage(): array
    {

        $this->waitForAjaxUpload();

        $cropModal = $this->getCropModal();

        $this->getDocument()->waitFor( 10000, function () use ( $cropModal ){
            return ( ! is_null( $cropModal->find( 'css', '.point-nw' ) ) );
        } );

        // find the crop box which contain the width and height
        $cropperBoxInitialStyle = $cropModal->find( 'css', '.cropper-crop-box' )->getAttribute( 'style' );

        $pointNWCropDrag = $cropModal->find( 'css', '.point-nw' );
        $pointWCropDrag  = $cropModal->find( 'css', '.point-w' );

        $pointNWCropDrag->dragTo( $pointWCropDrag );

        $cropperBoxResizedStyle = $cropModal->find( 'css', '.cropper-crop-box' )->getAttribute( 'style' );

        return [
            'before' => $cropperBoxInitialStyle,
            'after' => $cropperBoxResizedStyle
        ];
    }

    public function applyCrop()
    {
        $imageForm = $this->getLastImageElement();

        $saveCrop = $imageForm->find('css', '.js-save');

        $saveCrop->click();

        $this->getDocument()->waitFor( 10000, function () use ( $imageForm ){
            $modalBackground = $imageForm->find('css', '.js-modal-background');
            return (!$modalBackground->isVisible());
        } );
    }

}
