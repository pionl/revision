## Revision

[![Travis CI](https://img.shields.io/travis/stevebauman/revision.svg?style=flat-square)](https://travis-ci.org/stevebauman/revision)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/stevebauman/revision.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevebauman/revision/?branch=master)

### Installation

Insert Revision in your `composer.json` file:

    "stevebauman/revision": "1.0.*"
    
Now run `composer update`. Once that's complete, insert the Revision service provider inside your `config/app.php` file:

    `Stevebauman\Revision\RevisionServiceProvider`
    
Run `php vendor:publish` to publish the Revision migration file. Then, run `php artisan migrate`.

You're all set!

### Setup

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

