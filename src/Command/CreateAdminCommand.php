<?php

namespace App\Command;

use App\Entity\AdminUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates the default admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = 'info@rdn.by';
        $password = 'Vlad228800';

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(AdminUser::class)->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            $io->note(sprintf('User with email %s already exists!', $email));
            
            // Optionally update password if needed
            $hashedPassword = $this->passwordHasher->hashPassword($existingUser, $password);
            $existingUser->setPassword($hashedPassword);
            $existingUser->setRoles(['ROLE_ADMIN']);
            
            $this->entityManager->flush();
            $io->success('Password/roles updated for existing user.');
            
            return Command::SUCCESS;
        }

        $user = new AdminUser();
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user %s successfully created.', $email));

        return Command::SUCCESS;
    }
}
