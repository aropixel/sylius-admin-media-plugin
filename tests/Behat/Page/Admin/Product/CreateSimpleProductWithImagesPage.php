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
        Assert::true($spinner->isVisible());
    }

    public function waitForAjaxUpload()
    {
        // le timeout est en micro seconde, 10000000ms = 10s
        $this->getDocument()->waitFor(10000000, function () {
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
        $imageForm = $this->getLastImageElement();

        $imageNode = $imageForm->find('css', '.crop-hover img');

        $imgUrl = $imageNode->getAttribute('src');

        Assert::true(strpos($imgUrl, $path) !== false);

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

    public function getTypeOptions()
    {
        $imageForm = $this->getLastImageElement();
        $typeInput = $imageForm->find('css', '.js-admin-media-type');

        return $typeInput->getText();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'spinner' => '.spinner-container',
        ]);
    }

}
