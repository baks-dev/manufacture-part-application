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

namespace BaksDev\Manufacture\Part\Application\Command;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Locale\Locales\Ru;
use BaksDev\Manufacture\Part\Application\Repository\ExistManufactureApplicationAction\ExistManufactureApplicationActionInterface;
use BaksDev\Manufacture\Part\Application\Type\Id\ManufactureApplicationUid;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\Trans\UsersTableActionsTransDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsDTO;
use BaksDev\Users\UsersTable\UseCase\Admin\Actions\NewEdit\UsersTableActionsHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'baks:manufacture:application:action',
    description: 'Создание процесса производства для заявки ManufactureApplicationAction'
)]
class ManufactureApplicationActionCommand extends Command
{
    public function __construct(
        private readonly UsersTableActionsHandler $UsersTableActionsHandler,
        private readonly ExistManufactureApplicationActionInterface $existManufactureApplicationAction
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('argument', InputArgument::OPTIONAL, 'Описание аргумента');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $action_name = ManufactureApplicationUid::ACTION_NAME;
        $action_id = ManufactureApplicationUid::ACTION_ID;


        /** Проверить - существует ли Производственный процесс с заданным id */
        if ($this->existManufactureApplicationAction->isExistManufactureApplicationAction($action_id)) {
            $io->success('Производственный процесс "' . $action_name . '" уже существует');
            return Command::SUCCESS;
        }


        /**  Процесс производства */
        $UsersTableActionsDTO = new UsersTableActionsDTO();

        // UsersTableActionsTransDTO
        $translateElement = new UsersTableActionsTransDTO();
        $translateElement->setName($action_name);

        $Locale = new Locale(Ru::LOCAL);
        $translateElement->setLocal($Locale);

        $translateCollection = new ArrayCollection();
        $translateCollection->add($translateElement);
        $UsersTableActionsDTO->setTranslate($translateCollection);

        /** Задать предопределенное значение */
        $UsersTableActionsDTO->setApplication(new  UsersTableActionsUid(ManufactureApplicationUid::ACTION_ID));

        /** Создать производственный процесс */
        $this->UsersTableActionsHandler->handle($UsersTableActionsDTO);

        $io->success('Производственный процесс "' . $action_name . '" создан');

        return Command::SUCCESS;
    }
}
