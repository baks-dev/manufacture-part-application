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

namespace BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationDTOCollection;

use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\Product\ManufactureApplicationProductDTO;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Класс для использования нескольких ManufactureApplicationDTO
 */
class ManufactureApplicationDTOCollection
{
    private ArrayCollection $product_data;

    private bool $priority;

    public function setPriority(bool $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getPriority(): bool
    {
        return $this->priority;
    }

    public function __construct()
    {
        $this->product_data = new ArrayCollection();
    }

    public function getProductData(): ArrayCollection
    {
        return $this->product_data;
    }

    public function addProductData(ManufactureApplicationProductDTO $product): self
    {

        $this->product_data->add($product);

        return $this;
    }

    public function removeProductData(ManufactureApplicationProductDTO $product): self
    {
        $this->product_data->removeElement($product);
        return $this;
    }
}