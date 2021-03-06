<?php

namespace andytan07\LaravelSesTracker;

use Ramsey\Uuid\Uuid;
use andytan07\LaravelSesTracker\Models\EmailOpen;
use andytan07\LaravelSesTracker\Models\SentEmail;
use andytan07\LaravelSesTracker\Models\EmailLink;
use PHPHtmlParser\Dom;

class MailProcessor
{
    protected $emailBody;
    protected $batch;
    protected $sentEmail;

    public function __construct(SentEmail $sentEmail, $emailBody)
    {
        $this->setEmailBody($emailBody);
        $this->setSentEmail($sentEmail);
    }

    public function getEmailBody()
    {
        return $this->emailBody;
    }

    private function setEmailBody($body): void
    {
        $this->emailBody = $body;
    }

    private function setSentEmail(SentEmail $email): void
    {
        $this->sentEmail = $email;
    }

    public function openTracking(): MailProcessor
    {
        $beaconIdentifier = Uuid::uuid4()->toString();
        $beaconUrl = rtrim(config('app.url'), '/') . "/laravel-ses/beacon/$beaconIdentifier";

        EmailOpen::create([
            'sent_email_id' => $this->sentEmail->id,
            'email' => $this->sentEmail->email,
            'batch' => $this->sentEmail->batch,
            'beacon_identifier' => $beaconIdentifier,
            'url' => $beaconUrl,
        ]);

        $this->setEmailBody($this->getEmailBody() . "<img src=\"$beaconUrl\"" . ' alt="" style="width:1px;height:1px;"/>');
        return $this;
    }

    public function linkTracking(): MailProcessor
    {
        $dom = new Dom;
        $dom->load($this->getEmailBody());
        $anchors = $dom->find('a');
        foreach ($anchors as $anchor) {
            $originalUrl = $anchor->getAttribute('href');

            // set tracking url only if the anchor tag has href
            if ($originalUrl) {
                $anchor->setAttribute('href', $this->createAppLink($originalUrl));
            }
        }

        $this->setEmailBody($dom->innerHtml);

        return $this;
    }

    private function createAppLink(string $originalUrl): string
    {
        $linkIdentifier = Uuid::uuid4()->toString();

        $link = EmailLink::create([
            'sent_email_id' => $this->sentEmail->id,
            'batch' => $this->sentEmail->batch,
            'link_identifier' => $linkIdentifier,
            'original_url' => $originalUrl
        ]);

        return rtrim(config('app.url'), '/') . "/laravel-ses/link/$linkIdentifier";
    }
}