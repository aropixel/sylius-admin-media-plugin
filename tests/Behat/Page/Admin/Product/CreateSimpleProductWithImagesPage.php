<?php

declare( strict_types=1 );

namespace Tests\Aropixel\SyliusAdminMediaPlugin\Behat\Page\Admin\Product;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Product\CreateSimpleProductPage;
use Webmozart\Assert\Assert;

class CreateSimpleProductWithImagesPage extends CreateSimpleProductPage
{

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

        $baseUrl = $this->getParameter('base_url');

        $this->getSession()->visit( $baseUrl . '/' . $imgUrl );

        $pageText = $this->getDocument()->getText();

        // return true si c'est trouvé
        return (strpos($pageText, '404 Not Found') === false);

    }

    private function getLastImageElement(): NodeElement
    {
        $images = $this->getElement('images');
        $items = $images->findAll('css', 'div[data-form-collection="item"]');

        Assert::notEmpty($items);

        return end($items);
    }


    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'spinner' => '.spinner-container',
        ]);
    }

}
