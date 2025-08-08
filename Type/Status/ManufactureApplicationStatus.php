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

namespace BaksDev\Manufacture\Part\Application\Type\Status;

use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\Collection\ManufactureApplicationStatusInterface;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\ManufactureApplicationStatusNew;
use InvalidArgumentException;

final class ManufactureApplicationStatus
{
    public const string TYPE = 'manufacture_application_status_type';
    public const string TEST = ManufactureApplicationStatusNew::class;

    private ManufactureApplicationStatusInterface $status;

    public function __construct(
        self|string|ManufactureApplicationStatusInterface $status,
    ) {

        if(is_string($status) && class_exists($status))
        {
            $instance = new $status();

            if($instance instanceof ManufactureApplicationStatusInterface)
            {
                $this->status = $instance;
                return;
            }
        }

        if($status instanceof ManufactureApplicationStatusInterface)
        {
            $this->status = $status;
            return;
        }

        if($status instanceof self)
        {
            $this->status = $status->getManufactureApplicationStatus();
            return;
        }

        /** @var ManufactureApplicationStatusInterface $declare */

        foreach(self::getDeclared() as $declare)
        {
            $instance = new self($declare);

            if($instance->getManufactureApplicationStatusValue() === $status)
            {
                $this->status = new $declare();
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Not found ManufactureApplicationStatus %s', $status));

    }

    public function __toString(): string
    {
        return $this->status->getValue();
    }

    public function getManufactureApplicationStatus(): ManufactureApplicationStatusInterface
    {
        return $this->status;
    }

    public function getManufactureApplicationStatusValue(): string {
        return $this->status->getValue();
    }

    public function getManufactureApplicationStatusName(): string {
        return $this->status->getName();
    }

    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $key => $declared)
        {
            /** @var ManufactureApplicationStatusInterface $declared */
            $class = new $declared();

            $case[$class::priority().$key] = new self($class);
        }

        ksort($case);

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(ManufactureApplicationStatusInterface::class, class_implements($className), true);
            }
        );
    }

    public function equals(mixed $status): bool
    {
        $status = new self($status);
        return $this->getManufactureApplicationStatusValue() === $status->getManufactureApplicationStatusValue();
    }

}