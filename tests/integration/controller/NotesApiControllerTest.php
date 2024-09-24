<?php
/**
 * ownCloud - Notes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */

namespace OCA\Notes\Controller;

use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

use OCP\AppFramework\App;
use OCP\Files\File;
use Test\Traits\UserTrait;

class NotesApiControllerTest extends TestCase {
	use UserTrait;

	private $controller;
	private $mapper;
	private $userId = 'test';
	private $notesFolder = '/test/files/Notes';
	private $fs;

	public function setUp(): void {
		$this->createUser($this->userId);

		$app = new App('notes');
		$container = $app->getContainer();
		$container->registerService('UserId', function ($c) {
			return $this->userId;
		});
		$user = $this->getMockBuilder('OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->method('getUID')
			->willReturn($this->userId);

		/** @var IUserSession $userSession */
		$userSession = $container->query('OCP\IUserSession');
		$userSession->setUser($user);
		$this->controller = $container->query(
			'OCA\Notes\Controller\NotesApiController'
		);

		$this->fs = $container->query(
			'OCP\Files\IRootFolder'
		);
		$this->fs->newFolder($this->notesFolder);
	}

	public function testUpdate() {
		$note = $this->controller->create('test')->getData();
		$this->assertEquals('test', $note->getContent());

		$note2 = $this->controller->update($note->getId(), 'test2')->getData();
		$this->assertEquals('test2', $note2->getContent());
		$this->assertEquals($note->getId(), $note2->getId());

		$notes = $this->controller->index()->getData();

		$this->assertCount(1, $notes);
		$this->assertEquals('test2', $notes[0]->getContent());

		$file = $this->fs->get($this->notesFolder . '/test2.txt');

		$this->assertTrue($file instanceof File);
	}

	public function tearDown(): void {
		$this->fs->get($this->notesFolder)->delete();
	}
}
