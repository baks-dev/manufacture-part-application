<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Manufacture\Part\Application\UseCase\Admin\Completed;

use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEvent;
use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEventInterface;
use BaksDev\Manufacture\Part\Application\Type\Event\ManufactureApplicationEventUid;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\ManufactureApplicationStatusCompleted;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ManufactureApplicationEvent */
final class ManufactureApplicationCompletedDTO implements ManufactureApplicationEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?ManufactureApplicationEventUid $id;

    /**
     * Статус производственной партии
     */
    #[Assert\NotBlank]
    private readonly ManufactureApplicationStatus $status;

    public function __construct()
    {
        $this->status = new ManufactureApplicationStatus(ManufactureApplicationStatusCompleted::class);
    }

    /**
     * Идентификатор события
     */
    public function getEvent(): ?ManufactureApplicationEventUid
    {
        return $this->id;
    }

    public function setId(?ManufactureApplicationEventUid $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Статус производственной партии
     */
    public function getStatus(): ManufactureApplicationStatus
    {
        return $this->status;
    }
}