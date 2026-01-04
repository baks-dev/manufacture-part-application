<?php

declare(strict_types=1);

namespace BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEvent;
use BaksDev\Manufacture\Part\Application\Entity\ManufactureApplication;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationDTO;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationHandler;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


#[Group('manufacture-part-application')]
#[When(env: 'test')]
final class ManufactureApplicationHandlerTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $main = $em->getRepository(ManufactureApplication::class)
            ->findOneBy(['id' => ManufactureApplicationUid::TEST]);

        if($main)
        {
            $em->remove($main);
        }

        $event = $em->getRepository(ManufactureApplicationEvent::class)
            ->findBy(['main' => ManufactureApplicationUid::TEST]);

        foreach($event as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
        $em->clear();
    }


    public function testUseCase(): void
    {
        /** @see ManufactureApplicationDTO */
        $ManufactureApplicationDTO = new ManufactureApplicationDTO(new UserProfileUid(UserProfileUid::TEST));

        $ManufactureApplicationProductDTO = $ManufactureApplicationDTO
            ->getProduct();

        $ManufactureApplicationProductDTO
            ->setProduct(new ProductEventUid(ProductEventUid::TEST))
            ->setOffer(new ProductOfferUid(ProductOfferUid::TEST))
            ->setVariation(new ProductVariationUid(ProductVariationUid::TEST))
            ->setModification(new ProductModificationUid(ProductModificationUid::TEST))
            ->setTotal(10);

        /** @var ManufactureApplicationHandler $ManufactureApplicationHandler */
        $ManufactureApplicationHandler = self::getContainer()->get(ManufactureApplicationHandler::class);
        $handle = $ManufactureApplicationHandler->handle($ManufactureApplicationDTO);

        self::assertTrue(($handle instanceof ManufactureApplication), $handle.': Ошибка ManufactureApplication');

    }


    public function testComplete(): void
    {
        /** @var DBALQueryBuilder $dbal */
        $dbal = self::getContainer()->get(DBALQueryBuilder::class);

        $dbal->createQueryBuilder(self::class);

        $dbal->from(ManufactureApplication::class)
            ->where('id = :id')
            ->setParameter('id', ManufactureApplicationUid::TEST);

        self::assertTrue($dbal->fetchExist());
    }
}