# Guide Visuel / Visual Guide

## Vue d'ensemble du module / Module Overview

Ce module PrestaShop 9 bloque automatiquement les inscriptions avec des emails jetables.
This PrestaShop 9 module automatically blocks registrations with disposable emails.

---

## 📋 Tableau de bord / Dashboard

### Statistiques affichées / Displayed Statistics:

```
┌─────────────────────────────────────────────────────────────┐
│  📊 Statistiques / Statistics                                │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│   ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐│
│   │       125       │  │      4941       │  │   2 heures  ││
│   │ Tentatives      │  │ Domaines dans   │  │ Âge du      ││
│   │ bloquées        │  │ la liste        │  │ cache       ││
│   └─────────────────┘  └─────────────────┘  └─────────────┘│
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Configuration / Settings

```
┌─────────────────────────────────────────────────────────────┐
│  Paramètres / Settings                                       │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Activer le filtre / Enable filter                           │
│  [x] Activé / Enabled    [ ] Désactivé / Disabled           │
│                                                               │
│  Mise à jour automatique / Auto-update blocklist             │
│  [x] Activé / Enabled    [ ] Désactivé / Disabled           │
│                                                               │
│  [ Enregistrer / Save ]  [ Effacer le cache / Clear Cache ] │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 Journal des tentatives / Attempts Log

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Tentatives bloquées récentes / Recent Blocked Attempts                  │
├────────────────────────────┬──────────────────┬─────────────────────────┤
│ Email                      │ Adresse IP       │ Date                    │
├────────────────────────────┼──────────────────┼─────────────────────────┤
│ user@0-mail.com           │ 192.168.1.100    │ 2024-12-16 14:30:25    │
│ test@tempmail.com         │ 192.168.1.101    │ 2024-12-16 13:45:12    │
│ spam@guerrillamail.com    │ 192.168.1.102    │ 2024-12-16 12:15:08    │
│ fake@10minutemail.com     │ 192.168.1.103    │ 2024-12-16 11:20:45    │
│ bot@mailinator.com        │ 192.168.1.104    │ 2024-12-16 10:05:33    │
└────────────────────────────┴──────────────────┴─────────────────────────┘
```

---

## 🚫 Expérience utilisateur / User Experience

### Inscription normale / Normal Registration
```
┌──────────────────────────────────┐
│  Créer un compte / Create Account│
├──────────────────────────────────┤
│  Email: user@gmail.com           │
│  Mot de passe: **********        │
│                                   │
│  [    S'inscrire / Register   ]  │
└──────────────────────────────────┘
         ↓
    ✅ SUCCÈS / SUCCESS
```

### Inscription bloquée / Blocked Registration
```
┌──────────────────────────────────┐
│  Créer un compte / Create Account│
├──────────────────────────────────┤
│  Email: spam@0-mail.com          │
│  Mot de passe: **********        │
│                                   │
│  [    S'inscrire / Register   ]  │
└──────────────────────────────────┘
         ↓
    ❌ ERREUR / ERROR
    "L'inscription avec des adresses
     email jetables n'est pas autorisée"
    
    → Log enregistré / Log recorded
```

---

## 🔄 Flux de travail / Workflow

```
┌─────────────────┐
│ Utilisateur     │
│ tente de        │
│ s'inscrire      │
│ User attempts   │
│ to register     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Module vérifie  │
│ le domaine      │
│ Module checks   │
│ domain          │
└────┬───────┬────┘
     │       │
     │       │
     ▼       ▼
┌─────┐   ┌─────────────┐
│Email│   │Email jetable│
│OK   │   │Disposable   │
└──┬──┘   └──────┬──────┘
   │             │
   ▼             ▼
┌──────────┐ ┌───────────┐
│Inscription│ │ Blocage + │
│réussie    │ │ Log       │
│Success    │ │ Block+Log │
└───────────┘ └───────────┘
```

---

## 📊 Caractéristiques techniques / Technical Features

- ✅ **4900+ domaines** bloqués / blocked domains
- ✅ **Cache 24h** pour les performances / for performance
- ✅ **Logs détaillés** (email, IP, date) / detailed logs
- ✅ **Compatible PrestaShop 9+**
- ✅ **Multi-langue** (FR/EN) / Multi-language
- ✅ **Sécurisé** (protection SQL injection) / Secure
- ✅ **Mise à jour auto** de la liste / Auto-update list
- ✅ **Interface admin** intuitive / Intuitive admin interface

---

## 🎯 Cas d'usage / Use Cases

### ✅ Ce qui est bloqué / What is blocked:
- 0-mail.com
- tempmail.com
- guerrillamail.com
- 10minutemail.com
- mailinator.com
- Et 4900+ autres / And 4900+ others

### ✅ Ce qui est autorisé / What is allowed:
- gmail.com
- yahoo.com
- hotmail.com
- outlook.com
- Tous les emails légitimes / All legitimate emails

---

## 💡 Avantages / Benefits

1. **Protection contre le spam** / Spam protection
   - Empêche les inscriptions frauduleuses / Prevents fraudulent registrations
   
2. **Qualité de la base de données** / Database quality
   - Emails valides uniquement / Valid emails only
   
3. **Sécurité** / Security
   - Réduit les attaques par bots / Reduces bot attacks
   
4. **Traçabilité** / Traceability
   - Logs complets des tentatives / Complete attempt logs

5. **Performance** / Performance
   - Cache intelligent / Smart caching
   - Impact minimal / Minimal impact

---

## 📞 Support

Pour toute question / For any questions:
- GitHub: https://github.com/cesar-cardinale/ps-disposable-email-filter
- Documentation: README.md
- Installation: INSTALLATION.md
