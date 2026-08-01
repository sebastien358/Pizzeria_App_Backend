# E-commerce Pizzeria - Fullstack

**Démo en ligne :** [pizzeria.sebastien-petit.fr](https://pizzeria.sebastien-petit.fr)

## 🎯 Objectif du projet
Plateforme e-commerce complète développée avec **Symfony + React + Next.js**
dans le cadre du Titre RNCP Développeur Web et Web Mobile - Niveau 5.

Ce projet démontre :
- **Architecture Fullstack** : Frontend React/Next.js + Backend API Symfony
- **Authentification sécurisée** : Gestion des rôles Admin / Client
- **Gestion complète** : Produits, Catégories, Commandes
- **Tunnel de commande** : Intégration Stripe
- **UX/UI** : Interface responsive + Animations GSAP
- **Code propre** : Composants réutilisables

## 🔐 Comptes de démonstration
Pour tester l’application :

**Admin**
- Email : `sebastienpetit27330@gmail.com`
- Mot de passe : `password`

**Client**
- Email : `sebastien.p0027@gmail.com`
- Mot de passe : `password`

## 💳 Carte de test Stripe
- Numéro : `4242 4242 4242 4242`
- Expiration : `07/28`
- CVC : `123`
- Code postal : `75001`

*Les données sont réinitialisées régulièrement. Usage démo uniquement.*

## 🛠️ Stack technique
**Frontend** : React, Next.js, GSAP
**Backend** : Symfony, API REST, MySQL
**Paiement** : Stripe
**Déploiement** : Production

## 🚀 Lancement
```bash
# Backend
composer install
symfony serve

# Frontend
npm install
npm run dev
