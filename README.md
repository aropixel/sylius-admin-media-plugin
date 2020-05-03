<p align="center">
  <a href="http://www.aropixel.com/">
    <img src="https://avatars1.githubusercontent.com/u/14820816?s=200&v=4" alt="Aropixel logo" width="75" height="75" style="border-radius:100px">
  </a>
</p>

<h1 align="center">Sylius Admin Media Pugin</h1>
<h3 align="center">Enhanced sylius admin media management with image cropping, library (based on Artgris Media Bundle)</h3>


## Table of contents

- [Presentation](#presentation)
- [Installation](#installation)
- [Usage](#usage)
- [License](#license)


## Presentation


Once the plugin is installed and configured, the sylius image system for the ressource (product etc) will be replaced by complete media management system. You'll be able to:

- upload image (of course)
- open a file manager in order to retrieve all the image uploaded, and select one of them
- crop freely the image directly in the admin before saving it
- select a format that will force you to crop the image with a certain ratio)

This plugin is heavily based on the artgris media bundle and file manager.

## Installation

In a sylius application :

- Install the plugin : 
`composer require sylius-admin-media-plugin`

- Create a aropixel_sylius_admin_media.yaml in the config folder and import the plugin configuration:

```
imports:
    - { resource: "@AropixelSyliusAdminMediaBundle/Resources/config/app/config.yml" }
```

- Create a aropixel_sylius_admin_media.yaml in the config/routes folder and import the plugin routes:

```
aropixel_sylius_admin_media:
    '@AropixelSyliusAdminMediaBundle/Resources/config/routes.xml'
```

- In the bundles.php file, register the new plugins: 

```
Artgris\Bundle\FileManagerBundle\ArtgrisFileManagerBundle::class => ['all' => true],
Gregwar\ImageBundle\GregwarImageBundle::class => ['all' => true],
Artgris\Bundle\MediaBundle\ArtgrisMediaBundle::class => ['all' => true],
Aropixel\SyliusAdminMediaPlugin\AropixelSyliusAdminMediaPlugin::class => ['all' => true],
```

- Create the 'uploads' folder in the 'public' folder

- install the assets: 

```php bin/console assets:install```


## Usage

For each ressource, you can define crops format (ratio) that you want to use for cropping the image in admin. Each crop definied is linked to a liip filter so that the ratio of the crop is automatically calculated based on the liip filter size.


For example, imagine you have a blog system in your sylius app and you want to display each blog post in the home with a thumbnail. 
You have two sylius ressource: Post Entity and a PostImage entity.


- First you need to create the admin form for your Post entity, and also the admin form for your PostImage entity. The entity form will add a CollectionType for the images:

```
->add('images', CollectionType::class, [
                'entry_type' => PostImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Images'
            ])
```

- The PostImage form will need to extend the ImageType defined by the plugin, example;

```
use App\Entity\Producer\ProducerImage;
use Aropixel\SyliusAdminMediaBundle\Form\Type\ImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;


final class PostImageType extends ImageType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => PostImage::class,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'app_post_image';
    }
}

```

- Then register the image form in the services.yaml:

```
    app.form.type.post_image:
        class: App\Form\Type\PostImageType
        tags:
            - { name: form.type }
        arguments: [ '@aropixel_sylius_admin_media.image_crop.crop_ratio_manager', '%app.model.post_image.class%']
```



- In a classic sylius application, you generally use liip filters in order to generate, in front the thumbnail of your image blog post, example:

```
liip_imagine:
    resolvers:
        default:
            web_path:
                web_root: "%kernel.project_dir%/public"
                cache_prefix: "media/cache"

    filter_sets:
        home_news:
            quality: 75
            filters:
                strip: ~
                thumbnail:
                    size: [600, 400]
                    mode: outbound
                    allow_upscale: true
```

- When the admin upload the image for the blog post, with this plugin, he can choose a defined format in order to be able to crop the image at the correct ratio, before saving it, so that the image is perfectly suited for the thumbnail! Here is the configuration of the plugin if you want to let the admin crop the image using the home_news liip filter size:

 ```  
aropixel_sylius_admin_media:
 
    entities_crops:
        #first you have to define the image entity in which you want to use the crop system
        App\Entity\Producer\ProducerImage:
            # you use the the liip filter id and define a name that will be displayed for selecting this crop
            home_news: "Home news"
```


And that's it! You can now in the admin choose a format and crop it with a specfic ratio so that it's perfectly suited for your page!

## License
Aropixel Blog Bundle is under the [MIT License](LICENSE)
