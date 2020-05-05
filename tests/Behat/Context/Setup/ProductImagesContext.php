<?php

declare(strict_types=1);

namespace Tests\Aropixel\SyliusAdminMediaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Tests\Aropixel\SyliusAdminMediaPlugin\Behat\Page\Admin\Product\CreateSimpleProductWithImagesPage;
use Webmozart\Assert\Assert;

class ProductImagesContext implements Context
{

    /** @var CreateSimpleProductWithImagesPage */
    private $createSimpleProductWithImagesPage;

    public function __construct(
        CreateSimpleProductWithImagesPage $createSimpleProductWithImagesPage
    ) {
        $this->createSimpleProductWithImagesPage = $createSimpleProductWithImagesPage;
    }


    /**
     * @Then I should see the :path image preview
     */
    public function iShouldSeeImagePreview($path)
    {

        $page = $this->createSimpleProductWithImagesPage;

        // le loader doit être affiché
        $page->isSpinnerVisible();

        // on attend que le spinner soit masqué, donc que l'appel ajax soit terminé
        $page->waitForAjaxUpload();

        $isImagePreviewVisible = $page->isImagePreviewVisible($path);

        // on vérifie que le lien de l'image en preview est valide
        Assert::true($isImagePreviewVisible);
        
    }

}
