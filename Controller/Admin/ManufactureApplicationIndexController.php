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

namespace BaksDev\Manufacture\Part\Application\Controller\Admin;

use BaksDev\Centrifugo\Services\Token\TokenUserGenerator;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Manufacture\Part\Application\Forms\ManufactureApplicationFilter\Admin\ManufactureApplicationFilterDTO;
use BaksDev\Manufacture\Part\Application\Forms\ManufactureApplicationFilter\Admin\ManufactureApplicationFilterForm;
use BaksDev\Manufacture\Part\Application\Repository\AllManufacturePartApplication\AllManufacturePartApplicationInterface;
use BaksDev\Manufacture\Part\Repository\OpenManufacturePart\OpenManufacturePartInterface;
use BaksDev\Manufacture\Part\UseCase\Admin\AddProduct\ManufactureSelectionPartProductsForm;
use BaksDev\Telegram\Bot\BaksDevTelegramBotBundle;
use BaksDev\Telegram\Bot\UseCase\TelegramProductInfo\TelegramProductsInfoForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_MANUFACTURE_PART_APPLICATION')]
final class ManufactureApplicationIndexController extends AbstractController
{
    #[Route('/admin/manufacture/application/{page<\d+>}', name: 'admin.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        AllManufacturePartApplicationInterface $allManufacturePartApplication,
        TokenUserGenerator $tokenUserGenerator,
        OpenManufacturePartInterface $openManufacturePart,
        int $page = 0,
    ): Response
    {

        // Поиск
        $search = new SearchDTO();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);

        $opens = $openManufacturePart
            ->forFixed($this->getCurrentProfileUid())
            ->find();

        /**
         * Фильтр продукции
         */
        $filter = new ManufactureApplicationFilterDTO();

        $filterForm = $this
            ->createForm(
                type: ManufactureApplicationFilterForm::class,
                data: $filter,
                options: ['action' => $this->generateUrl('manufacture-part-application:admin.index')],
            )
            ->handleRequest($request);


        /**
         * Список продукции
         */
        $query = $allManufacturePartApplication
            ->search($search)
            ->setOpens($opens)
            ->filter($filter)
            ->findPaginator();

        /** Проверить установлен ли telegram-bot */
        $has_telegram_bot = class_exists(BaksDevTelegramBotBundle::class);

        return $this->render(
            [
                'query' => $query, //$ManufacturePart,
                'search' => $searchForm->createView(),
                'filter' => $filterForm->createView(),
                'current_profile' => $this->getCurrentProfileUid(),
                'token' => $tokenUserGenerator->generate($this->getUsr()),
                'add_selected_product_form_name' => $this->createForm(type: ManufactureSelectionPartProductsForm::class)->getName(),
                'opens' => $opens,
                'has_telegram_bot' => class_exists(BaksDevTelegramBotBundle::class),
                // Если telegram-bot установлен - указать имя формы TelegramProductsInfoForm
                'telegram_products_info_form_name' => $has_telegram_bot ? $this->createForm(type: TelegramProductsInfoForm::class)->getName() : '',
            ]
        );
    }
}