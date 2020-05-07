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
    Scenario: List default crops as selectable image types
        Given I want to create a new simple product
        When I add an image item
        Then I should see the configured default crops as type options

    @ui @javascript
    Scenario: List entity crops as selectable image types
        Given I want to create a new simple product
        When I add an image item
        Then I should see the configured entity crops as type options

    @ui @javascript
    Scenario: Crop freely an image
        Given I want to create a new simple product
        When I attach the "t-shirts.jpg" image
        Then I should be able to crop freely the image
        When I apply the crop
        Then I should see the "cropped/" image preview


    @ui @javascript
    Scenario: Crop an image based on crop ratio
        Given I want to create a new simple product
        When I attach the "t-shirts.jpg" image
        And I select the "Format carré" type
        Then I should be able to crop the image only as square
        When I apply the crop
        # doesn't work with symfony in a subfolder (the ajax controller of artgris save the crop file
        # with a calculated path that work only in a classic symfony folder structure -- maybe needs
        # to be overrided in order to test and to prevent bugs in non classic symfony folder structure)
        #Then I should see the "cropped/" image preview
    
    # See the library contain uploaded images
