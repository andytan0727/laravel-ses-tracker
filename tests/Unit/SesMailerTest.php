<?php

namespace andytan07\LaravelSesTracker\Tests\Unit;

use SesMail;
use andytan07\LaravelSesTracker\Mocking\TestMailable;
use andytan07\LaravelSesTracker\Exceptions\TooManyEmails;
use andytan07\LaravelSesTracker\Models\SentEmail;

class SesMailerTest extends UnitTestCase
{
    public function test()
    {
        SesMail::fake();
        $mail = new TestMailable();
        SesMail::enableAllTracking()
            ->to('oliveready@gmail.com')
            ->send($mail);

        SesMail::assertSent(TestMailable::class);
    }

    public function testExceptionIsThrownWhenTryingToSendToMoreThanOnePerson()
    {
        SesMail::fake();
        $mail = new TestMailable();
        $exceptionThrown = false;

        try {
            SesMail::to(['oliveready@gmail.com', 'something@whatever.com'])->send($mail);
        } catch (TooManyEmails $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testTrackingSettingsAreSetCorrectly()
    {
        SesMail::enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking();

        $this->assertEquals([
            'openTracking' => true,
            'linkTracking' => true,
            'bounceTracking' => true,
            'deliveryTracking' => false,
            'complaintTracking' => false
        ], SesMail::trackingSettings());

        //check that disabling works
        SesMail::disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking();

        $this->assertEquals([
            'openTracking' => false,
            'linkTracking' => false,
            'bounceTracking' => false,
            'deliveryTracking' => false,
            'complaintTracking' => false
        ], SesMail::trackingSettings());

        //check all tracking methods work
        SesMail::enableAllTracking();

        $this->assertEquals([
            'openTracking' => true,
            'linkTracking' => true,
            'bounceTracking' => true,
            'deliveryTracking' => true,
            'complaintTracking' => true
        ], SesMail::trackingSettings());

        SesMail::disableAllTracking();

        $this->assertEquals([
            'openTracking' => false,
            'linkTracking' => false,
            'bounceTracking' => false,
            'deliveryTracking' => false,
            'complaintTracking' => false
        ], SesMail::trackingSettings());
    }
}
