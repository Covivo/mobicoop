import UserReferenceField from "../User/UserReferenceField";
import React from "react";

const fr = {
    custom: {
        email: {
            texte: {
                emailTous: 'Email à tous les filtrés',
                emailSelect: 'Email aux sélectionnés',
                blockUnsubscribe :"Vous ne pouvez pas envoyer de mailing à des personnes qui l'ont refusé."
            }
        },
        rgpd : {
            modal :{
                titre : 'Avertissement RGPD',
                texte : 'Envoyer des emails en masse aux utilisateurs de %{instanceName} engage votre responsabilité, et a un impact potentiel sur toute la plate-forme. Pour rester conformes à la Réglementation Générale sur la Protection des Données, les emails envoyés doivent rester dans le cadre d"actualité de service" et en aucun cas inciter à l\'usage d\'autres services que %{instanceName} . \n' +
                        'Par ailleurs, le droit de retrait des utilisateurs ne peut s\'exercer par eux de manière distincte entre les différents expéditeurs : si un utilisateur désapprouve un email envoyé par un administrateur territorial ou un référent de commuauté et se désinscrit, il ne pourra plus être destinataire de tout futur envoi en masse, quelque soit l\'expéditeur.\n' +
                        'Une grande vigilance est donc demandée à chacun sur la pertinence et la fréquence de ses envois en masse.',
                buttonAgree : "Je comprend et j'accepte",
                buttonDisagree : "Je refuse"
            },
        },
        alert : {
            clearSelected : 'Réinitialiser la séléction',
            fieldMandatory : 'Ce champ est obligatoire'
        },
        label : {
            event :{
                name : 'Nom',
                resume : 'Résumé',
                resumefull : 'Résumé long',
                site : 'Site internet',
                adresse : 'Adresse',
                setTime : "Préciser l'heure",
                createur : "Créateur",
                status : "Status",
                dateTimeStart: "Date et heure de début",
                dateTimeFin : "Date et heure de fin",
                dateStart: "Date de début",
                dateFin: "Date de fin",
                image : "Image"
            },
            community :{
                community : 'Communauté',
                name : 'Nom',
                numberMember: 'Nombre de membres ',
                adress : 'Adresse',
                memberHidden : 'Membres masqués',
                proposalHidden : 'Annonces masquées',
                validationType : 'Type de validation',
                domainName : 'Nom de domaine',
                description : 'Description',
                descriptionFull : 'Description complète',
                createdDate : 'Date de création',
                updateDate : 'Date de mise à jour',
                status : "Status",
                createdBy : 'Référent',
                oldAdress : 'Ancienne adresse',
                newAdress : 'Nouvelle adresse',
                members : 'Membres',
                member : 'Membre',
                detail : 'Détail',
                membersModerator : 'Membres & Modérateurs',
                joinAt : 'Rejoint le',
                acceptedAt : 'Accepté le',
                refusedAt : 'Refusé le',
            },
            campaign : {
                object : 'Sujet',
                numberMember: 'Nombre de destinataires ',
                sender: 'Expéditeur',
                state: 'Statut',
                createdDate: 'Création',
                updateDate: 'Dernière modification',
                sendDate: 'Expédition',
                statusCampaign : {
                    init : 'Initialiser',
                    create: 'Créé',
                    send: 'Expédié',
                    archive: 'Archivé',
                },
                resumeCampaign : 'Reprendre la campagne'
            },user : {
              id : 'Id',
              givenName: 'Prénom',
              familyName: 'Nom',
              email: 'Email',
              accepteEmail: 'Accepte les emails',
              accepteReceiveEmail: 'Accepte de recevoir les emails',
              createdDate: 'Date de création',
              lastActivityDate: 'Date de dernière connection',
              solidary : "Solidaire",
              territory : "Territoires",
              gender : "Civilité",
              password : "Mot de passe",
              birthDate : "Date de naissance",
              telephone: "Téléphone",
              newsSubscription : "Recevoir les actualités du service  %{instanceName} (informations utiles pour covoiturer, et nouveaux services ou nouvelles fonctionnalités)",
              roles : "Droits d'accès",
              adresse : "Adresse",
              carpoolSetting : {
                music :"En ce qui concerne la musique en voiture",
                musicFavorites : "Radio et/musique préférées",
                chat : "En ce qui concerne le bavardage en voiture",
                chatFavorites :"Sujets préférés",
                smoke : "En ce qui concerne le tabac en voiture"
              },
              choices : {
                women : "Femme",
                men : "Homme",
                other : "Autre",
                didntSmoke : 'Je ne fume pas',
                didntSmokeCar : 'Je ne fume pas en voiture',
                smoke :'Je fume',
                withoutMusic : 'Je préfère rouler sans fond sonore',
                withMusic :'J’écoute la radio ou de la musique',
                dontTalk :'Je ne suis pas bavard',
                talk :'Je discute'
              },
              indentity : "Identité",
              preference : "Préférence",
              title : {
                edit : "Utilisateurs > Editer",
                create  : "Utilisateurs > Créer"
              },
              errors : {
                  upperPassword : 'Au minimum 1 majuscule',
                  lowerPassword : 'Au minimum 1 minuscule',
                  numberPassword : 'Au minimum 1 chiffre'
              },
              phoneDisplay : {
                visibility : "Visibilité de mon numéro de téléphone",
                forCarpooler : "Visible seulement après acceptation du covoiturage, uniquement par les participants.",
                forAll : "Visible dès la publication de l'annonce, par tous les inscrits."
              }
            }
        }
    }
}
const en = {
    custom: {

    }

}

export {
    fr,en
}
