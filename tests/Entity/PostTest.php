<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\Post
 *
 * @uses \App\Entity\Comment
 */
class PostTest extends TestCase
{
    /**
     * @group unit
     */
    public function testCanAddComment(): void
    {
        $post = new Post();

        $this->assertCount(0, $post->getComments());

        $comment = new Comment();
        $post->addComment($comment);

        $this->assertCount(1, $post->getComments());

        $this->assertSame($comment, $post->getComments()->first());
    }

    /**
     * @group unit
     */
    public function testCommentReferencesOwnerPostWhenAddingComment(): void
    {
        $post = new Post();
        $comment = new Comment();

        $this->assertNotSame($post, $comment->getPost());

        $post->addComment($comment);

        $this->assertSame($post, $comment->getPost());
    }

    /**
     * @group unit
     */
    public function testCommentIgnoredIfAlreadyExistsWhenAddingComment(): void
    {
        // Postula de depart
        $post = new Post();
        $comment = new Comment();
        $post->addComment($comment);

        // Valide le postula de depart
        $this->assertCount(1, $post->getComments());

        // j'effectue mon action
        $post->addComment($comment);

        // je verifie les impacts de mon action
        $this->assertCount(1, $post->getComments());
    }
}
