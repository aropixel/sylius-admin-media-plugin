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
        //Assert::true($this->createSimpleProductWithImagesPage->isSpinnerVisible());

        // on vérifie que le lien de l'image en preview est valide
        Assert::true($this->createSimpleProductWithImagesPage->isImagePreviewVisible($path));
    }

    /**
     * @When I add an image item
     */

    public function iAddAnImageItem()
    {
        $this->createSimpleProductWithImagesPage->addImageItem();
    }

    /**
     * @Then I should see the configured default crops as type options
     */
    public function iShouldSeeDefaultCropsAsOptions()
    {
        $page = $this->createSimpleProductWithImagesPage;

        $defaultCrops = $page->getDefaultCrops();

        $optionsInTypeAsText = $page->getTypeOptions();

        // foreach crops, check if the label of the crop is found is the options of the type select input
        foreach ($defaultCrops as $defaultCrop) {
            Assert::true(strpos($optionsInTypeAsText, $defaultCrop) !== false);
        }
    }

    /**
     * @Then I should see the configured entity crops as type options
     */
    public function iShouldSeeEntityCropsAsOptions()
    {

        $productImageCrops = $this->createSimpleProductWithImagesPage->getProductImageCrops();

        $optionsInTypeAsText = $this->createSimpleProductWithImagesPage->getTypeOptions();

        // foreach crops, check if the label of the crop is found is the options of the type select input
        foreach ($productImageCrops as $productImageCrop) {
            Assert::true(strpos($optionsInTypeAsText, $productImageCrop) !== false);
        }
    }

    /**
     * @Then I should be able to crop freely the image
     */
    public function iShouldBeAbleToCropFreely()
    {
        Assert::true($this->createSimpleProductWithImagesPage->isCroppingFree());
    }

    /**
     * @When I select the :square type
     */
    public function iSelectType($type)
    {
        $this->createSimpleProductWithImagesPage->selectImageType($type);
    }


    /**
     * @Then I should be able to crop the image only as square
     */
    public function iShouldBeAbleToCropAsSquare()
    {
        Assert::true($this->createSimpleProductWithImagesPage->isCroppingSquare());
    }

    /**
     * @When I apply the crop
     */
    public function iApplyTheCrop()
    {
        $this->createSimpleProductWithImagesPage->applyCrop();
    }


}
