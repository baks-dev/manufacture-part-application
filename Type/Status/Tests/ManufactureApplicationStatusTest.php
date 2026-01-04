<?php

namespace BaksDev\Manufacture\Part\Application\Type\Status\Tests;

use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\Collection\ManufactureApplicationStatusCollection;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatusType;
use BaksDev\Manufacture\Part\Type\Status\ManufacturePartStatus;
use BaksDev\Manufacture\Part\Type\Status\ManufacturePartStatus\Collection\ManufacturePartStatusCollection;
use BaksDev\Manufacture\Part\Type\Status\ManufacturePartStatusType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('manufacture-part-application')]
#[When(env: 'test')]
final class ManufactureApplicationStatusTest
    extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var ManufactureApplicationStatusCollection $ManufactureApplicationStatusCollection */
        $ManufactureApplicationStatusCollection = self::getContainer()->get(ManufactureApplicationStatusCollection::class);

        foreach($ManufactureApplicationStatusCollection->cases() as $case)
        {
            $ManufactureApplicationStatus = new ManufactureApplicationStatus($case->getValue());

            self::assertTrue($ManufactureApplicationStatus->equals($case::class)); // немспейс интерфейса
            self::assertTrue($ManufactureApplicationStatus->equals($case)); // объект интерфейса
            self::assertTrue($ManufactureApplicationStatus->equals($case->getValue())); // срока
            self::assertTrue($ManufactureApplicationStatus->equals($ManufactureApplicationStatus)); // объект класса

            $ManufactureApplicationStatusType = new ManufactureApplicationStatusType();
            $platform = $this
                ->getMockBuilder(AbstractPlatform::class)
                ->getMock();


            $convertToDatabase = $ManufactureApplicationStatusType->convertToDatabaseValue($ManufactureApplicationStatus, $platform);
            self::assertEquals($ManufactureApplicationStatus->getManufactureApplicationStatusValue(), $convertToDatabase);

            $convertToPHP = $ManufactureApplicationStatusType->convertToPHPValue($convertToDatabase, $platform);
            self::assertInstanceOf(ManufactureApplicationStatus::class, $convertToPHP);
            self::assertEquals($case, $convertToPHP->getManufactureApplicationStatus());

        }

    }
}