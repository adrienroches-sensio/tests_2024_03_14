<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use App\EventSubscriber\CommentNotificationSubscriber;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \App\EventSubscriber\CommentNotificationSubscriber
 */
class CommentNotificationSubscriberTest extends TestCase
{
    use ProphecyTrait;

    public function testEmailIsSentWhenCommentIsCreatedToPostAuthor(): void
    {
        $sender = 'fake.sender@example.com';

        $post = new Post();
        $author = new User();
        $author->setEmail('fake.author@example.com');

        $post->setAuthor($author);

        $comment = new Comment();
        $post->addComment($comment);

        $event = new CommentCreatedEvent($comment);

        $mailer = $this->prophesize(MailerInterface::class);

        $mailer
            ->send(Argument::that(function ($email) use ($sender): bool {
                if (!$email instanceof Email) {
                    return false;
                }

                if ($email->getFrom()[0]->getAddress() !== $sender) {
                    return false;
                }

                if ($email->getTo()[0]->getAddress() !== 'fake.author@example.com') {
                    return false;
                }

                if ($email->getSubject() !== 'comment_created') {
                    return false;
                }

                if ($email->getHtmlBody() !== 'comment_created.description') {
                    return false;
                }

                return true;
            }))
            ->shouldBeCalledOnce()
        ;

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator
            ->generate(
                Argument::is('blog_post'),
                Argument::type('array'),
                Argument::is(UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->shouldBeCalledOnce()
            ->willReturn('http://example.com/blog/post')
        ;

        $translator = $this->prophesize(TranslatorInterface::class);

        $translator
            ->trans(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn('comment_created')
        ;
        $translator
            ->trans(Argument::any(), Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn('comment_created.description')
        ;

        $commentNotificationSubscriber = new CommentNotificationSubscriber(
            $mailer->reveal(),
            $urlGenerator->reveal(),
            $translator->reveal(),
            $sender
        );

        $commentNotificationSubscriber->onCommentCreated($event);
    }
}
