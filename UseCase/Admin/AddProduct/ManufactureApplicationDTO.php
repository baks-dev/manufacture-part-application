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

declare(strict_types=1);

namespace BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct;

use BaksDev\Manufacture\Part\Application\Entity\Event\ManufactureApplicationEventInterface;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\ManufactureApplicationStatusNew;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\Product\ManufactureApplicationProductDTO;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ManufactureApplicationEvent */
final class ManufactureApplicationDTO implements ManufactureApplicationEventInterface
{

    /**
     * Идентификатор события
     */
    private readonly null $id;


    /**
     * Идентификатор процесса производства
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly UsersTableActionsEventUid $action;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    private UserProfileUid $fixed;

    private bool $priority;

    private ManufactureApplicationProductDTO $product;

    public function setProduct(ManufactureApplicationProductDTO $product): self
    {
        $this->product = $product;
        return $this;
    }

    /** Статус заявки */
    #[Assert\NotBlank]
    private readonly ManufactureApplicationStatus $status;


    public function __construct(UserProfileUid $profile,) {

        $this->id = null;
        $this->product = new ManufactureApplicationProductDTO();
        $this->fixed = $profile;

        $this->action = new UsersTableActionsEventUid(ManufactureApplicationUid::ACTION_ID);
        $this->status = new ManufactureApplicationStatus(ManufactureApplicationStatusNew::class);

    }

    /**
     * Идентификатор события
     */
    public function getEvent(): null
    {
        return $this->id;
    }


    public function getProduct(): ManufactureApplicationProductDTO
    {
        return $this->product;
    }

    public function getPriority(): bool
    {
        return $this->priority;
    }

    public function setPriority(bool $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getAction(): UsersTableActionsEventUid
    {
        return $this->action;
    }

    public function getFixed(): UserProfileUid
    {
        return $this->fixed;
    }

    public function getStatus(): ManufactureApplicationStatus
    {
        return $this->status;
    }

}