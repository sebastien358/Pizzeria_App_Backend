<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductOption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Crée des produits (pizzas)
        $pizzas = [
            'Margherita',
            'Reine',
            'Hawaïenne',
            'Végétarienne',
            'Quatre fromages',
            'Pepperoni',
            'Calzone',
            'Diavola',
            'Orientale',
            'Mediterraneenne',
            'Savoyarde',
            'Lyonnaise',
        ];
        // Crée des options (tailles de pizza)
        $sizes = [
            'Petite' => 6.99,
            'Moyenne' => 8.99,
            'Grande' => 12.99,
        ];
        foreach ($pizzas as $name) {
            $product = new Product();
            $product->setTitle($name);
            $product->setDescription('Une délicieuse pizza ' . $name);
            $manager->persist($product);
            foreach ($sizes as $sizeName => $sizePrice) {
                $productOption = new ProductOption();
                $productOption->setName($sizeName);
                $productOption->setPrice($sizePrice);
                $productOption->setProduct($product);
                $manager->persist($productOption);
            }
        }
        $manager->flush();
    }
}
