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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Manufacture\Part\Application\BaksDevManufacturePartApplicationBundle;
use BaksDev\Manufacture\Part\Application\Type\Event\ManufactureApplicationEventType;
use BaksDev\Manufacture\Part\Application\Type\Event\ManufactureApplicationEventUid;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationType;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Manufacture\Part\Application\Type\Product\ManufactureApplicationProductType;
use BaksDev\Manufacture\Part\Application\Type\Product\ManufactureApplicationProductUid;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatusType;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine) {

    $doctrine->dbal()->type(ManufactureApplicationUid::TYPE)->class(ManufactureApplicationType::class);
    $doctrine->dbal()->type(ManufactureApplicationEventUid::TYPE)->class(ManufactureApplicationEventType::class);

    $doctrine->dbal()->type(ManufactureApplicationProductUid::TYPE)->class(ManufactureApplicationProductType::class);

    $doctrine->dbal()->type(ManufactureApplicationStatus::TYPE)->class(ManufactureApplicationStatusType::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);


    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    /** Value Resolver */

    $services->set(ManufactureApplicationUid::class)->class(ManufactureApplicationUid::class);

    $emDefault
        ->mapping('manufacture-part-application')
        ->type('attribute')
        ->dir(BaksDevManufacturePartApplicationBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevManufacturePartApplicationBundle::NAMESPACE.'\\Entity')
        ->alias('manufacture-part-application');
};
