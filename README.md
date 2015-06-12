## Revision

[![Travis CI](https://img.shields.io/travis/stevebauman/revision.svg?style=flat-square)](https://travis-ci.org/stevebauman/revision)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/stevebauman/revision.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevebauman/revision/?branch=master)

### Installation

Insert Revision in your `composer.json` file:

    "stevebauman/revision": "1.0.*"
    
Now run `composer update`. Once that's complete, insert the Revision service provider inside your `config/app.php` file:

    `Stevebauman\Revision\RevisionServiceProvider`
    
Run `vendor:publish` to publish the Revision migration file. Then, run `php artisan migrate`.

You're all set!

### Usage

Insert the `Stevebauman\Revision\Traits\HasRevisionsTrait` onto your model:
    
    use Stevebauman\Revision\Traits\HasRevisionsTrait;
    
    class Post extends Model
    {
        use HasRevisionsTrait;
    }
