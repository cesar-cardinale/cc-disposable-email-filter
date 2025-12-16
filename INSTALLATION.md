# Guide d'installation et d'utilisation

## Installation

### Méthode 1: Installation via l'interface d'administration PrestaShop

1. Téléchargez ou clonez ce dépôt
2. Compressez le dossier `ps_disposable_email_filter` en fichier ZIP
3. Connectez-vous à votre panneau d'administration PrestaShop
4. Allez dans **Modules → Module Manager**
5. Cliquez sur **"Uploader un module"**
6. Sélectionnez le fichier ZIP
7. Cliquez sur **"Installer"**

### Méthode 2: Installation manuelle

1. Téléchargez ou clonez ce dépôt
2. Copiez le dossier complet `ps_disposable_email_filter` dans le répertoire `modules` de votre installation PrestaShop
3. Allez dans **Modules → Module Manager**
4. Recherchez "Disposable Email Filter"
5. Cliquez sur **"Installer"**

## Configuration

Après l'installation, configurez le module :

1. Allez dans **Modules → Module Manager**
2. Recherchez "Disposable Email Filter"
3. Cliquez sur **"Configurer"**

### Options de configuration

- **Activer le filtre** : Active ou désactive le filtrage des emails jetables
- **Mise à jour automatique** : Met à jour automatiquement la liste des domaines tous les jours
- **Effacer le cache** : Force la mise à jour immédiate de la liste depuis GitHub

## Tableau de bord

Le module fournit un tableau de bord avec :

### Statistiques
- **Total des tentatives bloquées** : Nombre total d'inscriptions bloquées
- **Domaines dans la liste** : Nombre de domaines d'emails jetables dans la liste
- **Âge du cache** : Depuis combien de temps la liste a été mise à jour

### Journal des tentatives
Un tableau affichant les 50 dernières tentatives bloquées avec :
- Adresse email
- Adresse IP
- Date et heure de la tentative

## Fonctionnement

1. Lorsqu'un utilisateur tente de s'inscrire sur votre site
2. Le module vérifie si le domaine de l'email est dans la liste des emails jetables
3. Si le domaine est trouvé dans la liste :
   - L'inscription est bloquée
   - La tentative est enregistrée dans la base de données
   - Un message d'erreur est affiché à l'utilisateur
4. Si le domaine n'est pas dans la liste, l'inscription se poursuit normalement

## Source de la liste

Le module utilise la liste maintenue par la communauté disponible sur :
https://github.com/disposable-email-domains/disposable-email-domains

Cette liste contient plus de 4900 domaines d'emails jetables connus.

## Performance

- La liste est mise en cache localement pendant 24 heures
- Pas d'impact sur les performances grâce au système de cache
- Mise à jour automatique en arrière-plan

## Dépannage

### Le module ne bloque pas les emails jetables

1. Vérifiez que le filtre est activé dans la configuration
2. Effacez le cache du module
3. Vérifiez que votre serveur peut accéder à GitHub (pare-feu, etc.)

### Les logs ne s'affichent pas

1. Vérifiez que la table `ps_disposable_email_log` existe dans votre base de données
2. Réinstallez le module si nécessaire

### Message d'erreur lors de l'installation

1. Vérifiez les permissions sur le dossier du module
2. Vérifiez que votre version de PrestaShop est 9.0.0 ou supérieure
3. Consultez les logs d'erreur PrestaShop

## Base de données

Le module crée une table `ps_disposable_email_log` avec la structure suivante :

```sql
CREATE TABLE `ps_disposable_email_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `email` (`email`),
  KEY `date_add` (`date_add`)
)
```

## Désinstallation

Lors de la désinstallation :
- La table `ps_disposable_email_log` est supprimée (tous les logs sont perdus)
- Les configurations sont supprimées
- Le cache est supprimé

**Attention** : Si vous souhaitez conserver les logs, sauvegardez la table avant de désinstaller.

## Support

Pour toute question ou problème, créez une issue sur le dépôt GitHub :
https://github.com/cesar-cardinale/ps-disposable-email-filter/issues
