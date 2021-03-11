<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\UserVerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property Carbon|null email_verified_at
 * @property string password
 * @property string|null bio
 * @property Carbon created_at
 * @property string|null facebook_id
 * @property string|null google_id
 * @property int|null read_books_count
 * @property Carbon updated_at
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
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new UserVerifyEmailNotification());
    }

    public function toArray(): array
    {
        $arr = parent::toArray();

        $arr['has_password'] = $this->hasPassword();

        return $arr;
    }

    public function hasPassword(): bool
    {
        return (bool)$this->password;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function loadReadBooksCount(): self
    {
        $this->read_books_count = $this->books()->where('status', UserBook::STATUS_READ)->count();

        return $this;
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'follows',
            'followee_id',
            'follower_id'
        );
    }
    public function followees(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'follows',
            'follower_id',
            'followee_id'
        );
    }

    public function follow(User $followee): void
    {
        $this->followees()->syncWithoutDetaching([$followee->id]);
        $this->save();
    }

    public function unfollow(User $followee): void
    {
        $this->followees()->detach($followee);
        $this->save();
    }
}
