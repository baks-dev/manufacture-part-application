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

use BaksDev\Manufacture\Part\Application\Type\Event\ManufactureApplicationEventUid;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;

final class ManufactureApplicationProductResult
{
    public function __construct(

        private string|null $id,
        private string|null $event,

        private ?string $product_product_uid,

        private int|null $product_total,
        private int|null $product_total_completed,
        private bool|null $priority = false,

    ) {}

    public function getManufactureApplicationId(): ManufactureApplicationUid {
        return new ManufactureApplicationUid($this->id);
    }

    public function getManufactureApplicationEvent(): ManufactureApplicationEventUid {
        return new ManufactureApplicationEventUid($this->event);
    }

    public function getPriority(): ?bool
    {
        return $this->priority;
    }

    public function getProductTotal(): ?int {
        return $this->product_total;
    }

    public function getProductTotalCompleted(): ?int {
        return $this->product_total_completed;
    }

    public function getProductProductId(): ProductEventUid
    {
        return new ProductEventUid($this->product_product_uid);
    }

}