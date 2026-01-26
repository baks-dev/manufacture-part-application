<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Manufacture\Part\Application\Repository\ManufactureApplicationProduct;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEvent;
use BaksDev\Manufacture\Part\Application\Entity\ManufactureApplication;
use BaksDev\Manufacture\Part\Application\Entity\Product\ManufactureApplicationProduct;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\ManufactureApplicationStatusNew;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

/**
 * Используется для получения ManufactureApplicationProduct по product_event, offer, variation, modification
 */
final class ManufactureApplicationProductRepository implements ManufactureApplicationProductInterface
{
    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function findApplicationProduct(
        string|ProductEventUid $product,
        string|ProductOfferUid|false $offer,
        string|ProductVariationUid|false $variation,
        string|ProductModificationUid|false $modification,
    ): ManufactureApplicationProductResult|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('manufacture_application.id')
            ->addSelect('manufacture_application.event')
            ->from(ManufactureApplication::class, 'manufacture_application');

        $dbal
            ->addSelect('manufacture_application_event.priority')
            ->leftJoin(
                'manufacture_application',
                ManufactureApplicationEvent::class,
                'manufacture_application_event',
                'manufacture_application_event.id = manufacture_application.event',
            );

        $dbal
            ->addSelect('manufacture_application_product.product as product_product_uid')
            ->addSelect('manufacture_application_product.total as product_total')
            ->addSelect('manufacture_application_product.completed as product_total_completed')
            ->leftJoin(
                'manufacture_application_event',
                ManufactureApplicationProduct::class,
                'manufacture_application_product',
                'manufacture_application_event.id = manufacture_application_product.event',
            );


        $dbal->where('manufacture_application_product.product=:product')
            ->setParameter('product', $product, ProductEventUid::TYPE);

        /* Выбираем товары только со статусом new */
        $dbal
            ->andWhere('manufacture_application_event.status = :status')
            ->setParameter('status', ManufactureApplicationStatusNew::STATUS);


        if($offer !== false)
        {
            $dbal
                ->andWhere('manufacture_application_product.offer = :offer')
                ->setParameter('offer', $offer, ProductOfferUid::TYPE);
        }

        if($variation !== false)
        {
            $dbal
                ->andWhere('manufacture_application_product.variation = :variation')
                ->setParameter('variation', $variation, ProductVariationUid::TYPE);
        }

        if($modification !== false)
        {
            $dbal
                ->andWhere('manufacture_application_product.modification = :modification')
                ->setParameter('modification', $modification, ProductModificationUid::TYPE);
        }

        //        dump($dbal->getSQL());

        /* Сортируем по приоритету и по event (uuid) */
        $dbal->orderBy('manufacture_application_event.priority', 'DESC');
        $dbal->addOrderBy('manufacture_application.id', 'DESC');

        return $dbal->fetchHydrate(ManufactureApplicationProductResult::class);

    }

}