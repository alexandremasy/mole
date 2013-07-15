Mole
===

Like a mole digging in it's own way, the library dig and find all the javascript files. While developping javascript application i too often have an error saying 'Object is not a constructor' because i forgot to include a file. To prevent that, a mole is born.

### Preparation

First thing is to include the library.

### Instantiation

The constructor accept a bunch of parameters:

- minified `string` The path where would be outputed the minified file
- root `string` The root folder. This path will be used to detect files existance. 
- base `string` Path to the document root. Used to compute html path. Default value is null
- relative `bool` Would the outputed path be relative or not. Default value is false.
- env `bool` Would the ouput be aware of the environment values

### Add some stuff

Now it's time to add some path into the library. For that purpose, the api provide a well named method called `add`.
This one accept only one parameters, the path. it should be relative to the root previously provided. The path could be a **file** or a **directory**. In the case of a folder, all the files contained in it would be **recursively** added to the list. 

To prevent duplicate, the path is compared to the list before being added.

### Build vs Write

Now that we are done adding path, time to output something. You have two method at your disposal for that:

- `build` Will build the list of file to output, in the order the path have been added. So if you want a file comming before another, juste add it sooner. This method accept a parameter, the type of output.  
- `write` Will build the list of file and write the ouput in a file. Two parameters in this case:
    - the type of output;
    - the destination of the file;

#### Output types

Currently only two types of output are included with the library.

- **html**: Will output the list of file with script tags.
- **closure**: Will output the list of file in the format of a flagfile for [Google Closure](https://developers.google.com/closure/compiler/).

Two constants are defined to prevent typo: `Mole::HTML` and `Mole::CLOSURE`.

### E.G.

```php
    require('Mole.php');

    $root = $_SERVER['DOCUMENT_ROOT'];
    $flag = dirname($root).DIRECTORY_SEPARATOR.'build/build.flag';

    $demo = new Mole( 'statics/libs/demo.min.js', $root, null, true );
    $demo->add('statics/libs/demo/hello.js');
    $demo->add('statics/libs/demo');
    $demo->write( Mole::CLOSURE, $flag );
    echo $demo->build( Mole::HTML );
```

Enjoy!
