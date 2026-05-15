<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Message;

use App\ApiResource\Message\MessageCreateInput;
use App\ApiResource\Track\TrackInput;
use App\Entity\Enum\MessageTypeEnum;
use PHPUnit\Framework\TestCase;

class MessageCreateInputTest extends TestCase
{
    public function testConstructorWithTextMessage(): void
    {
        $input = new MessageCreateInput(
            conversationId: 1,
            type: MessageTypeEnum::Text,
            content: 'Hello!',
        );

        $this->assertSame(1, $input->conversationId);
        $this->assertSame(MessageTypeEnum::Text, $input->type);
        $this->assertSame('Hello!', $input->content);
        $this->assertNull($input->track);
    }

    public function testConstructorWithMusicMessage(): void
    {
        $track = new TrackInput(
            songId: 'track123',
            title: 'Blinding Lights',
            artistName: 'The Weeknd',
        );
        $input = new MessageCreateInput(
            conversationId: 5,
            type: MessageTypeEnum::Music,
            track: $track,
        );

        $this->assertSame(5, $input->conversationId);
        $this->assertSame(MessageTypeEnum::Music, $input->type);
        $this->assertSame($track, $input->track);
        $this->assertNull($input->content);
    }

    public function testDefaultContentAndTrackAreNull(): void
    {
        $input = new MessageCreateInput(
            conversationId: 1,
            type: MessageTypeEnum::Image,
        );

        $this->assertNull($input->content);
        $this->assertNull($input->track);
    }

    public function testGetGroupSequenceContainsClassAndTypeValue(): void
    {
        $input = new MessageCreateInput(
            conversationId: 1,
            type: MessageTypeEnum::Text,
        );

        $groups = $input->getGroupSequence();

        $this->assertContains(MessageCreateInput::class, $groups);
        $this->assertContains(MessageTypeEnum::Text->value, $groups);
    }
}
