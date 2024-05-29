# PoleIT
Site web E-commerce
Voici les besoins du projet PoleIt détaillés :
✓ Création d’un site web attractif pour le centre d’astronomie PoleIt :
• Le site doit comprendre une page d’accueil présentant le centre et ses 
principaux atouts
(Observatoire, planétarium, etc.).
• La page d’accueil doit afficher les 4 derniers articles sous forme de 
carrousel accessibles uniquement aux membres connectés.
• Une page décrivant les activités diurnes et nocturnes.
• Une page de contage alimentant une base de données avec une interface 
d’administration pour traiter les demandes reçues.
✓ Fonctionnalité de commerce en ligne et interface d’administration :
• Une page e-commerce permettant la présentation et l’achat de goodies 
via la plateforme Stripe.
• Une gestion des stocks et la possibilité de spécifier la quantité d’articles 
à acheter
• Un système d’administration pour visualiser les demandes de contact, 
ajouter/modifier des articles, changer le stock disponible, et gérer les 
comptes utilisateurs.
✓ Infrastructure et déploiement :
• Mise en place d’une infrastructure kubernetes avec un contrôleur
Ingress, une architecture hyper convergée pour la haute disponibilité et 
la maintenabilité.
• Le cluster kubernetes doit contenir au moins 3 nœuds
• Les sessions PHP doivent être stockées de manière centralisée, par 
exemple dans Redis ou MySQL, au lieu d’une instance Docker PHP 
locale.
✓ Pratiques DevOps et pipeline CI/CD :
• Déploiement automatique du site web sur le cluster kubernetes via du 
pipeline CI/CD.
• Le pipeline doit valider le code HTML, utiliser un linter pour tous les 
fichiers, construire une image Docker du code, et déployer
automatiquement l’application sur le cluster kubernetes lors de la 
création d’un tag.
✓ Technologies utilisées :
• Symfony et Mysql.
Pour l’application lié au projet PoleIt, un menu de header efficace et organisé 
aiderait les utilisateurs à naviguer facilement et à accéder aux diverses sections 
importantes du site. Voici une proposition pour le menu header basée sur les 
informations fournies :
1. Accueil :
✓ Introduction au centre d’astronomie PoleIT
✓ Mise en avant des installations clés comme le radiotélescope géant
2. A propos :
✓ Informations détaillées sur le centre, son histoire et sa mission
✓ Détails sur les infrastructures comme le planétarium et l’observatoire
3. Activités :
✓ Activités Diurnes : Liste et descriptions des expositions et activités 
disponibles pendant la journée
✓ Activités Nocturnes : Informations sur les événements spéciaux et les 
expositions nocturnes
✓ Calendrier des Evénements : Calendrier interactif montrant les 
événements planifiés
4. Blog/Actualités
✓ Mises à jour régulières et articles de blog sur les dernières découvertes et 
événements au centre
✓ Articles accessibles uniquement aux membres connectés, avec un carrousel 
des quatre derniers articles sur la page d’accueil
5. Boutique
✓ Présentation des goodies disponibles à l’achat
✓ Intégration de la plateforme de paiement Stripe pour les transactions
6. Contact
✓ Formulaire de contact pour les questions générales
✓ Coordonnées pour les réservations de groupe ou les visites scolaires
✓ Interface d’administration pour gérer les requêtes reçues
7. Espace Membre
✓ Connexion/Inscription pour accéder aux contenus exclusifs et participer 
aux discussions
✓ Profil utilisateur et gestion des préférences
Ce menu est conçu pour offrir une navigation intuitive et mettre en avant les 
principales attractions et services du centre d’astronomie PoleIt. Il peut être ajusté 
en fonction des retours des utilisateurs et de l’évolution des besoins du centre.
Infrastructure et déploiement :
Votre infrastructure est un « beau » cluster Kubernetes incluant un contrôleur 
Ingress(Traefik)
✓ Cluster Kubernetes avec 3 nœuds :
✓ Implémentation du controller Traefik : lien de la documentation : 
https://doc.traefik.io/traefik/getting-started/quick-startwith-kubernetes/
✓ Le stockage doit être hautement disponible et il faut le penser comme une 
« infrastructure hyperconvergée » ( cf Annexes ) pour simplifier 
l'architecture et la maintenabilité.
• Volume persistant pour mysql :
• Déploiement Mysql et service :
✓ Déploiement de l’application symphony
Création du fichier DockerFile :
FROM php:8.1-fpm
# Install dependencies
RUN apt-get update && apt-get install -y \
 libicu-dev \
 libonig-dev \
 libzip-dev \
 unzip \
 git \
 && docker-php-ext-install pdo pdo_mysql intl zip opcache
# Set working directory
WORKDIR /var/www
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Set environment variables for Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer
# Copy application source
COPY . .
# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts
# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
• Dockerfile pour le serveur web Nginx :
• docker-compose.yml pour l'orchestration des conteneurs :
• Fichier de configuration Nginx (nginx.conf)
• Construire et pousser l’image docker
Commandes:
docker login
s’authentifier : nom d’utilisateur : farouckloicyann motdepasse : 
Adechina2024@
Construire l’image docker :
docker build -t farouckloicyann/symfony-app:1.0 .
Pousser l’image docker 
docker push farouckloicyann/symfony-app:1.0
Preuve de réussite des commandes docker :
• Stockage persistant pour l’application symfony
• Création du fichier Yaml pour le déploiement et le de l’application 
symfony :
• Création du fichier Yaml pour l’ingress:
• apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
 name: memos-ingress
spec:
 rules:
 - host: poleit.localhost.com
 http:
 paths:
 - pathType: Prefix
 path: "/"
 backend:
 service:
 name: memos-services
 port:
 number: 9000
Modifier le fichier /etc/hosts dans le master dans la machine virtuelle 
debian et enregistrer
Modifier Le fichier C:\Windows\System32\drivers\etc\hosts sur windows 
en tant qu’administrateur
