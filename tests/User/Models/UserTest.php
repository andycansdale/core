<?php

use \Mockery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

use Alba\User\Models\User;

class UserTest extends TestCase {
    

    public function setUp() {
        parent::setUp();

        $this->user = new User();
    }


    public function tearDown() {
        Mockery::close();

        parent::tearDown();
    }


    public function testIsLoginAllowed() {
        
        $this->user->blocked = true; //
        $this->user->active = true;
        $this->user->password = 'asdasd';
        $this->assertFalse($this->user->isLoginAllowed());

        $this->user->blocked = false;
        $this->user->active = false; //
        $this->user->password = 'asdasd';
        $this->assertFalse($this->user->isLoginAllowed());

        $this->user->blocked = false;
        $this->user->active = true;
        $this->user->password = null; //
        $this->assertFalse($this->user->isLoginAllowed());

        $this->user->blocked = false;
        $this->user->active = true;
        $this->user->password = 'asdasd';        
        $this->assertTrue($this->user->isLoginAllowed());        

    }


    public function testGetFullName() {
        
        $nameMock = Mockery::mock('Alba\User\Models\Name');        
        $this->user->name = $nameMock;
        
        $expect = 'My Nice Name';
        $nameMock->shouldReceive('getFullName')->once()->andReturn($expect);
        $this->assertEquals('My Nice Name', $this->user->getFullName());

    }


    //Tests for method isRequestActivationAllowed()
    
    public function testIsRequestActivationAllowedWhenActive() {
        $this->user->active = true;
        $this->assertFalse($this->user->isRequestActivationAllowed());
    }


    public function testIsRequestActivationAllowedWhenBlocked() {
        $this->user->blocked = true;
        $this->assertFalse($this->user->isRequestActivationAllowed());   
    }


    public function testIsRequestActivationAllowedWhenOk() {
        $this->user->active = false;
        $this->user->blocked = false;
        $this->assertTrue($this->user->isRequestActivationAllowed());
    }


    //Tests for method IsActivateAllowed()

