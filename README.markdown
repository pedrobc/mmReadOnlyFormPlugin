# mmReadOnlyFormPlugin


symfony 1.4/1.3/1.2 plugin to easily handle readonly forms (and widgets) in the Symfony form framework.

## Purpose

Using this plugin you will be able to maintain your Rackspace Cloud files (images, javascripts, 
stylesheets, etc) easily without having to interact directly with the Rackspace API.

## How to install

Download this plugin into your /plugins dir
Using `git`, the standard way:

    $ cd /path/to/symfony/project
    $ git clone git://github.com/pedrobc/mmReadOnlyBaseForm.git

If your project already uses `git`, you can add the plugin as one of its submodule:

    $ cd /path/to/symfony/project
    $ git submodule add git://github.com/pedrobc/mmReadOnlyBaseForm.git plugins/mmReadOnlyBaseForm
    $ git submodule update --init --recursive
    $ git commit -a -m "added mmRackspaceFilePlugin submodule"

and activate it in your ProjectConfiguration.class.php (for example).

    $this->enablePlugins(…, 'mmReadOnlyBaseForm');


## Simple example

Change the parent class in lib/form/BaseForm.class.php to mmReadOnlyBaseForm

    class BaseForm extends sfFormSymfony
    
should be
  
    class BaseForm extends mmReadOnlyBaseForm
      
## Known Limitations

* 

## More

See the tests files for further details.