<?php

declare(strict_types=1);

namespace BaksDev\Manufacture\Part\Application\UseCase\Admin\Completed\Tests;

use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEvent;
use BaksDev\Manufacture\Part\Application\Entity\ManufactureApplication;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\Tests\ManufactureApplicationHandlerTest;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\Completed\ManufactureApplicationCompletedDTO;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\Completed\ManufactureApplicationCompletedHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


#[Group('manufacture-part-application')]
#[When(env: 'test')]
final class ManufactureApplicationCompletedHandlerTest extends KernelTestCase
{
    #[DependsOnClass(ManufactureApplicationHandlerTest::class)]
    public function testUseCase(): void
    {


        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');


        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $ManufactureApplication = $em->getRepository(ManufactureApplication::class)
            ->find(ManufactureApplicationUid::TEST);

        $ManufactureApplicationEvent = $em
            ->getRepository(ManufactureApplicationEvent::class)
            ->find($ManufactureApplication->getEvent());


        /** @see ManufactureApplicationDTO */
        $ManufactureApplicationCompletedDTO = new ManufactureApplicationCompletedDTO();
        $ManufactureApplicationEvent->getDto($ManufactureApplicationCompletedDTO);


        /** @var ManufactureApplicationCompletedHandler $ManufactureApplicationCompletedHandler */
        $ManufactureApplicationCompletedHandler = self::getContainer()->get(ManufactureApplicationCompletedHandler::class);
        $handle = $ManufactureApplicationCompletedHandler->handle($ManufactureApplicationCompletedDTO);

        self::assertTrue(($handle instanceof ManufactureApplication), $handle.': Ошибка ManufactureApplication');

    }

}