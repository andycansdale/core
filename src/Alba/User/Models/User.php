<?php namespace Alba\User\Models;

use Carbon\Carbon;
use Illuminate\Auth\UserInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Zizaco\Entrust\HasRole;
use Alba\Core\Models\Model;
use Alba\User\Models\Token;
use Alba\User\Models\Role;

/**
 * Alba\User model
 *
 * @author diego <diego@emersonmedia.com>
 * @author daniel <daniel@bexarcreative.com>
 * @see Alba\Core\Models\Model
 * @see Alba\User\Models\Name
 * @see Alba\User\Models\Role
 * @see Alba\User\Models\Token
 */
class User extends Model implements UserInterface {

    /**
     * Include HasRole trait from Entrust
     */
    use HasRole;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The relationships that should be eager loaded with each query
     *
     * @var array
     */
    protected $with = ['name'];

    /**
     * Attributes that Ardent should Hash
     *
     * @var array
     */
    public static $passwordAttributes = ['password'];

    /**
     * Ardent should automatically hash the $passwordAttributes
     *
     * @var boolean
     */
    public $autoHashPasswordAttributes = true;

    /**
     * Removes the _confirmation type fields
     *
     * @var boolean
     */
    public $autoPurgeRedundantAttributes = true;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * The attributes that can be safely filled
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'active', 'blocked', 'password_confirmation',
        'password_updated_at', 'activated_at', 'authenticated_at',
    ];

    /**
     * Enable soft deletes on model
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * The attributes that can be full-text searched
     *
     * @var array
     */
    public $searchable = ['email'];

    /**
     * Default roles to set to new users
     *
     * @var array
     */
    public $defaultRoles = ['user'];

    /**
     * Options for active status dropdowns
     *
     * @var array
     */
    public $activeOptions = [
        ''      => 'Any Active',
        1       => 'Activated',
        0       => 'Deactivated',
    ];

    /**
     * Options for blocked status dropdowns
     *
     * @var array
     */
    public $blockedOptions = [
        ''      => 'Any Blocked',
        1       => 'Blocked',
        0       => 'Unblocked',
    ];

    /**
     * Options for order by dropdowns
     *
     * @var array
     */
    public $orderOptions = [
        'id'                    => 'ID',
        'name'                  => 'Name',
        'email'                 => 'Email',
        'active'                => 'Active Status',
        'blocked'               => 'Blocked Status',
        'created_at'            => 'Created',
        'updated_at'            => 'Updated',
        'activated_at'          => 'Activated',
        'deleted_at'            => 'Deleted',
        'authenticated_at'      => 'Last Authenticated',
        'password_updated_at'   => 'Last Password Update',
    ];

    /**
     * Relationships that Ardent should set up
     * 
     * @var array
     */
    public static $relationsData = [
        'name' => [
            self::HAS_ONE,
            'Alba\User\Models\Name',
            'foreignKey' => 'user_id'
        ],
        'tokens' => [
            self::BELONGS_TO_MANY,
            'Alba\User\Models\Token',
            'foreignKey' => 'user_id',
            'table' => 'token_user',
        ],
    ];

    /**
     * The attribute rules that Ardent will validate against
     * 
     * @var array
     */
    public static $rules = [
        'email' => ['required', 'email', 'max:128', 'unique:users'],
        'password' => ['required', 'alpha_num', 'between:4,256', 'confirmed'],
        'password_confirmation' => ['required_with:password', 'alpha_num', 'between:4,256'],
        'active' => ['in:true,false,1,0'],
        'blocked' => ['in:true,false,1,0'],
        'activated_at' => ['date'],
        'authenticated_at' => ['date'],
        'password_updated_at' => ['date'],
    ];

    /**
     * The attribute rules used by seeder
     * 
     * @var array
     */
    public static $rulesForSeeding = ['email'];

    /**
     * The attribute rules used by store()
     * 
     * @var array
     */
    public static $rulesForStoring = ['email', 'password', 'password_confirmation'];

    /**
     * The attribute rules used by update()
     * 
     * @var array
     */
    public static $rulesForUpdating = ['email', 'password', 'password_confirmation'];

    /**
     * The attribute rules used by activate()
     * 
     * @var array
     */
    public static $rulesForActivating = ['active', 'activated_at'];

    /**
     * The attribute rules used by block()
     * 
     * @var array
     */
    public static $rulesForBlocking = ['blocked'];

    /**
     * The attribute rules used by savePassword()
     * 
     * @var array
     */
    public static $rulesForUpdatingPassword = ['password', 'password_confirmation', 'password_updated_at'];

    /**
     * Rules needed for seeding
     * 
     * @return array
     */    
    public function getRulesForSeedingAttribute()
    {
        return array_only(self::$rules, self::$rulesForSeeding);
    }

    /**
     * Rules needed for storing
     * 
     * @return array
     */    
    public function getRulesForStoringAttribute()
    {
        return array_only(self::$rules, self::$rulesForStoring);
    }

    /**
     * Rules needed for updating
     * 
     * @return array
     */
    public function getRulesForUpdatingAttribute()
    {
        $rules = array_only(self::$rules, self::$rulesForStoring);

        // add exception for the unique constraint
        $key = array_search('unique:users', $rules['email']);
        $rules['email'][$key] = 'unique:users,email,' . $this->id;

        return $rules;
    }

    /**
     * Rules needed for activating
     * 
     * @return array
     */
    public function getRulesForActivatingAttribute()
    {
        return array_only(self::$rules, self::$rulesForActivating);
    }

    /**
     * Rules needed for updating password
     * 
     * @return array
     */
    public function getRulesForUpdatingPasswordAttribute()
    {
        return array_only(self::$rules, self::$rulesForUpdatingPassword);
    }

    /**
     * Rules needed for blocking
     * 
     * @return array
     */
    public function getRulesForBlockingAttribute()
    {
        return array_only(self::$rules, self::$rulesForBlocking);
    }

    /**
     * Returns a string with the full name of the user
     * 
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->name->fullName;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Many-to-Many relations with Role.
     * Do NOT remove this definition because it is needed to overwrite
     * Entrust's implementation.
     *
     * @return Illuminate\Database\Eloquent\Relationship
     */
    public function roles()
    {
        return $this->belongsToMany('Alba\User\Models\Role', 'assigned_roles', 'user_id', 'role_id');
    }

    /**
     * Returns the user who has the activation token indicated
     *
     * @param  Illuminate\Database\Query\Builder $query
     * @param string $token
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeWhereActivationToken($query, $token)
    {
        return $query
            ->select('users.*') //this should be here, so it gets the correct id field
            ->join('token_user', 'users.id', '=', 'token_user.user_id')
            ->join('tokens', 'tokens.id', '=', 'token_user.token_id')
            ->where('tokens.type', '=', Token::TYPE_ACTIVATION)
            ->where('tokens.token', '=', $token);
    }

    /**
     * Returns the user who has the password reset token indicated
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param string $token
     * @param boolean $isExpired
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeWherePasswordResetToken($query, $token, $isExpired = null)
    {
        // Get the user with token
        $query->select('users.*')
            ->join('token_user', 'users.id', '=', 'token_user.user_id')
            ->join('tokens', 'tokens.id', '=', 'token_user.token_id')
            ->where('tokens.type', '=', Token::TYPE_PASSWORD_RESET)
            ->where('tokens.token', '=', $token);

        // Return only expired tokens
        if ( $isExpired === true )
        {
            $query->where('tokens.expires_at', '<', Carbon::now());
        }

        // Return only valid tokens
        elseif ( $isExpired === false)
        {
            $query->where('tokens.expires_at', '>=', Carbon::now());
        }

        return $query;

    }

    /**
     * Builds a query scope to return users of a certain role
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param string|array $roles ids of role
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeOfRole($query, $roles)
    {
        // Convert roles string to array
        if ( is_string($roles) )
            $roles = explode(',', $roles);

        // Separate role names from role ids
        $rolesNames = [];
        foreach($roles as $i => $role)
        {
            if(!is_numeric($role))
            {
                $roleNames[] = $role;
                unset($roles[$i]);
            }
        }

        // Get role ids by querying role names and merge
        $roleIds = $roles;
        if(!empty($roleNames))
        {
            $roles = Role::whereIn('name', $roleNames)->lists('id');
            $roleIds = array_values(array_unique(array_merge($roleIds, $roles), SORT_NUMERIC));
        }

        // Query the assign_roles pivot table for matching roles
        return $query->addSelect(['assigned_roles.role_id'])
            ->join('assigned_roles', 'users.id', '=', 'assigned_roles.user_id')
            ->whereIn('assigned_roles.role_id', $roleIds)
            ->groupBy('users.id');
    }

    /**
     * Builds a query scope to return users by first or last name
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param string|array $names
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeByName($query, $names)
    {
        // Convert roles string to array
        if ( is_string($names) )
            $names = explode(',', $names);

        // Query the names table for matching names
        return $query->joinNames()
            ->where(function($query) use ($names)
                {
                    // Loop over each name to find matches for both first and last name
                    foreach($names as $name)
                    {
                        $query->orWhere('user_names.first_name', 'LIKE', '%'.$name.'%')
                            ->orWhere('user_names.last_name', 'LIKE', '%'.$name.'%');
                    }
                });
    }

    /**
     * Builds a query scope to return users with names
     *
     * @param Illuminate\Database\Query\Builder $query
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeJoinNames($query)
    {
        // Query the names table
        $name = DB::raw('CONCAT(`user_names`.`first_name`, `user_names`.`middle_name`, `user_names`.`last_name`) AS `name`');
        return $query->addSelect(['user_names.first_name', 'user_names.last_name', $name])
            ->join('user_names', 'users.id', '=', 'user_names.user_id');
    }

    /** 
     * Get a token by type
     * 
     * @param string $type
     * @return Token
     */
    public function getTypeToken($type)
    {
        return $this->tokens()
            ->whereType($type)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /** 
     * Returns the current activation token of the user
     * 
     * @return Token
     */
    public function getActivationTokenAttribute()
    {
        return $this->getTypeToken(Token::TYPE_ACTIVATION);
    }

    /** 
     * Returns the current password reset token of the user
     *
     * @return Token
     */
    public function getPasswordResetTokenAttribute()
    {
        return $this->getTypeToken(Token::TYPE_PASSWORD_RESET);
    }

    /**
     * Returns the message describing the login allowed status
     * 
     * @return string
     */
    public function getLoginStatusAttribute()
    {
        if (!$this->active)
        {
            return Lang::get('alba::user.messages.not_active');
        }
        if (is_null($this->password))
        {
            return Lang::get('alba::user.messages.no_password');
        }
        if ($this->blocked)
        {
            return Lang::get('alba::user.messages.is_blocked');
        }
        return null;
    }

    /**
     * Returns a string representing the status of password
     *
     * @return string
     */
    public function getPasswordStatusAttribute()
    {
        return is_null($this->password) ? Lang::get('alba::user.messages.no_password') : Lang::get('alba::user.messages.has_password');
    }

    /**
     * Returns a string representing the status of account activation
     *
     * @return string
     */
    public function getActiveStatusAttribute()
    {
        return $this->active ? Lang::get('alba::user.messages.active') : Lang::get('alba::user.messages.not_active');
    }    

    /**
     * Returns a string representing the blocked status of this account
     *
     * @return string
     */
    public function getBlockedStatusAttribute()
    {
        return $this->blocked ? Lang::get('alba::user.messages.blocked') : Lang::get('alba::user.messages.not_blocked');
    }

    /**
     * Returns the number of days since the last password update
     *
     * @return integer
     */
    public function getDaysSinceLastPasswordUpdateAttribute()
    {
        $date = new Carbon($this->password_updated_at);
        $now = Carbon::now();
        return $date->diffInDays($now);
    }

    /**
     * Returns the number of minutes since the last log in
     *
     * @return integer
     */
    public function getTimeSinceLastAuthenticatedAttribute()
    {
        // Short circuit for users who haven't authenticated yet
        if( is_null($this->authenticated_at) )
        {
            return Lang::get('alba::user.messages.never_authenticated');
        }

        $date = new Carbon($this->authenticated_at);
        return $date->diffForHumans();
    }

    /**
     * Returns the number of minutes since the last activation
     *
     * @return integer
     */
    public function getTimeSinceLastActivatedAttribute()
    {
        // Short circuit for users who haven't authenticated yet
        if( is_null($this->activated_at) )
        {
            return Lang::get('alba::user.messages.never_activated');
        }

        $date = new Carbon($this->activated_at);
        return $date->diffForHumans();
    }

    /**
     * Returns the number of minutes since the password was updated
     *
     * @return integer
     */
    public function getTimeSinceLastPasswordUpdateAttribute()
    {
        // Short circuit for users who haven't set a password yet
        if( is_null($this->password) || is_null($this->password_updated_at))
        {
            return Lang::get('alba::user.messages.never_set_password');
        }

        $date = new Carbon($this->password_updated_at);
        return $date->diffForHumans();
    }

    /**
     * Checks if this user is allowed to login. Currently must be active 
     * and not blocked to be able to login
     * 
     * @return boolean
     */
    public function isLoginAllowed()
    {
        return $this->active && $this->password && !$this->blocked;
    }

    /**
     * Checks if activation is allowed. Currently it must be not blocked to do so.
     * 
     * @return boolean
     */
    public function isActivationAllowed()
    {
        return !$this->active && !$this->blocked;
    }

    /**
     * Checks if the password can be reset
     * 
     * @return boolean
     */
    public function isPasswordResetAllowed()
    {
        return $this->active && !$this->blocked;
    }

    /**
     * Activates the user
     *
     * @return boolean
     */
    public function activate()
    {
        return $this->setActive(true);
    }

    /**
     * Deactivates the user
     *
     * @return boolean
     */
    public function deactivate()
    {
        return $this->setActive(false);
    }

    /**
     * Save active status
     *
     * @param boolean $active status
     * @param Carbon $time of activated_at
     * @return boolean
     */
    public function setActive($active = true)
    {
        $this->active = $active;
        $this->activated_at = $active ? Carbon::now() : DB::raw('NULL');
        $rules = $this->rulesForActivating;
        if( !$active )
        {
            $rules = array_except($rules, ['activated_at']);
        }
        return $this->save($rules);
    }

    /**
     * Blocks the user
     *
     * @return boolean
     */
    public function block()
    {
        return $this->setBlocked(true);
    }

    /**
     * Unblocks the user
     *
     * @return boolean
     */
    public function unblock()
    {   
        return $this->setBlocked(false);
    }

    /**
     * Save blocked status
     *
     * @param boolean $blocked status
     * @return boolean
     */
    public function setBlocked($blocked = true)
    {
        $this->blocked = $blocked;
        return $this->save($this->rulesForBlocking);
    }

    /**
     * Saves the model with a new password.
     *
     * @param array $newPassword
     * @return boolean
     */
    public function savePassword($newPassword)
    {
        $this->fill($newPassword);
        $this->password_updated_at = Carbon::now();
        return $this->save($this->rulesForUpdatingPassword);
    }

    /**
     * Set the password to null
     *
     * @return boolean
     */
    public function resetPassword()
    {
        $this->autoHashPasswordAttributes = false;
        $this->password = DB::raw('NULL');
        $this->password_updated_at = DB::raw('NULL');
        return $this->forceSave();
    }

}