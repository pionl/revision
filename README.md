## Revision

[![Travis CI](https://img.shields.io/travis/stevebauman/revision.svg?style=flat-square)](https://travis-ci.org/stevebauman/revision)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/stevebauman/revision.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevebauman/revision/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/stevebauman/revision.svg?style=flat-square)](https://packagist.org/packages/stevebauman/revision)
[![Total Downloads](https://img.shields.io/packagist/dt/stevebauman/revision.svg?style=flat-square)](https://packagist.org/packages/stevebauman/revision)
[![License](https://img.shields.io/packagist/l/stevebauman/revision.svg?style=flat-square)](https://packagist.org/packages/stevebauman/revision)

### Installation

Insert Revision in your `composer.json` file:

    "stevebauman/revision": "1.1.*"
    
Now run `composer update`. Once that's complete, insert the Revision service provider inside your `config/app.php` file:

    `Stevebauman\Revision\RevisionServiceProvider`
    
Run `php vendor:publish` to publish the Revision migration file. Then, run `php artisan migrate`.

You're all set!

### Setup

Create the `Revision` model and insert the `belongsTo()` or `hasOne()` `user()` relationship as well as the `RevisionTrait`:

    namespace App\Models;
    
    use Stevebauman\Revision\Traits\RevisionTrait;
    
    class Revision extends Eloquent
    {
        use RevisionTrait;
    
        protected $table = 'revisions';
        
        public function user()
        {
            return $this->belongsTo('App\Models\User');
        }
    }

Insert the `Stevebauman\Revision\Traits\HasRevisionsTrait` onto your base model:
    
    namespace App\Models;
    
    use Stevebauman\Revision\Traits\HasRevisionsTrait;
    
    class BaseModel extends Eloquent
    {
        use HasRevisionsTrait;
        
        public function revisions()
        {
            return $this->morphMany('Stevebauman\Revision\Models\Revision', 'revisionable');
        }
        
        public function revisionUser()
        {
            return $this->hasOne('App\Models\User');
        }

        public function revisionUserId()
        {
            return $this->revisionUser->id;
        }
    }

The trait includes 3 (three) abstract methods that you must implement in your base model (shown above). This allows you to use your
 own revision model, as well as your own user model. If you'd like to supply your own Revision model, create one and insert it into
 the base models `revisions()` relationship like so:

Your `Revision` model:
    
    namespace App\Models;
    
    use Stevebauman\Revision\Traits\RevisionTrait;
    
    class Revision extends Eloquent
    {
        use RevisionTrait;
    
        protected $table = 'revisions';
    }

Your `BaseModel`:

    use Stevebauman\Revision\Traits\HasRevisionsTrait;
        
    class BaseModel extends Eloquent
    {
        use HasRevisionsTrait;
        
        public function revisions()
        {
            return $this->morphMany('App\Models\Revision', 'revisionable');
        }
    
        public function revisionUser()
        {
            return $this->hasOne('App\Models\User');
        }
    
        public function revisionUserId()
        {
            return $this->revisionUser->id;
        }
    }

### Usage

#### Revision Columns

You **must** insert the `$revisionColumns` property on your model to track revisions.

###### Tracking All Columns

To track all changes on every column on the models database table, use an asterisk like so:

    class Post extends BaseModel
    {
        protected $table = 'posts';
        
        protected $revisionColumns = ['*'];
    }
    
###### Tracking Specific Columns

To track changes on specific columns, insert the column names you'd like to track like so:

    class Post extends BaseModel
    {
        protected $table = 'posts';
        
        protected $revisionColumns = [
            'user_id',
            'title', 
            'description',
        ];
    }


#### Displaying Revisions

To display your revisions on a record, call the relationship accessor `revisions`. Remember, this is just
a regular Laravel relationship, so you can eager load / lazy load your revisions as you please:

    $post = Post::with(['revisions'])->find(1);
    
    return view('post.show', ['post' => $post]);

On each revision record, you can use the following methods to display the revised data:

###### getColumnName()

To display the column name that the revision took place on, use the method `getColumnName()`:

    $revision = Revision::find(1);
    
    echo $revision->getColumnName(); // Returns string
    
###### getUserResponsible()

To retrieve the User that performed the revision, use the method `getUserResponsible()`:

    $revision = Revision::find(1);
    
    $user = $revision->getUserResponsible(); // Returns user model
    
    echo $user->id;
    echo $user->email;
    echo $user->first_name;

###### getOldValue()

To retrieve the old value of the record, use the method `getOldValue()`:

    $revision = Revision::find(1);
    
    echo $revision->getOldValue(); // Returns string
    
###### getNewValue()

To retrieve the new value of the record, use the method `getNewValue()`:

    $revision = Revision::find(1);
    
    echo $revision->getNewValue(); // Returns string

###### Example

// In your `post.show` view:

    @if($post->revisions->count() > 0)
        
         <table class="table table-striped">
         
                <thead>
                    <tr>
                        <th>User Responsible</th>
                        <th>Changed</th>
                        <th>From</th>
                        <th>To</th>
                        <th>On</th>
                    </tr>
                </thead>
                
                <tbody>
                @foreach($post->revisions as $revision)
                    <tr>
                        <td>{{ $revision->getUserResponsible()->first_name }} {{ $record->getUserResponsible()->last_name }}</td>
                        <td>{{ $revision->getColumnName() }}</td>
                        <td>
                            @if(is_null($revision->getOldValue()))
                                <em>None</em>
                            @else
                                {{ $revision->getOldValue() }}
                            @endif
                        </td>
                        <td>{{ $revision->getNewValue() }}</td>
                        <td>{{ $revision->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
                
            </table>
    @else
        <h5>There are no revisions to display.</h5>
    @endif

#### Modifying the display of column names

To change the display of your column name that is revisioned, insert the property `$revisionColumnsFormatted` on your model:

    protected $revisionColumnsFormatted = [
        'user_id' => 'User',
        'title' => 'Post Title',
        'description' => 'Post Description',
    ];

#### Modifying the display of values

To change the display of your values that have been revisioned, insert the property `$revisionColumnsMean`. You can use
dot notation syntax to indicate relationship values. For example:

    protected $revisionColumnsMean = [
        'user_id' => 'user.full_name',
    ];

You can even use laravel accessors with the `revisionColumnsMean` property.

> **Note**: The revised value will be passed into the first parameter of the accessor.

    protected $revisionColumnsMean = [
        'status' => 'status_label',
    ];
    
    public function getStatusLabelAttribute($status = null)
    {
        if(! $status) {
            $status = $this->getAttribute('status');
        }
        
        return view('status.label', ['status' => $status])->render();
    }
