@upload_images
Feature: Adding a new product with an image
    In order to add images to a product
    As an administrator
    I want to be able to upload image to a new product

    Background:
        Given I am logged in as an administrator

    @ui @javascript
    Scenario: Adding an image to product
        Given I want to create a new simple product
        When I attach the "t-shirts.jpg" image
        Then I should see the "t-shirts.jpg" image preview
