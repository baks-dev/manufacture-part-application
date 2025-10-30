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

use BaksDev\Manufacture\Part\Application\Type\Event\ManufactureApplicationEventUid;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

final class AllManufacturePartApplicationResult
{
    public function __construct(
        private string|null $id,
        private string|null $event,

        private string|null $product_uid,

        private string|null $action_name,

        private string|null $users_profile_username,

        private string|null $product_name,
        private string|null $product_article,

        private ?string $product_image,
        private ?string $product_image_ext,
        private ?bool $product_image_cdn,

        private bool|null $priority,

        private int|null $product_total,
        private int|null $product_total_completed,

        private string|null $status,

        private ?string $product_offer_uid = null,
        private ?string $product_offer_value = null,
        private ?string $product_offer_postfix = null,
        private ?string $product_offer_reference = null,
        private ?string $product_offer_name = null,
        private ?string $product_offer_name_postfix = null,

        private ?string $product_variation_uid = null,
        private ?string $product_variation_value = null,
        private ?string $product_variation_postfix = null,
        private ?string $product_variation_reference = null,
        private ?string $product_variation_name = null,
        private ?string $product_variation_name_postfix = null,

        private ?string $product_modification_uid = null,
        private ?string $product_modification_value = null,
        private ?string $product_modification_postfix = null,
        private ?string $product_modification_reference = null,
        private ?string $product_modification_name = null,
        private ?string $product_modification_name_postfix = null,

    ) {}

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }

    public function getPriority(): ?bool
    {
        return $this->priority;
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getUsersProfileUsername(): ?string
    {
        return $this->users_profile_username;
    }

    public function getActionName(): ?string
    {
        return $this->action_name;
    }

    public function getManufactureApplicationId(): ManufactureApplicationUid
    {
        return new ManufactureApplicationUid($this->id);
    }

    public function getManufactureApplicationEvent(): ManufactureApplicationEventUid
    {
        return new ManufactureApplicationEventUid($this->event);
    }

    public function getProductId(): ProductEventUid
    {
        return new ProductEventUid($this->product_uid);
    }

    public function getProductOfferId(): ProductOfferUid|false
    {
        return $this->product_offer_uid ? new ProductOfferUid($this->product_offer_uid) : false;
    }

    /**
     * ProductOffer
     */
    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductOfferName(): ?string
    {
        return $this->product_offer_name;
    }

    public function getProductOfferNamePostfix(): ?string
    {
        return $this->product_offer_name_postfix;
    }

    /**
     * ProductVariation
     */

    public function getProductVariationId(): ProductVariationUid|false
    {
        return $this->product_variation_uid ? new ProductVariationUid($this->product_variation_uid) : false;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductVariationName(): ?string
    {
        return $this->product_variation_name;
    }

    public function getProductVariationNamePostfix(): ?string
    {
        return $this->product_variation_name_postfix;
    }


    /**
     * ProductModification
     */

    public function getProductModificationId(): ProductModificationUid|false
    {
        return $this->product_modification_uid ? new ProductModificationUid($this->product_modification_uid) : false;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductModificationName(): ?string
    {
        return $this->product_modification_name;
    }

    public function getProductModificationNamePostfix(): ?string
    {
        return $this->product_modification_name_postfix;
    }


    public function getProductRootImages(): array|null
    {
        return [
            'img_root' => true,
            'img' => $this->product_image,
            'img_ext' => $this->product_image_ext,
            'img_cdn' => $this->product_image_cdn,
        ];
    }

    public function getProductTotal(): ?int
    {
        return $this->product_total;
    }

    public function getProductTotalCompleted(): ?int
    {
        return $this->product_total_completed;
    }

}