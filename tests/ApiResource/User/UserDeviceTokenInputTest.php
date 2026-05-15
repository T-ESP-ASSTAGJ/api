<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\User;

use App\ApiResource\User\UserDeviceTokenInput;
use PHPUnit\Framework\TestCase;

class UserDeviceTokenInputTest extends TestCase
{
    public function testCanSetDeviceToken(): void
    {
        $input = new UserDeviceTokenInput();
        $input->deviceToken = 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]';

        $this->assertSame('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $input->deviceToken);
    }

    public function testCanSetFcmToken(): void
    {
        $input = new UserDeviceTokenInput();
        $input->deviceToken = 'fcm_token_abcdefg123456';

        $this->assertSame('fcm_token_abcdefg123456', $input->deviceToken);
    }
}
