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

namespace BaksDev\Manufacture\Part\Application\Repository\AllManufacturePartApplication;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEvent;
use BaksDev\Manufacture\Part\Application\Entity\ManufactureApplication;
use BaksDev\Manufacture\Part\Application\Entity\Product\ManufactureApplicationProduct;
use BaksDev\Manufacture\Part\Application\Forms\ManufactureApplicationFilter\Admin\ManufactureApplicationFilterDTO;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\ManufactureApplicationStatusNew;
use BaksDev\Manufacture\Part\Repository\OpenManufacturePart\OpenManufacturePartResult;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Trans\CategoryProductOffersTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\Trans\CategoryProductModificationTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\Trans\CategoryProductVariationTrans;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\UsersTable\Entity\Actions\Trans\UsersTableActionsTrans;

/**
 * Получение произв-ных заявок
 */
final class AllManufacturePartApplicationRepository implements AllManufacturePartApplicationInterface
{
    private ?SearchDTO $search = null;
    private ?OpenManufacturePartResult $opens = null;

    private ?ManufactureApplicationFilterDTO $filter = null;

    private bool $has_offer = true;
    private bool $has_variation = true;
    private bool $has_modification = true;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function filter(ManufactureApplicationFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function getOpens(): ?OpenManufacturePartResult
    {
        return $this->opens;
    }

    public function setOpens(OpenManufacturePartResult|false|null $opens): self
    {

        /* Если есть открытая произв. партия */
        if ($opens instanceof OpenManufacturePartResult)
        {
            $this->opens = $opens;

            $this->has_offer = $opens->getActionsOfferOffer();
            $this->has_variation = $opens->getActionsOfferVariation();
            $this->has_modification = $opens->getActionsOfferModification();
        }

        return $this;
    }


    public function findPaginator(): PaginatorInterface
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
            ->addSelect('manufacture_application_event.status')
            ->leftJoin(
                'manufacture_application',
                ManufactureApplicationEvent::class,
                'manufacture_application_event',
                'manufacture_application_event.id = manufacture_application.event'
            );

        /* Статус заявки - по умолчанию показать только новые */
        $dbal->andWhere('manufacture_application_event.status = :status');
        $dbal->setParameter('status', $this->filter?->getStatus() ?? ManufactureApplicationStatusNew::STATUS);


        $dbal
            ->addSelect('manufacture_application_product.product as product_uid')
            ->addSelect('manufacture_application_product.total as product_total')
            ->addSelect('manufacture_application_product.completed as product_total_completed')
            ->leftJoin(
                'manufacture_application_event',
                ManufactureApplicationProduct::class,
                'manufacture_application_product',
                'manufacture_application_event.id = manufacture_application_product.event'
            );

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'manufacture_application_product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = manufacture_application_product.product AND product_trans.local = :local',
            );

        $dbal
            ->addSelect('product_info.article AS product_article')
            ->leftJoin(
                'manufacture_application_product',
                ProductInfo::class,
                'product_info',
                'product_info.event = manufacture_application_product.product',
            );


        /* Торговое предложение */

        /* Проверить указано ли сво-во offer в в процессе производства (action) */
        if($this->has_offer)
        {

            $dbal
                ->addSelect('product_offer.id as product_offer_uid')
                ->addSelect('product_offer.value as product_offer_value')
                ->addSelect('product_offer.postfix as product_offer_postfix')
                ->leftJoin(
                    'manufacture_application_product',
                    ProductOffer::class,
                    'product_offer',
                    'product_offer.id = manufacture_application_product.offer OR product_offer.id IS NULL'
                );

            /* Получаем тип торгового предложения */
            $dbal
                ->addSelect('category_offer.reference AS product_offer_reference')
                ->leftJoin(
                    'product_offer',
                    CategoryProductOffers::class,
                    'category_offer',
                    'category_offer.id = product_offer.category_offer'
                );

            /* Получаем название торгового предложения */
            $dbal
                ->addSelect('category_offer_trans.name as product_offer_name')
                ->addSelect('category_offer_trans.postfix as product_offer_name_postfix')
                ->leftJoin(
                    'category_offer',
                    CategoryProductOffersTrans::class,
                    'category_offer_trans',
                    'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
                );
        }

        /* Проверить указано ли сво-во variation в процессе производства (action) */
        if($this->has_variation)
        {

            /* Множественные варианты торгового предложения */
            $dbal
                ->addSelect('product_variation.id as product_variation_uid')
                ->addSelect('product_variation.value as product_variation_value')
                ->addSelect('product_variation.postfix as product_variation_postfix')
                ->leftJoin(
                    'manufacture_application_product',
                    ProductVariation::class,
                    'product_variation',
                    'product_variation.id = manufacture_application_product.variation OR product_variation.id IS NULL '
                );

            /* Получаем тип множественного варианта */
            $dbal
                ->addSelect('category_variation.reference as product_variation_reference')
                ->leftJoin(
                    'product_variation',
                    CategoryProductVariation::class,
                    'category_variation',
                    'category_variation.id = product_variation.category_variation'
                );

            /* Получаем название множественного варианта */
            $dbal
                ->addSelect('category_variation_trans.name as product_variation_name')
                ->addSelect('category_variation_trans.postfix as product_variation_name_postfix')
                ->leftJoin(
                    'category_variation',
                    CategoryProductVariationTrans::class,
                    'category_variation_trans',
                    'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local'
                );
        }

        /* Модификация множественного варианта торгового предложения */

