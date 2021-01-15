<?php

namespace App;

use App\Notifications\UserVerifyEmailNotification;
use DateTime;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string name
 * @property string email
 * @property string avatar
 * @property string avatar_original
 * @property int id
 * @property DateTime|null email_verified_at
 * @property string password
 */
class User extends Authenticatable implements JWTSubject, HasMedia
{
    use Notifiable, FilepondTrait, HasMediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(static function (User $user) {
            if (array_key_exists('email', $user->getChanges())) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
            }
        });

        static::created(static function(User $user) {
            $user->sendEmailVerificationNotification();
        });
    }

    public function books(): HasMany
    {
        return $this->hasMany(UserBook::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function addBook(Book $book): UserBook
    {
        $userBook = UserBook::create($this, $book);
        $this->books()->get()->add($userBook);
        $userBook->save();
        $userBook->addRootSection();

        return $userBook;
    }

    public function filepondFields(): array
    {
        return [
            ['field' => 'avatar', 'type' => 'image', 'maxFileSize' => 1024 * 1024 * 5],
        ];
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new UserVerifyEmailNotification());
    }

    public function toArray(): array
    {
        $arr = parent::toArray();

        $arr['has_password'] = (bool)$this->password;

        return $arr;
    }
}
