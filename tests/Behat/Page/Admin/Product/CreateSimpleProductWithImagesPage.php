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

        return $this->isImageLinkBroken( $imgUrl );

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

}
