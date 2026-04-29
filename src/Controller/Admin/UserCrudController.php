<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword() !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashedPassword);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword() !== null) {
            // Проверяем, изменился ли пароль (не хэшируем уже хэшированный)
            $currentPassword = $entityInstance->getPassword();
            if (strlen($currentPassword) !== 60 || !preg_match('/^\$2y\$/', $currentPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $currentPassword);
                $entityInstance->setPassword($hashedPassword);
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email', 'Email'),
            TextField::new('username', 'Имя пользователя'),
            TextField::new('surname', 'Фамилия'),
            TextField::new('name', 'Имя'),
            TextField::new('patronymic', 'Отчество')
                ->hideOnIndex(),
            TextField::new('phone', 'Телефон'),
            TextField::new('password', 'Пароль')
                ->onlyOnForms()
                ->setRequired(false),
            BooleanField::new('isApproved', 'Подтверждён'),
            ArrayField::new('roles', 'Роли')
                ->hideOnIndex(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователя')
            ->setEntityLabelInPlural('Пользователи')
            ->setPageTitle('index', 'Список пользователей')
            ->setPageTitle('edit', 'Редактировать пользователя');
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveAction = Action::new('approve', 'Подтвердить', 'fa fa-check-circle')
            ->linkToCrudAction('approveUser')
            ->setCssClass('btn btn-success')
            ->displayIf(static function ($entity) {
                return !$entity->getIsApproved();
            });

        $blockAction = Action::new('block', 'Заблокировать', 'fa fa-ban')
            ->linkToCrudAction('blockUser')
            ->setCssClass('btn btn-danger')
            ->displayIf(static function ($entity) {
                return $entity->getIsApproved();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->add(Crud::PAGE_INDEX, $blockAction);
    }

    public function approveUser(User $user, EntityManagerInterface $em, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $user->setIsApproved(true);
        $em->flush();

        $this->addFlash('success', 'Пользователь подтверждён');

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function blockUser(User $user, EntityManagerInterface $em, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        $user->setIsApproved(false);
        $em->flush();

        $this->addFlash('success', 'Пользователь заблокирован');

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
