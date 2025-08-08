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

namespace BaksDev\Manufacture\Part\Application\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Manufacture\Part\Application\Entity\ManufactureApplication;
use BaksDev\Manufacture\Part\Application\Entity\Product\ManufactureApplicationProduct;
use BaksDev\Manufacture\Part\Application\Type\Event\ManufactureApplicationEventUid;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\ManufactureApplicationStatusNew;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Type\Actions\Event\UsersTableActionsEventUid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'manufacture_application_event')]
#[ORM\Index(columns: ['action'])]
#[ORM\Index(columns: ['fixed'])]
class ManufactureApplicationEvent extends EntityEvent
{

    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ManufactureApplicationEventUid::TYPE)]
    private ManufactureApplicationEventUid $id;


    /**
     * Идентификатор ManufactureApplication
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ManufactureApplicationUid::TYPE, nullable: false)]
    private ?ManufactureApplicationUid $main = null;

    /**
     * Идентификатор процесса производства
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UsersTableActionsEventUid::TYPE)]
    private UsersTableActionsEventUid $action;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE,)]
    private UserProfileUid $fixed;


    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $priority;


    /**
     * Коллекция продукции
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: ManufactureApplicationProduct::class, mappedBy: 'event', cascade: ['all'])]
    private ManufactureApplicationProduct $product;

    /** Статус заявки */
    #[Assert\NotBlank]
    #[ORM\Column(type: ManufactureApplicationStatus::TYPE)]
    private ManufactureApplicationStatus $status;

    public function __construct()
    {
        $this->id = new ManufactureApplicationEventUid();
        $this->status = new ManufactureApplicationStatus(ManufactureApplicationStatusNew::class);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ManufactureApplicationEventUid
    {
        return $this->id;
    }

    public function getMain(): ?ManufactureApplicationUid
    {
        return $this->main;
    }

    public function setMain(ManufactureApplicationUid|ManufactureApplication $main): self
    {
        $this->main = $main instanceof ManufactureApplication ? $main->getId() : $main;

        return $this;
    }

    public function isPriority(): bool
    {
        return $this->priority;
    }

    /**
     * @deprecated  используйте метод isStatusEquals
     * @see self::isStatusEquals
     */
    public function getStatus(): ManufactureApplicationStatus
    {
        return $this->status;
    }

    public function isStatusEquals(mixed $status): bool
    {
        return $this->status->equals($status);
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ManufactureApplicationEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ManufactureApplicationEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}