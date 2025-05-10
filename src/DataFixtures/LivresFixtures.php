<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Livres;
use DateTime;
use Faker\Factory;
use Symfony\Component\Validator\Constraints\Date;
use App\Entity\Categorie;

class LivresFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker  = Factory::create('fr_FR');
        for ($j = 1; $j <= 5; $j++) {
            $categorie = new Categorie();
            $names = ['Roman', 'Science Fiction', 'Histoire', 'Biographie', 'Fantasy'];
            $categorie->setLibelle($names[$j - 1])
                ->setSlug(strtolower(str_replace(' ', '-', $names[$j - 1])))
                ->setDescription($faker->text(200));
            $manager->persist($categorie);
            for ($i = 1; $i <= random_int(10, 15); $i++) {
                $Livre = new Livres();
                $Livre->setTitre('titre ' . $i)
                    ->setPrix($faker->randomFloat(2, 10, 700))
                    ->setIsbn($faker->isbn13)
                    ->setEditeur($faker->company)
                    ->setDateEdition($faker->dateTimeBetween('-5 years', 'now')) // Passage direct de l'objet DateTime
                    ->setImage('https://picsum.photos/200/300')
                    ->setSlug(strtolower(str_replace(' ', '-', 'titre ' . $i)))
                    ->setResume($faker->text(200))
                    ->setCategorie($categorie);

                $manager->persist($Livre);
            }
        }
        $manager->flush(); //envoie les insert au bd
    }
}
