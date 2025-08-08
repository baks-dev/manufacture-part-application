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

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Manufacture\Part\Application\Entity\ManufactureApplication;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationDTO;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationDTOCollection\ManufactureApplicationCollectionForm;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationDTOCollection\ManufactureApplicationDTOCollection;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\ManufactureApplicationHandler;
use BaksDev\Manufacture\Part\Application\UseCase\Admin\AddProduct\Product\ManufactureApplicationProductDTO;
use BaksDev\Products\Product\Repository\ProductsDetailByUids\ProductsDetailByUidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_MANUFACTURE_PART_APPLICATION_NEW')]
final class ManufactureApplicationNewController extends AbstractController
{

    #[Route('/admin/manufacture/part/application/new', name: 'admin.application.newedit.new', methods: ['GET', 'POST'])]
    public function news(
        Request $request,
        ManufactureApplicationHandler $ManufactureApplicationHandler,
        ProductsDetailByUidsInterface $productsDetail,
    )
    {

        /**  контейнер для форм товара */
        $ManufactureApplicationDTOCollection = new ManufactureApplicationDTOCollection();

        $form = $this->createForm(
            type: ManufactureApplicationCollectionForm::class,
            data: $ManufactureApplicationDTOCollection,
            options: ['action' => $this->generateUrl('manufacture-part-application:admin.application.newedit.new')]
        )
            ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('manufacture_application_collection'))
        {
            $this->refreshTokenForm($form);

            foreach($ManufactureApplicationDTOCollection->getProductData() as $key => $ManufactureApplicationProductDTO)
            {
                $ManufactureApplicationDTO = new ManufactureApplicationDTO($this->getProfileUid());
                $ManufactureApplicationDTO->setPriority($ManufactureApplicationDTOCollection->getPriority());
                $ManufactureApplicationDTO->setProduct($ManufactureApplicationProductDTO);

                $handle = $ManufactureApplicationHandler->handle($ManufactureApplicationDTO);

                $this->addFlash
                (
                    'admin.application.page.new',
                    $handle instanceof ManufactureApplication ? 'admin.success.new' : 'admin.danger.new',
                    'manufacture-part-application',
                );
            }

            return $this->redirectToReferer();
        }


        /**  Получить массивы UIDs по выбранным продуктам */
        $events = [];
        $offers = [];
        $variations = [];
        $modifications = [];

        /** @var ManufactureApplicationProductDTO $ManufactureApplicationProductDTO */
        foreach($ManufactureApplicationDTOCollection->getProductData() as $key => $ManufactureApplicationProductDTO)
        {
            $events[$key] = $ManufactureApplicationProductDTO->getProduct();
            $offers[$key] = $ManufactureApplicationProductDTO->getOffer();
            $variations[$key] = $ManufactureApplicationProductDTO->getVariation();
            $modifications[$key] = $ManufactureApplicationProductDTO->getModification();
        }


        /** Получаем информацию о добавленных продуктах */
        $details = $productsDetail
            ->events($events)
            ->offers($offers)
            ->variations($variations)
            ->modifications($modifications)
            ->toArray();

        return $this->render([
            'form' => $form->createView(),
            'applications' => $details,
        ]);

    }
}