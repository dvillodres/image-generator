# image-generator

**image-generator** is a Composer package that allows you to generate 
images by overlaying text onto an image using a provided image URL and 
text.

---

## Usage

### Installation

You can install the package via Composer. Run the following command:

```
composer require dvillodres/image-generator
```

### Code example

```
use DVillodres\ImageGenerator\Image;
use DVillodres\ImageGenerator\ImageConfig;

Image::create(
    ImageConfig::postCover(
        $this->imgDir . '/test-' . time() . '.jpg',
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        'example.com',
        color: '#ffffff',
        textColor: '#55b8ff',
        imgURL: 'https://images.pexels.com/photos/792416/pexels-photo-792416.jpeg'
    )
);
```

---

## Contributing

Contributions are welcome! Please feel free to submit a pull request.

---

## Acknowledgements

I want to extend my heartfelt gratitude to [Dantsu](https://github.com/DantSu) for his invaluable contribution to the world of development, particularly for his outstanding package [php-image-editor](https://github.com/DantSu/php-image-editor). Before creating this project, I relied on his package as an essential part of our workflow.

Dantsu's work provided a robust foundation and inspired us to create a simplified version tailored to our specific needs. We appreciate his dedication and effort in crafting and maintaining such a valuable project.

Thank you, Dantsu, for being a source of inspiration for the development community!

---

**Note:** This project was inspired by the work of [Dantsu/php-image-editor](https://github.com/DantSu/php-image-editor) before evolving into a simplified version for our specific requirements.

---
**Author:** Daniel Villodres | **Personal Website:** [d-v.es](https://d-v.es)
