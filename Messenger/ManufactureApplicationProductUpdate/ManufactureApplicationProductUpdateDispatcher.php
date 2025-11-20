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

namespace BaksDev\Manufacture\Part\Application\Messenger\ManufactureApplicationProductUpdate;

use BaksDev\Manufacture\Part\Application\Repository\ManufactureApplicationProduct\ManufactureApplicationProductInterface;
use BaksDev\Manufacture\Part\Application\Repository\ManufactureApplicationProduct\ManufactureApplicationProductResult;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\UpdateManufactureApplicationProduct\Product\UpdateManufactureApplicationProductDTO;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\UpdateManufactureApplicationProduct\UpdateManufactureApplicationDTO;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\UpdateManufactureApplicationProduct\UpdateManufactureApplicationProductHandler;
use BaksDev\Manufacture\Part\Messenger\ManufacturePartProduct\ManufacturePartProductMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обработчик сообщения ManufacturePartProductMessage,
 * которое диспатчится при добавлении товара в Производственную партию
 */
#[AsMessageHandler(priority: 10)]
final readonly class ManufactureApplicationProductUpdateDispatcher
{

    public function __construct(
        private ManufactureApplicationProductInterface $manufactureApplicationProduct,
        private UpdateManufactureApplicationProductHandler $UpdateManufactureApplicationProductHandler,
    ) {}

    public function __invoke(ManufacturePartProductMessage $message): void
    {

        /** Получить данные и отправить в хендлер по обновлению товара */

        if($message->getTotal() !== false)
        {

            /* Получить данные по товару текущей заявки */
            /** @var ManufactureApplicationProductResult $ManufactureApplicationProductResult */
            $ManufactureApplicationProductResult = $this->manufactureApplicationProduct->findApplicationProduct(
                $message->getEvent(),
                $message->getOffer(),
                $message->getVariation(),
                $message->getModification(),
            );

            if(false === ($ManufactureApplicationProductResult instanceof ManufactureApplicationProductResult))
            {
                return;
            }

            /* DTO для обновления заявки */
            $UpdateManufactureApplicationDTO = new UpdateManufactureApplicationDTO();

            /* DTO для обновления товара заявки */
            $UpdateManufactureApplicationProductDTO = new UpdateManufactureApplicationProductDTO();
            $UpdateManufactureApplicationProductDTO->setId($ManufactureApplicationProductResult->getManufactureApplicationEvent());

            $UpdateManufactureApplicationDTO->setId($ManufactureApplicationProductResult->getManufactureApplicationEvent());

            $is_completed = false;
            if($ManufactureApplicationProductResult->getProductTotal() > $message->getTotal())
            {
                /* Уменьшаем кол-во на то, что указал поль-тель при добавлении товара в производственную партию */
                $updated_total = $ManufactureApplicationProductResult->getProductTotal() - $message->getTotal();
                $UpdateManufactureApplicationProductDTO->setTotal($updated_total);
            }
            else
            {
                $UpdateManufactureApplicationProductDTO->setCompleted($message->getTotal());

                /* Укажем флаг по завершению */
                $is_completed = true;

            }

            $UpdateManufactureApplicationDTO->setProduct($UpdateManufactureApplicationProductDTO);

            $this->UpdateManufactureApplicationProductHandler->handle($UpdateManufactureApplicationDTO, $is_completed);

        }

    }
}