<?php

namespace App\DataFixtures;

use App\Entity\Advice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdviceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $advices = [
            1 => "Taillez vos arbres fruitiers pour favoriser la future floraison.",
            2 => "Préparez vos semis d’intérieur pour le printemps à venir.",
            3 => "Plantez les bulbes de printemps et aérez la terre.",
            4 => "Commencez à arroser régulièrement vos jeunes plants.",
            5 => "Surveillez les pucerons et parasites sur les plantes extérieures.",
            6 => "Arrosez tôt le matin pour éviter l’évaporation excessive.",
            7 => "Coupez les fleurs fanées pour prolonger la floraison.",
            8 => "Récoltez vos fruits et légumes d’été à maturité.",
            9 => "Préparez la terre pour les plantations d’automne.",
            10 => "Taillez les arbustes et rentrez les plantes fragiles.",
            11 => "Protégez vos plantes du gel et nettoyez le jardin.",
            12 => "Compostez les déchets verts et planifiez vos semis pour l’année suivante.",
        ];

        foreach ($advices as $month => $content) {
            $advice = new Advice();
            $advice->setContent($content);
            $advice->setMonth([$month]);
            $advice->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($advice);
        }

        $manager->flush();
    }
}
