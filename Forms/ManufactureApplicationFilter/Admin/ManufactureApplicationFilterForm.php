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

namespace BaksDev\Manufacture\Part\Application\Forms\ManufactureApplicationFilter\Admin;

use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus;
use BaksDev\Manufacture\Part\Application\Type\Status\ManufactureApplicationStatus\Collection\ManufactureApplicationStatusCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма фильтра на странице списка заявок
 */
class ManufactureApplicationFilterForm extends AbstractType
{

    private SessionInterface|false $session = false;

    private string $sessionKey;

    public function __construct(
        private readonly RequestStack $request,
        private readonly ManufactureApplicationStatusCollection $applicationStatusCollection,
    )
    {
        $this->sessionKey = md5(self::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * Статус
         */
        $builder->add('status', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event): void {

            /** @var ManufactureApplicationFilterDTO $data */
            $data = $event->getData();

            $builder = $event->getForm();

            if($this->session === false)
            {
                $this->session = $this->request->getSession();
            }

            if($this->session && $this->session->get('statusCode') === 307)
            {

                $this->session->remove($this->sessionKey);
                $this->session = false;
            }

            if($this->session && (time() - $this->session->getMetadataBag()->getLastUsed()) > 300)
            {
                $this->session->remove($this->sessionKey);
                $this->session = false;
            }


            if($this->session)
            {
                $sessionData = $this->request->getSession()->get($this->sessionKey);
                $sessionJson = $sessionData ? base64_decode($sessionData) : false;
                $sessionArray = $sessionJson !== false && json_validate($sessionJson) ? json_decode($sessionJson, true, 512, JSON_THROW_ON_ERROR) : false;


                if($sessionArray !== false)
                {
                    !isset($sessionArray['status']) ?: $data->setStatus(new ManufactureApplicationStatus($sessionArray['status']));
                }
            }

            /** Если жестко не указан status - выводим список для выбора */
            if($data)
            {

                $builder->add('status', ChoiceType::class, [
                    'choices' => $this->applicationStatusCollection->getStatuses(),
                    'choice_value' => function(?ManufactureApplicationStatus $status) {
                        return $status?->getManufactureApplicationStatusValue();
                    },
                    'choice_label' => function(ManufactureApplicationStatus $status) {
                        return $status?->getManufactureApplicationStatusName();
                    },
                    'label' => false,
                    'required' => false,
                ]);
            }
        });


        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {

                if($this->session === false)
                {
                    $this->session = $this->request->getSession();
                }

                if($this->session)
                {
                    /** @var ManufactureApplicationFilterDTO $data */
                    $data = $event->getData();

                    $sessionArray = [];

                    if($data->getStatus())
                    {
                        $sessionArray['status'] = (string) $data->getStatus();
                    }

                    if($sessionArray)
                    {
                        $sessionJson = json_encode($sessionArray, JSON_THROW_ON_ERROR);
                        $sessionData = base64_encode($sessionJson);
                        $this->request->getSession()->set($this->sessionKey, $sessionData);
                        return;
                    }

                    $this->session->remove($this->sessionKey);
                }

            }
        );


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event): void {}
        );


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ManufactureApplicationFilterDTO::class,
                'validation_groups' => false,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ]
        );
    }
}