    public function testIsActivateAllowedWhenRequestNotAllowed() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestActivationAllowed]');
        $user->shouldReceive('isRequestActivationAllowed')->once()->andReturn(false);
        $user->activation_token = 'token';
        $user->activation_token_created_at = new Carbon();

        $this->assertFalse($user->isActivateAllowed('token', 24));

    }


    public function testIsActivateAllowedWhenTokenDifferent() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestActivationAllowed]');
        $user->shouldReceive('isRequestActivationAllowed')->once()->andReturn(true);        
        $user->activation_token = 'token';
        $user->activation_token_created_at = new Carbon();

        $this->assertFalse($user->isActivateAllowed('tokendifferent', 24));
    }

    
    public function testIsActivateAllowedWhenTtlExpired() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestActivationAllowed]');
        $user->shouldReceive('isRequestActivationAllowed')->once()->andReturn(true);        
        $user->activation_token = 'token';
        $twoDaysAgo = new Carbon();
        $twoDaysAgo->subDays(2);
        $user->activation_token_created_at = $twoDaysAgo;

        $this->assertFalse($user->isActivateAllowed('token', 24));

    }


    public function testIsActivateAllowedWhenOk() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestActivationAllowed]');
        $user->shouldReceive('isRequestActivationAllowed')->once()->andReturn(true);
        $user->activation_token = 'token';
        $user->activation_token_created_at = new Carbon();
        
        $this->assertTrue($user->isActivateAllowed('token', 24));

    }


    //Test for method generateActivationToken() 
    
    public function testGenerateActivationTokenChangesAttributesCorrectly() {

        $user = Mockery::mock('Alba\User\Models\User[saveUpdate]');
        $user->activation_token = null;
        $user->activation_token_created_at = null;
        $user->shouldReceive('saveUpdate')->once();
        
        $token = $user->generateActivationToken();
        $this->assertNotNull($token);
        $this->assertNotNull($user->activation_token);
        $this->assertNotNull($user->activation_token_created_at);

    }


    //Tests for method activate()
    
    public function testActivateChangesAttributesCorrectly() {

        $user = Mockery::mock('Alba\User\Models\User[isActivateAllowed,saveUpdate]');
        $user->shouldReceive('isActivateAllowed')->once()->andReturn(true);
        $user->active = false;
        $user->activated_at = null;
        $user->activation_token = 'token';
        $user->activation_token_created_at = new Carbon();
        $user->password = null;
        $user->password_updated_at = null;        
        $user->shouldReceive('saveUpdate')->once();

        $actual = $user->activate('token', 'password', 24);
        $this->assertTrue($actual);

        $this->assertTrue($user->active);
        $this->assertNotNull($user->activated_at);
        $this->assertNull($user->activation_token);
        $this->assertNull($user->activation_token_created_at);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($user->password_updated_at);

    }


    //Tests mehod for method deactivate()
    
    public function testDeactivateWhenUserAlreadyDeactivated() {
        
        $user = Mockery::mock('Alba\User\Models\User[saveUpdate]');
        $user->active = false;
        $user->activated_at = null;
        $user->shouldReceive('saveUpdate')->never();

        $user->deactivate();
        $this->assertFalse($user->active);
        $this->assertNull($user->activated_at);

    }


    public function testDeactivateChangesAttributesCorrectly() {

        $user = Mockery::mock('Alba\User\Models\User[saveUpdate]');
        $user->active = true;
        $whenActual = Carbon::create(2000, 01, 01, 0, 0, 0);
        $whenNotExpect = Carbon::create(2000, 01, 01, 0, 0, 0); //Carbon doesn't implement the __clone() method right!
        $user->activated_at = $whenActual;
        $user->shouldReceive('saveUpdate')->once();

        $user->deactivate();
        $this->assertFalse($user->active);
        $this->assertNotEquals($whenNotExpect, $user->activated_at);
    }


    //Tests for method isRequestPasswordResetAllowed() 

    public function testIsRequestPasswordResetAllowedWhenBlocked() {
        $this->user->blocked = true;
        $this->assertFalse($this->user->isRequestPasswordResetAllowed());
    }


    public function testIsRequestPasswordResetAllowedWhenDeactivated() {
        $this->user->active = false;
        $this->assertFalse($this->user->isRequestPasswordResetAllowed());
    }


    public function testIsRequestPasswordResetAllowedWhenOk() {
        $this->user->active = true;
        $this->user->blocked = false;
        $this->assertTrue($this->user->isRequestPasswordResetAllowed());
    }


    //Test for method generatePasswordResetToken() 
    
    public function testGeneratePasswordResetTokenChangesAttributesCorrectly() {

        $user = Mockery::mock('Alba\User\Models\User[saveUpdate]');
        $user->password_reset_token = null;
        $user->password_reset_token_created_at = null;
        $user->shouldReceive('saveUpdate')->once();
        
        $token = $user->generatePasswordResetToken();
        $this->assertNotNull($token);
        $this->assertNotNull($user->password_reset_token);
        $this->assertNotNull($user->password_reset_token_created_at);

    }


    //Tests for method isPasswordResetAllowed()
    
    public function testIsPasswordResetAllowedWhenRequestNotAllowed() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestPasswordResetAllowed]');
        $user->shouldReceive('isRequestPasswordResetAllowed')->once()->andReturn(false);
        $user->email = 'email@domain.com';
        $user->password_reset_token = 'token';
        $user->password_reset_token_created_at = new Carbon();

        $this->assertFalse($user->isPasswordResetAllowed('token', 'email@domain.com', 24));

    }


    public function testIsPasswordResetAllowedWhenEmailDifferent() { 

        $user = Mockery::mock('Alba\User\Models\User[isRequestPasswordResetAllowed]');
        $user->shouldReceive('isRequestPasswordResetAllowed')->once()->andReturn(true);
        $user->email = 'email@domain.com';
        $user->password_reset_token = 'token';
        $user->password_reset_token_created_at = new Carbon();

        $this->assertFalse($user->isPasswordResetAllowed('token', 'emaildiff@domain.com', 24));

    }


    public function testIsPasswordResetAllowedWhenTokenDifferent() { 

        $user = Mockery::mock('Alba\User\Models\User[isRequestPasswordResetAllowed]');
        $user->shouldReceive('isRequestPasswordResetAllowed')->once()->andReturn(true);
        $user->email = 'email@domain.com';
        $user->password_reset_token = 'token';
        $user->password_reset_token_created_at = new Carbon();

        $this->assertFalse($user->isPasswordResetAllowed('tokendifferent', 'email@domain.com', 24));

    }


    public function testIsPasswordResetAllowedWhenTtlExpired() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestPasswordResetAllowed]');
        $user->shouldReceive('isRequestPasswordResetAllowed')->once()->andReturn(true);
        $user->email = 'email@domain.com';
        $user->password_reset_token = 'token';
        $twoDaysAgo = new Carbon();
        $twoDaysAgo->subDays(2);
        $user->password_reset_token_created_at = $twoDaysAgo;

        $this->assertFalse($user->isPasswordResetAllowed('token', 'email@domain.com', 24));

    }


    public function testIsPasswordResetAllowedWhenOk() {

        $user = Mockery::mock('Alba\User\Models\User[isRequestPasswordResetAllowed]');
        $user->shouldReceive('isRequestPasswordResetAllowed')->once()->andReturn(true);
        $user->email = 'email@domain.com';
        $user->password_reset_token = 'token';
        $user->password_reset_token_created_at = new Carbon();

        $this->assertTrue($user->isPasswordResetAllowed('token', 'email@domain.com', 24));

    }


    //Test for method resetPassword
    
    public function testResetPasswordWhenNotAllowed() {

        $user = Mockery::mock('Alba\User\Models\User[isPasswordResetAllowed]');
        $user->shouldReceive('isPasswordResetAllowed')->once()->andReturn(false);

        $this->assertFalse($user->resetPassword('token', 'email@domain.com', 'newpass', 24));

    }


    public function testResetPasswordChangesAttributesCorrectly() {

        $user = Mockery::mock('Alba\User\Models\User[isPasswordResetAllowed,saveUpdate]');
        $user->shouldReceive('isPasswordResetAllowed')->once()->andReturn(true);
        $oldPass = 'oldPass';
        $newPass = 'newPass';
        $user->password = $oldPass;
        $user->last_pass_update_at = null;
        $user->password_reset_token = 'token';
        $user->password_reset_token_created_at = new Carbon();
        $user->shouldReceive('saveUpdate')->once();

        $this->assertTrue($user->resetPassword('token', 'email@domain.com', $newPass, 24));
        $this->assertTrue(Hash::check($newPass, $user->password));
        $this->assertNotNull($user->last_pass_update_at);
        $this->assertNull($user->password_reset_token);
        $this->assertNull($user->password_reset_token_created_at);

    }


}