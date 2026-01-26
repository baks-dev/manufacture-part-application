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
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * При добавлении в открытую партию продукта производственной заявки
 * - уменьшает заявку на количество если взяли в работу меньше,
 * - завершает производственную заявку если количество взятое в работу равно
 */
#[AsMessageHandler(priority: 10)]
final readonly class ManufactureApplicationProductUpdateDispatcher
{
    public function __construct(
        #[Target('manufacturePartApplicationLogger')] private LoggerInterface $logger,
        private ManufactureApplicationProductInterface $manufactureApplicationProduct,
        private UpdateManufactureApplicationProductHandler $UpdateManufactureApplicationProductHandler,
    ) {}

    public function __invoke(ManufacturePartProductMessage $message): void
    {
        /** Получить данные и отправить в хендлер по обновлению товара */

        if(empty($message->getTotal()))
        {
            $this->logger->error(
                'Не возможно выполнить заявку с нулевым количеством',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

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
            $this->logger->error(
                'Производственная заявка не найдена',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        /* DTO для обновления заявки */
        $UpdateManufactureApplicationDTO = new UpdateManufactureApplicationDTO();
        $UpdateManufactureApplicationDTO->setId($ManufactureApplicationProductResult->getManufactureApplicationEvent());

        $UpdateManufactureApplicationProductDTO = new UpdateManufactureApplicationProductDTO();
        $UpdateManufactureApplicationProductDTO->setId($ManufactureApplicationProductResult->getManufactureApplicationEvent());


        /**
         * Уменьшаем производственную заявку в случае если количество взятое в работу меньше чем в заявке
         */

        if($ManufactureApplicationProductResult->getProductTotal() > $message->getTotal())
        {
            /* Уменьшаем кол-во на то, что указал поль-тель при добавлении товара в производственную партию */
            $updated_total = $ManufactureApplicationProductResult->getProductTotal() - $message->getTotal();
            $UpdateManufactureApplicationProductDTO->setTotal($updated_total);
            $UpdateManufactureApplicationDTO->setProduct($UpdateManufactureApplicationProductDTO);

            $this->UpdateManufactureApplicationProductHandler->handle($UpdateManufactureApplicationDTO, false);

            $this->logger->info(
                sprintf('Уменьшили кол-во товара в производственной заявке на %s', $updated_total),
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }

        /**
         * Закрываем производственную заявку в случае если количество взятое в работу равное заявке
         *
         * @see ManufactureApplicationProductCompleteDispatcher
         */

        $this->logger->info(
            sprintf('%s: закрываем производственную заявку', $ManufactureApplicationProductResult->getManufactureApplicationId()),
            [self::class.':'.__LINE__, var_export($message, true)],
        );

        $UpdateManufactureApplicationProductDTO->setCompleted($message->getTotal());
        $UpdateManufactureApplicationDTO->setProduct($UpdateManufactureApplicationProductDTO);

        $this->UpdateManufactureApplicationProductHandler->handle($UpdateManufactureApplicationDTO);

    }
}