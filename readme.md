Laravel Translate
===

This package changes the way **Laravel 5** handle translations files. By default Laravel uses PHP file with translations that might not be the best way to manage in case of multi-language sites or when you need to deliver text to translate to 3rd parties.
 
 Using this package:
 - You will store translations in `PO` files (and you can edit them in `Poedit`)
 - You don't have to change any piece of code in your existing application - you might use translations the same way you use them by default in Laravel
 - You don't need `gettext` to handle those translations (this package uses only `PO` and `POT` files but doesn't use `gettext` at all)
 
## Installation
 
1. Run
   ```php   
   composer require mnabialek/laravel-translate
   ```     
   in console to install this module
   
2. Open `config/app.php` and: 
  * Comment line with  
    ```php
    Illuminate\Translation\TranslationServiceProvider::class,
     ```
    
  * Add
    
    ```php
    Mnabialek\LaravelTranslate\Providers\TranslationServiceProvider::class,
    ```

    in same section (`providers`)

3. Run

    ```php
    php artisan vendor:publish --provider="Mnabialek\LaravelTranslate\Providers\TranslationServiceProvider"
    ```
    
    in your console to publish default configuration files
    
## Configuration
    
The first thing when using this package you should decide how are going to use your translations. You can store all the translations in single file `PO` or keep them in separate files - you can set it using `single_file` option in `translator` configuration file. Once you set up the mode you cannot change it easily at this stage, so you should decide it before you start working with any translations. 

### Single file mode

In case you set `single_file` mode to `true` all translations will be stored in single file (the name specified with `single_file_name` configuration option) and all translations keys will be explicit.

For example in case you are using in your application:

```
trans('messages.sample_text')
trans('sample_text')
trans('Sample text for something')
```

You will need to keep translations for  `messages.sample_text`, `sample_text` and `Sample text for something` in single file (by default `messages.po`).

As you see using this mode you can have in your translations also sentences (the same way 
as using `gettext` however in fact it's not recommended - you should separate translations text from your application - that's why using keys is usually the better option)

### Multiple file mode

In this mode translations will be stored in separate files. The first dot in translation will point in which file the rest of translation key will be stored. If there is no dot in translation it will be assumed `default_group_name` configuration option (by default `messages`)  

For example in case you are using in your application:

```
trans('messages.sample_text')
trans('sample_text')
trans('something.sample_text')
```

you should have `sample_text` translation in `messages.po` file, and should have translation for `sample_text` in `something.po` file. In this mode you cannot use sentences in your translations because you might get really unexpected behaviour. 

## Storing translation files

All the translation files should be stored in the same directory originally Laravel stores language files (by default `resources/assets/lang`) also divided in directories. For example you should keep translations  for `auth` for English in `resources/lang/en/auth.po` file.

All the `.php` translations files won't be used and it's recommended to remove them after you set up everything (please read further parts to know when it's fine to remove original translations)

## Extracting current translations

Assuming you have some translations, it's very possible you already have some translations created. By default in fresh Laravel installation you have also translations for example for `validation`, `auth` etc.

It's obvious you don't want to put those translations manually when you already have them. So first thing you should open those translation files to make sure you don't have any samples, example etc. For example in  `validation.php` file you have by default:

```php
'custom' => [
    'attribute-name' => [
        'rule-name' => 'custom-message',
    ],
],
```

or

```
'attributes' => [],
```

They are quite useless unless you have filled here something custom, so you can remove those parts or comment them before proceeding.

Once you finish cleaning your translation files, you can run:

```
artisan translator:extract
```

command. All existing translations will be exported into `PO` files into directory set in  `extract_directory` configuration option. 
 
At this point you can copy generated `.PO` files into `resources/assets/lang` directory and you have to open `additional.txt` file and copy its content and add it into  `additional_translations` section of your `translator` configuration file.
  
Once you complete this stage you can safely remove all `.php` files located in `resources/assets/lang` directory - you have now those in `.PO` files.


You should usually use this step only once (after installing this package) however it's possible that in future you might install other packages and you publish their files that include translations - in this case you will need to repeat this step).

## Export used translations

In your application you use or you will be using multiple translations. Assume you have in your Blade something like this:

```
{{ trans('messages.hello', ['name' => $name]) }}
 
{{ trans_choice('messages.prize',$points, ['number' => $points]) }}
```

What you want to do is create template files for your `.PO` in which you will keep what should be translated.  Those files are `.POT` files.

When you run:

```
artisan translator:export
```

command, those `.POT` files will be created and you can use them for translating.

This command will find all usages of:

- trans,
- trans_choice,
- Lang::get,
- Lang::trans,
- Lang::choice,
- Lang::transChoice,

in your files

## Using PO and POT files

After generating POT files you can create translation PO files. First you should verify if you have already existing PO file with same name as POT file or not. 

If you do, open this PO file and in `Poedit` you can now synchronize it with POT file. Now you should added more translation keys, you can translate them and save this file again.   

If you don't have this PO file yet, just open your POT file and choose creating new translation from existing POT file. Now you can translate and you should save this PO file in valid location inside `resources/assets/lang`

## FAQ

#### Something is not working

This package hasn't been tested in details yet, so in case of any problems, you can add  issue and it will be verified and hopefully fixed.

#### I would like something to be added

Please create Pull request
 
#### I'm getting some string to caught as translations and they shouldn't be 
 
This package puts into translations also `->trans('something')` or `->trans_choice('something')` (this is desired behaviour). In case you want to exclude any file from finding translations, you should add this file into `ignored_files`
   
#### In Poedit I'm getting warning that languages are the same
   
You can safely ignore this warning. It does not cause any problems with using translations.

## Licence

This package is licenced under the [MIT license](http://opensource.org/licenses/MIT)
