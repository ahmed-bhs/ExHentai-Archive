<?php

namespace App\Tests\Command;

use App\Command\ExhentaiDailyBonusCommand;
use App\Entity\ExhentaiGallery;
use App\Model\GalleryPage;
use App\Service\ExHentaiBrowserService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExhentaiDailyBonusCommandTest extends KernelTestCase
{
    /**
     * @var MockObject|ExHentaiBrowserService
     */
    private $browser;

    /**
     * @var MockObject|ExhentaiGallery
     */
    private $gallery;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @test
     */
    public function willReturnExitcode0IfSuccess()
    {
        $this->setExpects(file_get_contents(__DIR__ . '/../stubs/gallery-single-dawn-of-new-day.html'));

        $this->commandTester->execute([
            'command' => 'exhentai:daily-bonus'
        ]);

        $this->assertContains('Collected 104946 EXP, 10010 Credits, 10000 GP and 8 Hath', $this->commandTester->getDisplay());
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function willReturnExitCode1IfHtmlContainsNoBonus()
    {
        $this->setExpects(file_get_contents(__DIR__ . '/../stubs/gallery-single.html'));

        $this->commandTester->execute([
            'command' => 'exhentai:daily-bonus'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function willReturnExitCode1IfHtmlContainsHentaiVerseInsteadOfChallenge()
    {
        $this->setExpects(file_get_contents(__DIR__ . '/../stubs/gallery-single-hentaiverse-challenge.html'));

        $this->commandTester->execute([
            'command' => 'exhentai:daily-bonus'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function willReturnExitCode2IfNoGalleriesAreFound()
    {
        $this->browser->expects($this->once())
            ->method('getIndex')
            ->willReturn([]);

        $this->commandTester->execute([
            'command' => 'exhentai:daily-bonus'
        ]);

        $this->assertEquals(2, $this->commandTester->getStatusCode());
    }

    protected function setUp()
    {
        $this->browser = $this->getMockBuilder(ExHentaiBrowserService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gallery = $this->getMockBuilder(ExhentaiGallery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add(new ExhentaiDailyBonusCommand('exhentai:daily-bonus', $this->browser));

        $command = $application->find('exhentai:daily-bonus');
        $this->commandTester = new CommandTester($command);

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    private function setExpects($html)
    {
        $this->browser->expects($this->once())
            ->method('getIndex')
            ->willReturn([
                (new GalleryPage())
                    ->setId(1)
                    ->setToken('abc'),
                (new GalleryPage())
                    ->setId(1)
                    ->setToken('abc'),
                (new GalleryPage())
                    ->setId(1)
                    ->setToken('abc'),
            ]);

        $this->gallery->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->gallery->expects($this->any())
            ->method('getToken')
            ->willReturn('abc');

        $this->browser->expects($this->once())
            ->method('getIndex')
            ->willReturn([
                $this->gallery
            ]);

        $this->browser->expects($this->any())
            ->method('get')
            ->with(
                $this->stringContains(ExHentaiBrowserService::SAFE_URL . 'g/1/abc/')
            )
            ->willreturn($html);
    }
}
