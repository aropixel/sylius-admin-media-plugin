@upload_images
Feature: Adding a new product with an image
    In order to add images to a product
    As an administrator
    I want to be able to upload image to a new product and crop

    Background:
        Given the store operates on a single channel in "United States"
        Given I am logged in as an administrator

    @ui @javascript
    Scenario: Adding an non cropped image to a product
        Given I want to create a new simple product
        When I attach the "t-shirts.jpg" image
        Then I should see the "t-shirts.jpg" image preview
        And I specify its code as "T-SHIRT"
        And I name it "t-shirt" in "English (United States)"
        And I set its slug to "t-shirt" in "English (United States)"
        And I set its price to "$100.00" for "United States" channel
        And I add it
        Then I should be notified that it has been successfully created

    @ui @javascript
    Scenario: Select a default crop as image type
        Given I want to create a new simple product
        When I add an image item
        Then I should see the configured default crops as type options