        /* Проверить указано ли сво-во modification в процессе производства (action) */
        if($this->has_modification)
        {

            $dbal
                ->addSelect('product_modification.id as product_modification_uid')
                ->addSelect('product_modification.value as product_modification_value')
                ->addSelect('product_modification.postfix as product_modification_postfix')
                ->leftJoin(
                    'manufacture_application_product',
                    ProductModification::class,
                    'product_modification',
                    'product_modification.id = manufacture_application_product.modification OR product_modification.id IS NULL '
                );

            /* Получаем тип модификации множественного варианта */
            $dbal
                ->addSelect('category_modification.reference as product_modification_reference')
                ->leftJoin(
                    'product_modification',
                    CategoryProductModification::class,
                    'category_modification',
                    'category_modification.id = product_modification.category_modification'
                );

            /* Получаем название типа модификации */
            $dbal
                ->addSelect('category_modification_trans.name as product_modification_name')
                ->addSelect('category_modification_trans.postfix as product_modification_name_postfix')
                ->leftJoin(
                    'category_modification',
                    CategoryProductModificationTrans::class,
                    'category_modification_trans',
                    'category_modification_trans.modification = category_modification.id AND category_modification_trans.local = :local'
                );
        }

        /* Фото продукта */

        /* Проверить указано ли сво-во modification в процессе производства (action) */
        if($this->has_modification)
        {

            $dbal->leftJoin(
                'product_modification',
                ProductModificationImage::class,
                'product_modification_image',
                '
                product_modification_image.modification = product_modification.id AND
                product_modification_image.root = true
			',
            );

        }

        /* Проверить указано ли сво-во variation в процессе производства (action) */
        if($this->has_variation)
        {
            $dbal->leftJoin(
                'product_offer',
                ProductVariationImage::class,
                'product_variation_image',
                '
                product_variation_image.variation = product_variation.id AND
                product_variation_image.root = true
			',
            );
        }


        /* Задать условия для св-в product_variation_image.name таблицы  в зависимости от того указано ли сво-во variation в процессе производства (action) */
        $product_variation_image_cond = $this->has_offer && $this->has_variation ? 'product_variation_image.name IS NULL AND ' : '';

        if($this->has_offer)
        {

            $dbal->leftJoin(
                'product_offer',
                ProductOfferImage::class,
                'product_offer_images',

                $product_variation_image_cond.
                'product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			',
            );

        }


        $dbal->leftJoin(
            'manufacture_application_product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = manufacture_application_product.product AND
            product_photo.root = true
        ');


        /* Задать условия для формирования поля 'product_image' для: */
        /* product_modification_image.name, product_variation_image.name, product_offer_images.name */
        /* в зависимости от того указано ли соотвествующее сво-во: modification, offer,variation в процессе производства (action) */
        $modification_image_expr = $this->has_modification ?
            "WHEN product_modification_image.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name)" : '';

        $variation_image_expr = $this->has_variation ?
            "WHEN product_variation_image.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)" : '';

        $offer_image_expr = $this->has_offer ?
            "WHEN product_offer_images.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)" : '';


        $dbal->addSelect("
                CASE
    
                    $modification_image_expr
                    $variation_image_expr
                    $offer_image_expr
                    WHEN product_photo.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
                    ELSE NULL
                   
                END AS product_image
            ");


        /* Расширение файла */

        /* Задать условия для формирования полей 'product_image_ext' и 'product_image_cdn' для: */
        /* product_modification_image.ext, product_variation_image.ext, product_offer_images.ext */
        /* в зависимости от того указано ли соотвествующее сво-во: modification, offer,variation в процессе производства (action) */
        $modification_image_ext = $this->has_modification ? 'product_modification_image.ext,' : '';

        $variation_image_ext = $this->has_variation ? 'product_variation_image.ext,' : '';

        $offer_image_ext = $this->has_offer ? 'product_offer_images.ext,' : '';

        $dbal->addSelect(
            '
            COALESCE('.
            $modification_image_ext .
            $variation_image_ext .
            $offer_image_ext .
            'product_photo.ext
            ) AS product_image_ext',
        );


        $modification_image_cdn = $this->has_modification ? 'product_modification_image.cdn,' : '';

        $variation_image_cdn = $this->has_variation ? 'product_variation_image.cdn,' : '';

        $offer_image_cdn = $this->has_offer ? 'product_offer_images.cdn,' : '';

        $dbal->addSelect(
            '
            COALESCE('.
            $modification_image_cdn .
            $variation_image_cdn .
            $offer_image_cdn .
            'product_photo.cdn
            ) AS product_image_cdn',
        );


        /**
         * Производственный процесс
         */
        $dbal
            ->addSelect('action_trans.name AS action_name')
            ->leftJoin(
                'manufacture_application_event',
                UsersTableActionsTrans::class,
                'action_trans',
                'action_trans.event = manufacture_application_event.action AND action_trans.local = :local'
            );

        /** Ответственное лицо (Профиль пользователя) */

        $dbal->leftJoin(
            'manufacture_application_event',
            UserProfile::class,
            'users_profile',
            'users_profile.id = manufacture_application_event.fixed'
        );

        $dbal
            ->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = users_profile.event'
            );


        if($this->search?->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search, true)
                ->addSearchEqualUid('manufacture_application.id')
                ->addSearchEqualUid('manufacture_application.event')
                ->addSearchEqualUid('manufacture_application_product.id')
                ->addSearchEqualUid('product_variation.id')
                ->addSearchEqualUid('product_modification.id')
                ->addSearchLike('product_trans.name');

        }

        $dbal->orderBy('manufacture_application_event.priority', 'DESC');
        $dbal->addOrderBy('manufacture_application.id',  'ASC'); // desc

        $dbal->allGroupByExclude();


        return $this->paginator->fetchAllHydrate($dbal, AllManufacturePartApplicationResult::class);

    }

}